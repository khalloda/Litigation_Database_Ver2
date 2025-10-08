<?php

namespace App\Services\ETL;

use App\Models\CaseModel;
use App\Models\Client;
use App\Models\EngagementLetter;

class CasesImporter extends BaseImporter
{
    protected function getFilePath(): string
    {
        return 'cases.xlsx';
    }

    protected function getSheetName(): string
    {
        return 'الدعاوى'; // Arabic sheet name
    }

    protected function getColumnMapping(): array
    {
        return [
            'matter_id' => 'legacy_id',
            'client_id' => 'legacy_client_id',
            'contractID' => 'legacy_contract_id',
            'matter_name_ar' => 'matter_name_ar',
            'matter_name_en' => 'matter_name_en',
            'matter_description' => 'matter_description',
            'matter_status' => 'matter_status',
            'matterCategory' => 'matter_category',
            'matterDegree' => 'matter_degree',
            'matterCourt' => 'matter_court',
            'matterCircut' => 'matter_circuit',
            'matterDistination' => 'matter_destination',
            'matterImportance' => 'matter_importance',
            'matteEvaluation' => 'matter_evaluation',
            'matterStartDate' => 'matter_start_date',
            'matterEndDate' => 'matter_end_date',
            'matterAskedAmount' => 'matter_asked_amount',
            'matterJudgedAmount' => 'matter_judged_amount',
            'matterShelf' => 'matter_shelf',
            'matterPartner' => 'matter_partner',
            'lawyerA' => 'lawyer_a',
            'lawyerB' => 'lawyer_b',
            'circutSecretary' => 'circuit_secretary',
            'courtFloor' => 'court_floor',
            'courtHall' => 'court_hall',
            'خطاب الأتعاب' => 'fee_letter',
            'فريق العمل' => 'team_id',
            'الرأي القانوني' => 'legal_opinion',
            'المخصص المالي' => 'financial_provision',
            'الموقف الحالي' => 'current_status',
            'matterNotes1' => 'notes_1',
            'matterNotes2' => 'notes_2',
            'client&Cap' => 'client_and_capacity',
            'opponent&Cap' => 'opponent_and_capacity',
            'clientBranch' => 'client_branch',
            'نوع العميل' => 'client_type',
            'matterSelect' => 'matter_select',
        ];
    }

    protected function processRow(array $row): void
    {
        $legacyClientId = $this->parseInt($row['legacy_client_id'] ?? null);

        if (!$legacyClientId) {
            throw new \RuntimeException("client_id is required");
        }

        $client = Client::find($legacyClientId);

        if (!$client) {
            throw new \RuntimeException("Client not found: {$legacyClientId}");
        }

        // Try to find engagement letter
        $contractId = null;
        $legacyContractId = $this->parseDecimal($row['legacy_contract_id'] ?? null);
        
        if ($legacyContractId) {
            $contract = EngagementLetter::where('client_id', $client->id)
                ->where('mfiles_id', (int)$legacyContractId)
                ->first();
            
            $contractId = $contract?->id;
        }

        $data = [
            'client_id' => $client->id,
            'contract_id' => $contractId,
            'matter_name_ar' => $this->cleanString($row['matter_name_ar'] ?? null),
            'matter_name_en' => $this->cleanString($row['matter_name_en'] ?? null),
            'matter_description' => $this->cleanString($row['matter_description'] ?? null),
            'matter_status' => $this->cleanString($row['matter_status'] ?? null),
            'matter_category' => $this->cleanString($row['matter_category'] ?? null),
            'matter_degree' => $this->cleanString($row['matter_degree'] ?? null),
            'matter_court' => $this->cleanString($row['matter_court'] ?? null),
            'matter_circuit' => $this->cleanString($row['matter_circuit'] ?? null),
            'matter_destination' => $this->cleanString($row['matter_destination'] ?? null),
            'matter_importance' => $this->cleanString($row['matter_importance'] ?? null),
            'matter_evaluation' => $this->cleanString($row['matter_evaluation'] ?? null),
            'matter_start_date' => $this->parseDate($row['matter_start_date'] ?? null),
            'matter_end_date' => $this->parseDate($row['matter_end_date'] ?? null),
            'matter_asked_amount' => $this->parseDecimal($row['matter_asked_amount'] ?? null),
            'matter_judged_amount' => $this->parseDecimal($row['matter_judged_amount'] ?? null),
            'matter_shelf' => $this->cleanString($row['matter_shelf'] ?? null),
            'matter_partner' => $this->cleanString($row['matter_partner'] ?? null),
            'lawyer_a' => $this->cleanString($row['lawyer_a'] ?? null),
            'lawyer_b' => $this->cleanString($row['lawyer_b'] ?? null),
            'circuit_secretary' => $this->cleanString($row['circuit_secretary'] ?? null),
            'court_floor' => $this->parseInt($row['court_floor'] ?? null),
            'court_hall' => $this->parseInt($row['court_hall'] ?? null),
            'fee_letter' => $this->parseDecimal($row['fee_letter'] ?? null),
            'team_id' => $this->parseInt($row['team_id'] ?? null),
            'legal_opinion' => $this->cleanString($row['legal_opinion'] ?? null),
            'financial_provision' => $this->cleanString($row['financial_provision'] ?? null),
            'current_status' => $this->cleanString($row['current_status'] ?? null),
            'notes_1' => $this->cleanString($row['notes_1'] ?? null),
            'notes_2' => $this->cleanString($row['notes_2'] ?? null),
            'client_and_capacity' => $this->cleanString($row['client_and_capacity'] ?? null),
            'opponent_and_capacity' => $this->cleanString($row['opponent_and_capacity'] ?? null),
            'client_branch' => $this->cleanString($row['client_branch'] ?? null),
            'client_type' => $this->cleanString($row['client_type'] ?? null),
            'matter_select' => $this->parseBoolean($row['matter_select'] ?? true),
        ];

        // Validation
        if (empty($data['matter_name_ar'])) {
            throw new \RuntimeException("matter_name_ar is required");
        }

        // Idempotent upsert by legacy matter_id - PRESERVE ORIGINAL ID
        $legacyId = $this->parseInt($row['legacy_id'] ?? null);

        if ($legacyId) {
            $existing = CaseModel::find($legacyId);

            if ($existing) {
                $existing->update($data);
            } else {
                // Preserve original ID for FK integrity
                $data['id'] = $legacyId;
                CaseModel::create($data);
            }
        } else {
            CaseModel::create($data);
        }
    }
}

