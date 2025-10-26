<?php

namespace App\Services;

use App\Models\CaseModel;
use App\Models\Opponent;
use App\Models\Pivots\CaseOpponent;
use App\Models\OptionValue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CaseOpponentService
{
    /**
     * Attach an opponent to a case with specified parameters.
     *
     * CRITICAL: Handles soft deletes and capacity multiplicity per business rules.
     */
    public function attachOpponent(
        CaseModel $case,
        int $opponentId,
        ?int $capacityId = null,
        bool $isPrimary = false,
        ?int $order = null,
        ?string $alias = null
    ): CaseOpponent {
        return DB::transaction(function () use ($case, $opponentId, $capacityId, $isPrimary, $order, $alias) {
            // Validate max opponents constraint
            $this->validateMaxOpponents($case);

            // Check for existing opponent with same capacity (business rule enforcement)
            $existing = $this->findExistingOpponentWithCapacity($case, $opponentId, $capacityId);
            if ($existing) {
                throw new \InvalidArgumentException(
                    "Opponent with this capacity is already attached to this case. " .
                        "Same opponent can have different capacities, but not the same capacity."
                );
            }

            // If setting as primary, unset current primary
            if ($isPrimary) {
                $this->unsetCurrentPrimary($case);
            }

            // Determine display order
            if ($order === null) {
                $order = $case->opponents()->max('display_order') + 1;
            }

            // Create the pivot record
            $pivot = $case->opponents()->attach($opponentId, [
                'capacity_id' => $capacityId,
                'is_primary' => $isPrimary,
                'display_order' => $order,
                'alias_text' => $alias,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            // Sync legacy opponent_id field if this is primary
            if ($isPrimary) {
                $this->syncLegacyOpponentField($case);
            }

            Log::info("Opponent attached to case", [
                'case_id' => $case->id,
                'opponent_id' => $opponentId,
                'capacity_id' => $capacityId,
                'is_primary' => $isPrimary,
                'user_id' => auth()->id()
            ]);

            return $case->opponents()->wherePivot('opponent_id', $opponentId)->first()->pivot;
        });
    }

    /**
     * Detach an opponent from a case.
     */
    public function detachOpponent(CaseModel $case, int $opponentId): bool
    {
        $pivot = $case->opponents()->wherePivot('opponent_id', $opponentId)->first();

        if (!$pivot) {
            return false;
        }

        $wasPrimary = $pivot->pivot->is_primary;

        // Detach the opponent
        $case->opponents()->detach($opponentId);

        // If this was the primary opponent, sync legacy field
        if ($wasPrimary) {
            $this->syncLegacyOpponentField($case);
        }

        Log::info("Opponent detached from case", [
            'case_id' => $case->id,
            'opponent_id' => $opponentId,
            'was_primary' => $wasPrimary,
            'user_id' => auth()->id()
        ]);

        return true;
    }

    /**
     * Set an opponent as the primary opponent for a case.
     *
     * CRITICAL: Wrapped in transaction for atomicity.
     */
    public function setPrimary(CaseModel $case, int $opponentId): void
    {
        DB::transaction(function () use ($case, $opponentId) {
            // Check if opponent is attached to this case
            $pivot = $case->opponents()->wherePivot('opponent_id', $opponentId)->first();
            if (!$pivot) {
                throw new \InvalidArgumentException("Opponent is not attached to this case.");
            }

            // Unset current primary
            $this->unsetCurrentPrimary($case);

            // Set new primary
            $case->opponents()->updateExistingPivot($opponentId, [
                'is_primary' => true,
                'updated_by' => auth()->id(),
            ]);

            // Sync legacy opponent_id field
            $this->syncLegacyOpponentField($case);

            Log::info("Primary opponent changed", [
                'case_id' => $case->id,
                'opponent_id' => $opponentId,
                'user_id' => auth()->id()
            ]);
        });
    }

    /**
     * Reorder opponents for a case.
     */
    public function reorder(CaseModel $case, array $opponentIdsInOrder): void
    {
        DB::transaction(function () use ($case, $opponentIdsInOrder) {
            foreach ($opponentIdsInOrder as $index => $opponentId) {
                $case->opponents()->updateExistingPivot($opponentId, [
                    'display_order' => $index + 1,
                    'updated_by' => auth()->id(),
                ]);
            }
        });

        Log::info("Opponents reordered for case", [
            'case_id' => $case->id,
            'order' => $opponentIdsInOrder,
            'user_id' => auth()->id()
        ]);
    }

    /**
     * Validate that the case doesn't exceed the maximum opponents limit.
     */
    public function validateMaxOpponents(CaseModel $case): void
    {
        $maxOpponents = config('importer.opponents.max_per_case', 10);
        $currentCount = $case->opponents()->count();

        if ($currentCount >= $maxOpponents) {
            throw new \InvalidArgumentException(
                "Maximum {$maxOpponents} opponents allowed per case. Current count: {$currentCount}"
            );
        }
    }

    /**
     * Unset the current primary opponent for a case.
     */
    private function unsetCurrentPrimary(CaseModel $case): void
    {
        $case->opponents()->wherePivot('is_primary', true)->update([
            'is_primary' => false,
            'updated_by' => auth()->id(),
        ]);
    }

    /**
     * Sync the legacy opponent_id field to match the primary opponent.
     */
    private function syncLegacyOpponentField(CaseModel $case): void
    {
        $primaryOpponent = $case->primaryOpponent()->first();

        if ($primaryOpponent) {
            $case->update([
                'opponent_id' => $primaryOpponent->id,
                'opponent_capacity_id' => $primaryOpponent->pivot->capacity_id,
            ]);
        } else {
            // No primary opponent, clear legacy fields
            $case->update([
                'opponent_id' => null,
                'opponent_capacity_id' => null,
            ]);
        }
    }

    /**
     * Get opponents for a case with their capacities.
     */
    public function getOpponentsWithCapacities(CaseModel $case)
    {
        return $case->opponents()
            ->with('pivot.capacity')
            ->orderBy('display_order')
            ->get();
    }

    /**
     * Find or create an opponent by name.
     */
    public function findOrCreateOpponent(string $name, ?string $nameAr = null): Opponent
    {
        // Try to find existing opponent by name
        $opponent = Opponent::where('opponent_name_en', $name)
            ->orWhere('opponent_name_ar', $name)
            ->orWhere('opponent_name_en', $nameAr)
            ->orWhere('opponent_name_ar', $nameAr)
            ->first();

        if ($opponent) {
            return $opponent;
        }

        // Create new opponent
        return Opponent::create([
            'opponent_name_en' => $name,
            'opponent_name_ar' => $nameAr ?? $name,
            'is_active' => true,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);
    }

    /**
     * Find capacity by name or ID.
     */
    public function findCapacity(?string $capacityName = null, ?int $capacityId = null): ?OptionValue
    {
        if ($capacityId) {
            return OptionValue::find($capacityId);
        }

        if ($capacityName) {
            return OptionValue::where('label_en', $capacityName)
                ->orWhere('label_ar', $capacityName)
                ->first();
        }

        return null;
    }

    /**
     * Find existing opponent with same capacity (business rule enforcement).
     *
     * CRITICAL: Checks for soft deletes and enforces capacity multiplicity rule.
     * Same opponent can have different capacities, but not the same capacity.
     */
    private function findExistingOpponentWithCapacity(CaseModel $case, int $opponentId, ?int $capacityId): ?CaseOpponent
    {
        return $case->opponents()
            ->wherePivot('opponent_id', $opponentId)
            ->wherePivot('capacity_id', $capacityId)
            ->whereNull('deleted_at') // Only check non-deleted records
            ->first();
    }
}
