<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Services\NavigationService;
use App\Traits\HandlesPermissions;
use Illuminate\Support\Facades\Log;

class RolesController extends Controller
{
    use HandlesPermissions;

    public function index()
    {
        $this->checkPermission('view roles');

        $roles = Role::with('permissions')->get();
        $tables = NavigationService::getTablesForUser(auth()->user());

        return Inertia::render('Admin/Roles/Index', [
            'roles' => $roles,
            'permissions' => $this->getCrudPermissions('roles'),
            'tables' => $tables
        ]);
    }

    public function create()
    {
        $this->checkPermission('create roles');

        $permissions = Permission::all();
        $tables = NavigationService::getTablesForUser(auth()->user());

        return Inertia::render('Admin/Roles/Create', [
            'permissions' => $permissions,
            'tables' => $tables
        ]);
    }

    public function store(Request $request)
    {
        $this->checkPermission('create roles');

        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'array'
        ]);

        $role = Role::create(['name' => $request->name]);
        $role->syncPermissions($request->permissions ?? []);

        // Return to settings page with only success message
        return redirect()->route('admin.settings')->with('success', 'Role created successfully.');
    }

    public function show(Role $role)
    {
        $this->checkPermission('view roles');

        $tables = NavigationService::getTablesForUser(auth()->user());

        return Inertia::render('Admin/Roles/Show', [
            'role' => $role->load('permissions'),
            'tables' => $tables
        ]);
    }

    public function edit(Role $role)
    {
        $this->checkPermission('edit roles');

        $permissions = Permission::all();
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        $tables = NavigationService::getTablesForUser(auth()->user());

        return Inertia::render('Admin/Roles/Edit', [
            'role' => $role,
            'permissions' => $permissions,
            'rolePermissions' => $rolePermissions,
            'tables' => $tables
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $this->checkPermission('edit roles');

        $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
            'permissions' => 'array'
        ]);

        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions ?? []);

        // Return to settings page with only success message
        return redirect()->route('admin.settings')->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        $this->checkPermission('delete roles');

        $role->delete();
        
        // Return to settings page with only success message
        return redirect()->route('admin.settings')->with('success', 'Role deleted successfully.');
    }
}