<?php

namespace App\Http\Controllers;

use App\Models\Industry;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\NavigationService;
use App\Traits\HandlesPermissions;
use Illuminate\Support\Facades\Log;

class IndustryController extends Controller
{
    use HandlesPermissions;

    public function index()
    {
        $this->checkPermission('view industries');

        $industries = Industry::latest()->paginate(10);
        $tables = NavigationService::getTablesForUser(auth()->user());

        return Inertia::render('Admin/Industries/Index', [
            'industries' => $industries,
            'tables' => $tables,
            'permissions' => $this->getCrudPermissions('industries')
        ]);
    }

    public function create()
    {
        $this->checkPermission('create industries');

        $tables = NavigationService::getTablesForUser(auth()->user());

        return Inertia::render('Admin/Industries/Create', [
            'tables' => $tables,
            'permissions' => $this->getCrudPermissions('industries')
        ]);
    }

    public function store(Request $request)
    {
        $this->checkPermission('create industries');

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:industries,name',
            'description' => 'nullable|string',
        ]);

        Industry::create($validated);

        // Return to settings page with only success message
        return redirect()->route('admin.settings')->with('success', 'Industry created successfully.');
    }

    public function edit(Industry $industry)
    {
        $this->checkPermission('edit industries');

        $tables = NavigationService::getTablesForUser(auth()->user());

        return Inertia::render('Admin/Industries/Edit', [
            'industry' => $industry,
            'tables' => $tables,
            'permissions' => $this->getCrudPermissions('industries')
        ]);
    }

    public function update(Request $request, Industry $industry)
    {
        $this->checkPermission('edit industries');

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:industries,name,' . $industry->id,
            'description' => 'nullable|string',
        ]);

        $industry->update($validated);

        // Return to settings page with only success message
        return redirect()->route('admin.settings')->with('success', 'Industry updated successfully.');
    }

    public function destroy(Industry $industry)
    {
        $this->checkPermission('delete industries');

        $industry->delete();

        // Return to settings page with only success message
        return redirect()->route('admin.settings')->with('success', 'Industry deleted successfully.');
    }
}