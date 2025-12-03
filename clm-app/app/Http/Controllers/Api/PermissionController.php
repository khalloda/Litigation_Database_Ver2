<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    /**
     * List all available permissions for role management.
     */
    public function index(Request $request): JsonResponse
    {
        // Reuse the same authorization used for managing roles
        $this->authorize('viewAny', Role::class);

        $permissions = Permission::query()
            ->orderBy('name')
            ->get(['id', 'name', 'guard_name']);

        return response()->json([
            'data' => $permissions,
        ]);
    }
}


