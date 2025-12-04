<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdminTask;
use App\Models\Hearing;
use App\Models\CaseModel;
use App\Models\Lawyer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
            $todayHearings = Hearing::whereDate('date', $now->toDateString())
                ->with([
                    'case:id,matter_name_en,matter_name_ar',
                    'lawyer:id,lawyer_name_en,lawyer_name_ar',
                ])
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
            $weekHearings = Hearing::whereBetween('date', [$startOfWeek, $endOfWeek])
                ->with([
                    'case:id,matter_name_en,matter_name_ar',
                    'lawyer:id,lawyer_name_en,lawyer_name_ar',
                ])
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

    /**
     * Latest pending hearings for dashboard (paged, oldest first).
     */
    public function pendingHearings(Request $request): JsonResponse
    {
        try {
            $this->authorize('viewAny', Hearing::class);

            $perPage = (int) $request->get('per_page', 20);
            $perPage = $perPage > 0 ? min($perPage, 100) : 20;
            $page = (int) $request->get('page', 1);
            $page = $page > 0 ? $page : 1;

            // Treat status case-insensitively to include legacy rows where the value might be \"Pending\"
            $paginator = Hearing::whereRaw('LOWER(status) = ?', ['pending'])
                ->with([
                    'case:id,matter_name_en,matter_name_ar',
                    'lawyer:id,lawyer_name_en,lawyer_name_ar',
                ])
                ->orderBy('date', 'asc')
                ->paginate($perPage, ['*'], 'page', $page);

            $items = $paginator->getCollection()->map(function (Hearing $hearing) {
                return [
                    'id' => $hearing->id,
                    'date' => optional($hearing->date)->format('Y-m-d'),
                    'status' => $hearing->status,
                    'created_at' => optional($hearing->created_at)?->toIso8601String(),
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
            })->values();

            return response()->json([
                'data' => $items,
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'has_more' => $paginator->hasMorePages(),
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('DashboardController@pendingHearings error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Failed to fetch pending hearings',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Latest pending admin tasks for dashboard (paged, oldest first).
     */
    public function pendingTasks(Request $request): JsonResponse
    {
        try {
            $this->authorize('viewAny', AdminTask::class);

            $perPage = (int) $request->get('per_page', 20);
            $perPage = $perPage > 0 ? min($perPage, 100) : 20;
            $page = (int) $request->get('page', 1);
            $page = $page > 0 ? $page : 1;

            $paginator = AdminTask::whereIn('status', ['todo', 'in-progress'])
                ->with([
                    'case:id,matter_name_en,matter_name_ar',
                    'lawyer:id,lawyer_name_en,lawyer_name_ar',
                ])
                ->orderByRaw('COALESCE(creation_date, created_at) ASC')
                ->paginate($perPage, ['*'], 'page', $page);

            $items = $paginator->getCollection()->map(function (AdminTask $task) {
                return [
                    'id' => $task->id,
                    'title' => $task->required_work,
                    'status' => $task->status,
                    'creation_date' => optional($task->creation_date)?->toIso8601String(),
                    'created_at' => optional($task->created_at)?->toIso8601String(),
                    'case' => $task->case ? [
                        'id' => $task->case->id,
                        'name_en' => $task->case->matter_name_en,
                        'name_ar' => $task->case->matter_name_ar,
                    ] : null,
                    'lawyer' => $task->lawyer ? [
                        'id' => $task->lawyer->id,
                        'name_en' => $task->lawyer->lawyer_name_en,
                        'name_ar' => $task->lawyer->lawyer_name_ar,
                    ] : null,
                ];
            })->values();

            return response()->json([
                'data' => $items,
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'has_more' => $paginator->hasMorePages(),
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('DashboardController@pendingTasks error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Failed to fetch pending tasks',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}

