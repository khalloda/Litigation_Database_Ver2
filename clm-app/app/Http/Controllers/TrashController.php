<?php

namespace App\Http\Controllers;

use App\Models\DeletionBundle;
use App\Services\DeletionBundleService;
use Illuminate\Http\Request;

class TrashController extends Controller
{
    protected $service;

    public function __construct(DeletionBundleService $service)
    {
        $this->service = $service;
        $this->middleware('auth');
    }

    /**
     * Display a listing of deletion bundles.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', DeletionBundle::class);

        $query = DeletionBundle::with('deletedBy')->orderBy('created_at', 'desc');

        // Apply filters
        if ($type = $request->get('type')) {
            $query->ofType($type);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $bundles = $query->paginate(20);

        // Get statistics
        $stats = [
            'by_type' => DeletionBundle::selectRaw('root_type, count(*) as count')
                ->groupBy('root_type')
                ->pluck('count', 'root_type'),
            'by_status' => DeletionBundle::selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status'),
            'total' => DeletionBundle::count(),
        ];

        return view('trash.index', compact('bundles', 'stats'));
    }

    /**
     * Display the specified bundle.
     */
    public function show(DeletionBundle $bundle)
    {
        $this->authorize('view', $bundle);

        $bundle->load('deletedBy', 'items');

        // Group items by model type
        $itemsByType = $bundle->items->groupBy('model');

        return view('trash.show', compact('bundle', 'itemsByType'));
    }

    /**
     * Restore the specified bundle.
     */
    public function restore(Request $request, DeletionBundle $bundle)
    {
        $this->authorize('restore', $bundle);

        $request->validate([
            'conflict_strategy' => 'nullable|in:skip,overwrite,new_copy',
        ]);

        try {
            $report = $this->service->restoreBundle($bundle->id, [
                'dry_run' => false,
                'resolve_conflicts' => $request->get('conflict_strategy', 'skip'),
            ]);

            return redirect()
                ->route('trash.index')
                ->with('success', "Bundle restored successfully! Restored {$report['restored']} items.");
        } catch (\Exception $e) {
            return back()->with('error', "Restoration failed: {$e->getMessage()}");
        }
    }

    /**
     * Purge the specified bundle.
     */
    public function purge(DeletionBundle $bundle)
    {
        $this->authorize('purge', $bundle);

        try {
            $this->service->purgeBundle($bundle->id);

            return redirect()
                ->route('trash.index')
                ->with('success', 'Bundle purged successfully!');
        } catch (\Exception $e) {
            return back()->with('error', "Purge failed: {$e->getMessage()}");
        }
    }

    /**
     * Perform a dry-run restore simulation.
     */
    public function dryRunRestore(Request $request, DeletionBundle $bundle)
    {
        $this->authorize('restore', $bundle);

        $request->validate([
            'conflict_strategy' => 'nullable|in:skip,overwrite,new_copy',
        ]);

        try {
            $report = $this->service->restoreBundle($bundle->id, [
                'dry_run' => true,
                'resolve_conflicts' => $request->get('conflict_strategy', 'skip'),
            ]);

            return response()->json([
                'success' => true,
                'report' => $report,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
