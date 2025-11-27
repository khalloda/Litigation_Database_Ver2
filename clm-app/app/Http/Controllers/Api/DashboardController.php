<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function statistics(): JsonResponse
    {
        try {
            $now = Carbon::now();
            $startOfMonth = $now->copy()->startOfMonth();
            $startOfToday = $now->copy()->startOfDay();
            $endOfToday = $now->copy()->endOfDay();
            $startOfWeek = $now->copy()->startOfWeek();
            $endOfWeek = $now->copy()->endOfWeek();

            // Clients statistics
            $totalClients = \App\Models\Client::count();
            $newClientsThisMonth = \App\Models\Client::where('created_at', '>=', $startOfMonth)->count();

            // Cases/Matters statistics
            $totalCases = \App\Models\CaseModel::count();
            $newCasesThisMonth = \App\Models\CaseModel::where('created_at', '>=', $startOfMonth)->count();

            // Documents statistics (using ClientDocument model)
            $totalDocuments = \App\Models\ClientDocument::count();
            $newDocumentsThisMonth = \App\Models\ClientDocument::where('created_at', '>=', $startOfMonth)->count();

            // Power of Attorney statistics
            $totalPOAs = \App\Models\PowerOfAttorney::count();
            $newPOAsThisMonth = \App\Models\PowerOfAttorney::where('created_at', '>=', $startOfMonth)->count();

            // Today's hearings
            $todayHearings = \App\Models\Hearing::whereDate('date', $now->toDateString())
                ->with(['case:id,matter_name_en,matter_name_ar', 'lawyer:id,lawyer_name_en,lawyer_name_ar'])
                ->orderBy('date', 'asc')
                ->get()
                ->map(function ($hearing) {
                    return [
                        'id' => $hearing->id,
                        'date' => $hearing->date?->format('Y-m-d'),
                        'procedure' => $hearing->procedure,
                        'case' => $hearing->case ? [
                            'id' => $hearing->case->id,
                            'name_en' => $hearing->case->matter_name_en,
                            'name_ar' => $hearing->case->matter_name_ar,
                        ] : null,
                        'lawyer' => $hearing->lawyer ? [
                            'id' => $hearing->lawyer->id,
                            'name_en' => $hearing->lawyer->lawyer_name_en,
                            'name_ar' => $hearing->lawyer->lawyer_name_ar,
                        ] : null,
                    ];
                });

            // This week's hearings
            $weekHearings = \App\Models\Hearing::whereBetween('date', [$startOfWeek, $endOfWeek])
                ->with(['case:id,matter_name_en,matter_name_ar', 'lawyer:id,lawyer_name_en,lawyer_name_ar'])
                ->orderBy('date', 'asc')
                ->get()
                ->map(function ($hearing) {
                    return [
                        'id' => $hearing->id,
                        'date' => $hearing->date?->format('Y-m-d'),
                        'procedure' => $hearing->procedure,
                        'case' => $hearing->case ? [
                            'id' => $hearing->case->id,
                            'name_en' => $hearing->case->matter_name_en,
                            'name_ar' => $hearing->case->matter_name_ar,
                        ] : null,
                        'lawyer' => $hearing->lawyer ? [
                            'id' => $hearing->lawyer->id,
                            'name_en' => $hearing->lawyer->lawyer_name_en,
                            'name_ar' => $hearing->lawyer->lawyer_name_ar,
                        ] : null,
                    ];
                });

            return response()->json([
                'data' => [
                    'clients' => [
                        'total' => $totalClients,
                        'this_month' => $newClientsThisMonth,
                    ],
                    'cases' => [
                        'total' => $totalCases,
                        'this_month' => $newCasesThisMonth,
                    ],
                    'documents' => [
                        'total' => $totalDocuments,
                        'this_month' => $newDocumentsThisMonth,
                    ],
                    'power_of_attorneys' => [
                        'total' => $totalPOAs,
                        'this_month' => $newPOAsThisMonth,
                    ],
                    'hearings' => [
                        'today' => $todayHearings,
                        'this_week' => $weekHearings,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('DashboardController@statistics error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'error' => 'Failed to fetch dashboard statistics',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}

