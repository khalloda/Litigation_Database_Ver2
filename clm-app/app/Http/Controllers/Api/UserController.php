<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $query = User::with('roles');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('name')
            ->paginate($request->get('per_page', 25));

        // Normalize shape for SPA
        $users->getCollection()->transform(function (User $user) {
            return $this->transformUserForApi($user);
        });

        return response()->json($users);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role_id' => 'nullable|exists:roles,id',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        if (isset($validated['role_id'])) {
            $role = \Spatie\Permission\Models\Role::find($validated['role_id']);
            if ($role) {
                $user->assignRole($role);
            }
        }

        return response()->json([
            'data' => $this->transformUserForApi($user->fresh('roles')),
            'message' => 'User created successfully',
        ], 201);
    }

    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);
        $user->load('roles', 'permissions');

        return response()->json([
            'data' => $this->transformUserForApi($user),
        ]);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'role_id' => 'nullable|exists:roles,id',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        if (isset($validated['role_id'])) {
            $user->roles()->sync([$validated['role_id']]);
        }

        return response()->json([
            'data' => $this->transformUserForApi($user->fresh('roles', 'permissions')),
            'message' => 'User updated successfully',
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        $this->authorize('delete', $user);
        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }

    /**
     * Normalize User model into the SPA User type shape.
     */
    protected function transformUserForApi(User $user): array
    {
        $primaryRole = $user->roles->first();

        return [
            'id' => $user->id,
            'name_en' => $user->name,
            'name_ar' => $user->name,
            'email' => $user->email,
            'role_id' => $primaryRole?->id ?? null,
            'is_active' => $primaryRole !== null,
            'role' => $primaryRole ? [
                'id' => $primaryRole->id,
                'name_en' => $primaryRole->name,
                'name_ar' => $primaryRole->name,
                'description_en' => '',
                'description_ar' => '',
                'permissions' => $primaryRole->permissions
                    ? $primaryRole->permissions->pluck('name')->all()
                    : [],
            ] : null,
        ];
    }
}

