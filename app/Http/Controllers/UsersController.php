<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Services\NavigationService;
use App\Traits\HandlesPermissions;
use Illuminate\Support\Facades\Log;

class UsersController extends Controller
{
    use HandlesPermissions;

    public function index()
    {
        $this->checkPermission('view users');

        $users = User::with('roles')->latest()->get();
        $tables = NavigationService::getTablesForUser(auth()->user());

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'tables' => $tables,
            'permissions' => $this->getCrudPermissions('users')
        ]);
    }

    public function show(User $user)
    {
        $this->checkPermission('view users');

        $tables = NavigationService::getTablesForUser(auth()->user());

        return Inertia::render('Admin/Users/Show', [
            'user' => $user->load('roles'),
            'tables' => $tables,
        ]);
    }

    public function create()
    {
        $this->checkPermission('create users');

        $tables = NavigationService::getTablesForUser(auth()->user());

        return Inertia::render('Admin/Users/Create', [
            'roles' => Role::all(),
            'tables' => $tables,
        ]);
    }

    public function store(Request $request)
    {
        $this->checkPermission('create users');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:8',
            'roles' => 'array',
            'profile_image' => 'nullable|image|max:2048',
        ]);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ];

        if ($request->hasFile('profile_image')) {
            $imagePath = $request->file('profile_image')->store('profile-images', 'public');
            $userData['profile_image'] = $imagePath;
        }

        $user = User::create($userData);

        if (!empty($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        }

        // Return to settings page with only success message
        return redirect()->route('admin.settings')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $this->checkPermission('edit users');

        $tables = NavigationService::getTablesForUser(auth()->user());

        return Inertia::render('Admin/Users/Edit', [
            'user' => $user->load('roles'),
            'roles' => Role::all(),
            'userRoles' => $user->roles->pluck('name'),
            'tables' => $tables,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $this->checkPermission('edit users');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|confirmed|min:8',
            'roles' => 'array',
            'profile_image' => 'nullable|image|max:2048',
        ]);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($validated['password']);
        }

        if ($request->hasFile('profile_image')) {
            if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
                Storage::disk('public')->delete($user->profile_image);
            }
            
            $imagePath = $request->file('profile_image')->store('profile-images', 'public');
            $userData['profile_image'] = $imagePath;
        }

        if ($request->has('remove_existing_image') && $request->remove_existing_image) {
            if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
                Storage::disk('public')->delete($user->profile_image);
            }
            $userData['profile_image'] = null;
        }

        $user->update($userData);

        $user->syncRoles($validated['roles'] ?? []);

        // Return to settings page with only success message
        return redirect()->route('admin.settings')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $this->checkPermission('delete users');
        
        if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
            Storage::disk('public')->delete($user->profile_image);
        }
        
        $user->delete();
        
        // Return to settings page with only success message
        return redirect()->route('admin.settings')->with('success', 'User deleted successfully.');
    }

    public function removeImage(User $user)
    {
        $this->checkPermission('edit users');

        if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
            Storage::disk('public')->delete($user->profile_image);
        }

        $user->update(['profile_image' => null]);

        return back()->with('success', 'Profile image removed successfully.');
    }

    /**
     * Bulk delete users
     */
    public function bulkDestroy(Request $request)
    {
        $this->checkPermission('delete users');

        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:users,id'
        ]);

        try {
            $users = User::whereIn('id', $validated['ids'])->get();
            
            foreach ($users as $user) {
                if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
                    Storage::disk('public')->delete($user->profile_image);
                }
                $user->delete();
            }

            Log::info('Bulk users deletion by user: ' . auth()->id(), [
                'count' => count($validated['ids']),
                'deleted_ids' => $validated['ids']
            ]);

            return response()->json([
                'success' => true,
                'message' => count($validated['ids']) . ' users deleted successfully.'
            ]);

        } catch (\Exception $e) {
            Log::error('Bulk users deletion failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete users.'
            ], 500);
        }
    }
}