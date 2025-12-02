<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Role::class);

        $roles = Role::with('permissions')
            ->orderBy('name')
            ->paginate($request->get('per_page', 25));

        $roles->getCollection()->transform(function (Role $role) {
            return $this->transformRoleForApi($role);
        });

        return response()->json($roles);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Role::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create(['name' => $validated['name']]);

        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        $role->load('permissions');

        return response()->json([
            'data' => $this->transformRoleForApi($role),
            'message' => 'Role created successfully',
        ], 201);
    }

    public function show(Role $role): JsonResponse
    {
        $this->authorize('view', $role);
        $role->load('permissions');
        return response()->json([
            'data' => $this->transformRoleForApi($role),
        ]);
    }

    public function update(Request $request, Role $role): JsonResponse
    {
        $this->authorize('update', $role);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update($validated);

        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        $role->load('permissions');

        return response()->json([
            'data' => $this->transformRoleForApi($role),
            'message' => 'Role updated successfully',
        ]);
    }

    public function destroy(Role $role): JsonResponse
    {
        $this->authorize('delete', $role);
        $role->delete();
        return response()->json(['message' => 'Role deleted successfully']);
    }

    /**
     * Normalize Role model into the SPA Role type shape.
     */
    protected function transformRoleForApi(Role $role): array
    {
        $permissions = $role->permissions
            ? $role->permissions->pluck('name')->values()->all()
            : [];

        return [
            'id' => $role->id,
            'name_en' => $role->name,
            'name_ar' => $role->name,
            'description_en' => $role->description_en ?? '',
            'description_ar' => $role->description_ar ?? '',
            'permissions' => $permissions,
        ];
    }
}

