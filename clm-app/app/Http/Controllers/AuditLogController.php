<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::with(['causer', 'subject'])
            ->orderBy('created_at', 'desc');

        // Filter by model type
        if ($request->filled('subject_type')) {
            $query->where('subject_type', $request->subject_type);
        }

        // Filter by event type
        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        // Filter by user
        if ($request->filled('causer_id')) {
            $query->where('causer_id', $request->causer_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search in description
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('description', 'like', "%{$searchTerm}%")
                  ->orWhere('properties', 'like', "%{$searchTerm}%");
            });
        }

        $activities = $query->paginate(25);

        // Get filter options
        $subjectTypes = Activity::select('subject_type')
            ->distinct()
            ->whereNotNull('subject_type')
            ->orderBy('subject_type')
            ->pluck('subject_type')
            ->map(function ($type) {
                return [
                    'value' => $type,
                    'label' => class_basename($type)
                ];
            });

        $events = Activity::select('event')
            ->distinct()
            ->whereNotNull('event')
            ->orderBy('event')
            ->pluck('event');

        $users = Activity::with('causer')
            ->whereNotNull('causer_id')
            ->get()
            ->pluck('causer')
            ->unique('id')
            ->filter()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email
                ];
            });

        return view('audit-logs.index', compact('activities', 'subjectTypes', 'events', 'users'));
    }

    public function show(Activity $activity)
    {
        $activity->load(['causer', 'subject']);

        return view('audit-logs.show', compact('activity'));
    }

    public function export(Request $request)
    {
        // Build the same query as index
        $query = Activity::with(['causer', 'subject'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('subject_type')) {
            $query->where('subject_type', $request->subject_type);
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('causer_id')) {
            $query->where('causer_id', $request->causer_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('description', 'like', "%{$searchTerm}%")
                  ->orWhere('properties', 'like', "%{$searchTerm}%");
            });
        }

        $activities = $query->limit(1000)->get(); // Limit for performance

        $filename = 'audit_logs_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($activities) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, [
                'Date/Time',
                'User',
                'Action',
                'Subject Type',
                'Subject ID',
                'Description',
                'Changes'
            ]);

            foreach ($activities as $activity) {
                $changes = '';
                if ($activity->properties && isset($activity->properties['attributes'])) {
                    $changes = json_encode($activity->properties['attributes']);
                }

                fputcsv($file, [
                    $activity->created_at->format('Y-m-d H:i:s'),
                    $activity->causer ? $activity->causer->name . ' (' . $activity->causer->email . ')' : 'System',
                    ucfirst($activity->event),
                    $activity->subject_type ? class_basename($activity->subject_type) : 'N/A',
                    $activity->subject_id ?? 'N/A',
                    $activity->description,
                    $changes
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}