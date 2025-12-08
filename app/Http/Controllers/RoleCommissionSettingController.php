<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\RoleCommissionSetting;
use Spatie\Permission\Models\Role;
use App\Traits\HandlesPermissions;
use App\Services\NavigationService;

class RoleCommissionSettingController extends Controller
{
    use HandlesPermissions;

    public function index()
    {
        $this->checkPermission('manage commission settings');

        $tables = NavigationService::getTablesForUser(auth()->user());
        $settings = RoleCommissionSetting::all();
        $roles = Role::all()->pluck('name');

        // Get roles that don't have settings yet
        $existingRoles = $settings->pluck('role_name')->toArray();
        $availableRoles = $roles->diff($existingRoles)->values();

        return Inertia::render('Admin/Commission/RoleSettings', [
            'settings' => $settings,
            'availableRoles' => $availableRoles,
            'tables' => $tables,
        ]);
    }

    public function store(Request $request)
    {
        $this->checkPermission('manage commission settings');

        $validated = $request->validate([
            'role_name' => 'required|string|max:255|unique:role_commission_settings',
            'has_commission' => 'boolean',
            'default_commission_rate' => 'nullable|numeric|min:0|max:100|decimal:0,2',
            'is_commission_editable' => 'boolean',
        ]);

        // Ensure role exists in Spatie roles
        $roleExists = Role::where('name', $validated['role_name'])->exists();
        if (!$roleExists) {
            return back()->with('error', 'Role does not exist in the system.');
        }

        RoleCommissionSetting::create($validated);

        return back()->with('success', 'Commission settings for role created successfully.');
    }

    public function update(Request $request, RoleCommissionSetting $setting)
    {
        $this->checkPermission('manage commission settings');

        $validated = $request->validate([
            'has_commission' => 'boolean',
            'default_commission_rate' => 'nullable|numeric|min:0|max:100|decimal:0,2',
            'is_commission_editable' => 'boolean',
        ]);

        $setting->update($validated);

        // Update users with this role
        $this->updateUsersCommissionStatus($setting->role_name, $validated['has_commission']);

        return back()->with('success', 'Commission settings updated successfully.');
    }

    public function destroy(RoleCommissionSetting $setting)
    {
        $this->checkPermission('manage commission settings');

        $setting->delete();

        return back()->with('success', 'Commission settings removed successfully.');
    }

    /**
     * Update users' commission status when role settings change
     */
    private function updateUsersCommissionStatus($roleName, $hasCommission)
    {
        $users = \App\Models\User::whereHas('roles', function ($query) use ($roleName) {
            $query->where('name', $roleName);
        })->get();

        foreach ($users as $user) {
            // Check if user has any other commission-enabled roles
            $userRoles = $user->roles->pluck('name')->toArray();
            $hasOtherCommissionRole = RoleCommissionSetting::whereIn('role_name', $userRoles)
                ->where('role_name', '!=', $roleName)
                ->where('has_commission', true)
                ->exists();

            if (!$hasOtherCommissionRole && !$hasCommission) {
                // User no longer has any commission roles
                $user->update([
                    'has_commission' => false,
                    'commission_rate' => null
                ]);
            } elseif ($hasCommission) {
                // User now has commission from this role
                $user->update(['has_commission' => true]);
            }
        }
    }
}