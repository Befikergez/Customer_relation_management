<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Models\RoleCommissionSetting;
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

        $users = User::with('roles')->latest()->paginate(10);
        $tables = NavigationService::getTablesForUser(auth()->user());
        $commissionSettings = RoleCommissionSetting::all()->keyBy('role_name');

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'tables' => $tables,
            'permissions' => $this->getCrudPermissions('users'),
            'commissionSettings' => $commissionSettings
        ]);
    }

    public function show(User $user)
    {
        $this->checkPermission('view users');

        $tables = NavigationService::getTablesForUser(auth()->user());
        $commissionSettings = RoleCommissionSetting::all()->keyBy('role_name');
        
        // Get user's commission info
        $commissionInfo = [
            'has_commission' => $user->has_commission,
            'commission_rate' => $user->commission_rate,
            'effective_commission_rate' => $user->effective_commission_rate,
            'can_edit_commission' => $user->canEditCommission()
        ];

        return Inertia::render('Admin/Users/Show', [
            'user' => $user->load('roles'),
            'tables' => $tables,
            'commissionInfo' => $commissionInfo,
            'commissionSettings' => $commissionSettings
        ]);
    }

    public function create()
    {
        $this->checkPermission('create users');

        $tables = NavigationService::getTablesForUser(auth()->user());
        $commissionSettings = RoleCommissionSetting::all()->keyBy('role_name');
        $roles = Role::all()->map(function($role) use ($commissionSettings) {
            $setting = $commissionSettings->get($role->name);
            return [
                'id' => $role->id,
                'name' => $role->name,
                'has_commission' => $setting ? $setting->has_commission : false,
                'is_commission_editable' => $setting ? $setting->is_commission_editable : true,
                'default_commission_rate' => $setting ? $setting->default_commission_rate : 0
            ];
        });

        return Inertia::render('Admin/Users/Create', [
            'roles' => $roles,
            'tables' => $tables,
            'commissionSettings' => $commissionSettings
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
            'commission_rate' => 'nullable|numeric|min:0|max:100|decimal:0,2',
        ]);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ];

        // Dynamic commission rate handling
        $commissionData = $this->getCommissionDataForRoles(
            $validated['commission_rate'] ?? null, 
            $validated['roles'] ?? []
        );
        
        $userData['commission_rate'] = $commissionData['commission_rate'];
        $userData['has_commission'] = $commissionData['has_commission'];

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
        $commissionSettings = RoleCommissionSetting::all()->keyBy('role_name');
        
        // Determine if commission field should be shown/editable
        $canEditCommission = $user->canEditCommission();
        $showCommissionField = $user->has_commission;

        $roles = Role::all()->map(function($role) use ($commissionSettings) {
            $setting = $commissionSettings->get($role->name);
            return [
                'id' => $role->id,
                'name' => $role->name,
                'has_commission' => $setting ? $setting->has_commission : false,
                'is_commission_editable' => $setting ? $setting->is_commission_editable : true,
                'default_commission_rate' => $setting ? $setting->default_commission_rate : 0
            ];
        });

        return Inertia::render('Admin/Users/Edit', [
            'user' => $user->load('roles'),
            'roles' => $roles,
            'userRoles' => $user->roles->pluck('name'),
            'tables' => $tables,
            'commissionSettings' => $commissionSettings,
            'canEditCommission' => $canEditCommission,
            'showCommissionField' => $showCommissionField
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
            'commission_rate' => 'nullable|numeric|min:0|max:100|decimal:0,2',
        ]);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($validated['password']);
        }

        // Dynamic commission rate handling
        $commissionData = $this->getCommissionDataForRoles(
            $validated['commission_rate'] ?? null, 
            $validated['roles'] ?? [],
            $user->commission_rate
        );
        
        $userData['commission_rate'] = $commissionData['commission_rate'];
        $userData['has_commission'] = $commissionData['has_commission'];

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

    /**
     * Update commission rate for user with commission-enabled roles
     */
    public function updateCommission(Request $request, User $user)
    {
        $this->checkPermission('edit users');

        $validated = $request->validate([
            'commission_rate' => 'required|numeric|min:0|max:100|decimal:0,2',
        ]);

        // Check if user has any roles that allow commission and if it's editable
        $userRoles = $user->roles->pluck('name')->toArray();
        $hasEditableCommissionRole = RoleCommissionSetting::whereIn('role_name', $userRoles)
            ->where('has_commission', true)
            ->where('is_commission_editable', true)
            ->exists();

        if (!$hasEditableCommissionRole) {
            return back()->with('error', 'Commission can only be set for users with commission-enabled and editable roles.');
        }

        $user->update(['commission_rate' => $validated['commission_rate']]);

        return back()->with('success', 'Commission rate updated successfully.');
    }

    /**
     * Helper method to determine commission data based on roles
     */
    private function getCommissionDataForRoles(?float $commissionRate, array $roles, ?float $currentRate = null): array
    {
        // If no roles selected, return default
        if (empty($roles)) {
            return [
                'commission_rate' => null,
                'has_commission' => false
            ];
        }

        // Get commission settings for all roles
        $commissionSettings = RoleCommissionSetting::whereIn('role_name', $roles)->get();
        
        $hasCommissionRole = $commissionSettings->where('has_commission', true)->isNotEmpty();
        $isCommissionEditable = $commissionSettings->where('is_commission_editable', true)->isNotEmpty();
        
        // If no roles have commission, return null
        if (!$hasCommissionRole) {
            return [
                'commission_rate' => null,
                'has_commission' => false
            ];
        }
        
        // If commission is provided and editable, use it
        if ($commissionRate !== null && $isCommissionEditable) {
            return [
                'commission_rate' => $commissionRate,
                'has_commission' => true
            ];
        }
        
        // If no commission provided but user has commission roles, use the highest default rate
        if ($commissionRate === null && $hasCommissionRole) {
            $defaultRate = $commissionSettings->where('has_commission', true)
                ->max('default_commission_rate');
                
            return [
                'commission_rate' => $defaultRate ?: 0,
                'has_commission' => true
            ];
        }
        
        // Return current data if no changes needed
        return [
            'commission_rate' => $currentRate,
            'has_commission' => $hasCommissionRole
        ];
    }

    /**
     * Get commission settings for roles (useful for frontend)
     */
    public function getCommissionSettings()
    {
        $this->checkPermission('view users');

        $commissionSettings = RoleCommissionSetting::all()->keyBy('role_name');

        return response()->json([
            'commission_settings' => $commissionSettings
        ]);
    }
    
    /**
     * Get users with commission
     */
    public function getUsersWithCommission()
    {
        $this->checkPermission('view users');
        
        $users = User::where('has_commission', true)
            ->with('roles')
            ->latest()
            ->paginate(20);
            
        $commissionSettings = RoleCommissionSetting::all()->keyBy('role_name');
        
        return Inertia::render('Admin/Commission/Users', [
            'users' => $users,
            'commissionSettings' => $commissionSettings
        ]);
    }
}