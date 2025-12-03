<?php

namespace App\Services;

use App\Models\CaseModel;
use App\Models\Hearing;
use Carbon\Carbon;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class HearingLifecycleService
{
    public function __construct(
        protected DatabaseManager $db
    ) {
    }

    /**
     * Create a new hearing and apply lifecycle rules.
     *
     * @param array $data Validated request data (uses case_id/hearing_date/next_hearing_date keys from controller).
     */
    public function createHearing(array $data, int $userId): Hearing
    {
        return $this->db->transaction(function () use ($data, $userId) {
            $normalized = $this->normalizePayload($data, $userId, true);

            $this->validateNoOverlappingPending($normalized);

            $hearing = Hearing::create($normalized);

            $this->populateLastDecision($hearing);

            $this->updateStatusAndCompletion($hearing, $normalized);

            $hearing->save();

            $autoCreated = null;
            if (!empty($normalized['next_hearing'])) {
                $autoCreated = $this->autoCreateNextHearing($hearing, $userId);
            }

            $this->refreshCaseSummary($hearing->matter_id);

            if ($autoCreated) {
                $hearing->setRelation('next_auto_created', $autoCreated);
            }

            return $hearing;
        });
    }

    /**
     * Update an existing hearing and apply lifecycle rules.
     *
     * @param array $data Validated request data.
     * @return array{hearing:Hearing, warning?:string}
     */
    public function updateHearing(Hearing $hearing, array $data, int $userId): array
    {
        return $this->db->transaction(function () use ($hearing, $data, $userId) {
            $originalNext = $hearing->next_hearing ? $hearing->next_hearing->copy() : null;

            $normalized = $this->normalizePayload($data, $userId, false);

            // Apply changes to model
            $hearing->fill($normalized);

            $this->validateNoOverlappingPending($hearing->getAttributes(), $hearing->id);

            $this->populateLastDecision($hearing);
            $this->updateStatusAndCompletion($hearing, $normalized);
            $hearing->save();

            $warning = null;

            // Handle next hearing auto-create / reschedule rules
            $newNext = $hearing->next_hearing ? $hearing->next_hearing->copy() : null;
            if ($newNext && !$originalNext) {
                // New next date added -> create H(n+1)
                $this->autoCreateNextHearing($hearing, $userId);
            } elseif ($newNext && $originalNext && !$newNext->equalTo($originalNext)) {
                // Date changed -> attempt reschedule
                $conflict = $this->rescheduleNextHearingIfClean($hearing, $newNext);
                if ($conflict) {
                    $warning = 'next_hearing_conflict';
                }
            }

            $this->refreshCaseSummary($hearing->matter_id);

            return [
                'hearing' => $hearing,
                'warning' => $warning,
            ];
        });
    }

    /**
     * Normalize controller payload into Hearing fillable columns.
     */
    protected function normalizePayload(array $data, int $userId, bool $isCreate): array
    {
        $normalized = $data;

        if (isset($data['case_id'])) {
            $normalized['matter_id'] = $data['case_id'];
        }

        if (isset($data['hearing_date'])) {
            $normalized['date'] = $data['hearing_date'];
        }

        if (isset($data['next_hearing_date'])) {
            $normalized['next_hearing'] = $data['next_hearing_date'];
        }

        if (isset($data['attending_lawyer_id'])) {
            $normalized['lawyer_id'] = $data['attending_lawyer_id'];
        }

        unset(
            $normalized['case_id'],
            $normalized['hearing_date'],
            $normalized['next_hearing_date'],
            $normalized['attending_lawyer_id']
        );

        if ($isCreate) {
            $normalized['created_by'] = $userId;
        }

        $normalized['updated_by'] = $userId;

        return $normalized;
    }

    /**
     * Populate last_decision from the immediately previous hearing on the same case.
     */
    protected function populateLastDecision(Hearing $hearing): void
    {
        if (!$hearing->matter_id || !$hearing->date) {
            return;
        }

        $previous = Hearing::where('matter_id', $hearing->matter_id)
            ->where(function ($q) use ($hearing) {
                $q->where('date', '<', $hearing->date)
                    ->orWhere(function ($q2) use ($hearing) {
                        $q2->whereDate('date', $hearing->date)
                            ->where('id', '<', $hearing->id ?? 0);
                    });
            })
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        $hearing->last_decision = $previous?->decision;
    }

    /**
     * Update status and completed_at based on decision / next_hearing.
     */
    protected function updateStatusAndCompletion(Hearing $hearing, array $payload): void
    {
        $hasDecision = !empty($hearing->decision);
        $hasNext = !empty($hearing->next_hearing);

        // Enforce validation rule: next_hearing >= today if present
        if ($hasNext) {
            $nextDate = $hearing->next_hearing instanceof Carbon
                ? $hearing->next_hearing
                : Carbon::parse($hearing->next_hearing);

            if ($nextDate->isBefore(now()->startOfDay())) {
                throw ValidationException::withMessages([
                    'next_hearing_date' => ['Next hearing date must be today or in the future.'],
                ]);
            }
        }

        if ($hasDecision || $hasNext) {
            $hearing->status = 'complete';
            if (!$hearing->completed_at) {
                $hearing->completed_at = now();
            }
        } else {
            $hearing->status = 'pending';
            // Do not clear completed_at if already complete; status/payload rules decide.
        }
    }

    /**
     * Auto-create the next hearing H{n+1} when next_hearing is set.
     */
    protected function autoCreateNextHearing(Hearing $hearing, int $userId): ?Hearing
    {
        if (!$hearing->matter_id || !$hearing->next_hearing) {
            return null;
        }

        // Check if a child with that date already exists
        $existing = Hearing::where('matter_id', $hearing->matter_id)
            ->whereDate('date', $hearing->next_hearing)
            ->first();

        if ($existing) {
            return null;
        }

        $next = new Hearing();
        $next->matter_id = $hearing->matter_id;
        $next->date = $hearing->next_hearing;
        $next->decision = null;
        $next->next_hearing = null;
        $next->status = 'pending';
        $next->last_decision = $hearing->decision;
        $next->lawyer_id = $hearing->lawyer_id;
        $next->court = $hearing->court;
        $next->circuit = $hearing->circuit;
        $next->created_by = $userId;
        $next->updated_by = $userId;
        $next->save();

        return $next;
    }

    /**
     * Try to reschedule the auto-created next hearing; return true if conflict detected.
     */
    protected function rescheduleNextHearingIfClean(Hearing $hearing, Carbon $newDate): bool
    {
        $next = Hearing::where('matter_id', $hearing->matter_id)
            ->whereDate('date', $hearing->getOriginal('next_hearing') ?? $newDate)
            ->orderBy('id')
            ->first();

        if (!$next) {
            // Nothing to reschedule – allow a new auto-create if needed
            return false;
        }

        // If next hearing has no user edits (no decision, no notes, created_by same as updated_by)
        $hasUserInput = !empty($next->decision) || !empty($next->notes);

        if (!$hasUserInput && $next->created_by === $next->updated_by) {
            $next->date = $newDate;
            $next->save();
            return false;
        }

        return true; // conflict – do not auto-change
    }

    /**
     * Ensure we don't have two pending hearings on the same date for the same case.
     *
     * @param array $attributes attributes ready to be applied to a Hearing instance.
     * @param int|null $excludeId optional hearing id to exclude from the check (for updates).
     */
    protected function validateNoOverlappingPending(array $attributes, ?int $excludeId = null): void
    {
        $matterId = $attributes['matter_id'] ?? null;
        $date = $attributes['date'] ?? null;

        if (!$matterId || !$date) {
            return;
        }

        $query = Hearing::where('matter_id', $matterId)
            ->whereDate('date', $date)
            ->where('status', 'pending');

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'date' => ['Another pending hearing already exists for this case on the same date.'],
            ]);
        }
    }

    /**
     * Refresh case-level summary fields: next_hearing_date, last_hearing_date, latest_decision.
     */
    public function refreshCaseSummary(int $caseId): void
    {
        $case = CaseModel::find($caseId);
        if (!$case) {
            return;
        }

        $hearings = Hearing::where('matter_id', $caseId)
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        $today = now()->startOfDay();

        $nextPending = $hearings
            ->where('status', 'pending')
            ->filter(fn (Hearing $h) => $h->date && $h->date->greaterThanOrEqualTo($today))
            ->sortBy('date')
            ->first();

        $lastComplete = $hearings
            ->where('status', 'complete')
            ->filter(fn (Hearing $h) => $h->date)
            ->sortByDesc('date')
            ->sortByDesc('id')
            ->first();

        $latestDecision = $hearings
            ->filter(fn (Hearing $h) => !empty($h->decision))
            ->sortByDesc('date')
            ->sortByDesc('id')
            ->first();

        $case->next_hearing_date = $nextPending?->date;
        $case->last_hearing_date = $lastComplete?->date;
        $case->latest_decision = $latestDecision?->decision;
        $case->save();
    }
}


