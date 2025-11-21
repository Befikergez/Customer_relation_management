<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\NavigationService;
use App\Traits\HandlesPermissions;
use Illuminate\Support\Facades\Log;

class ProductCategoryController extends Controller
{
    use HandlesPermissions;

    public function index()
    {
        $this->checkPermission('view product_categories');

        $categories = ProductCategory::with(['creator', 'products'])->latest()->paginate(10);
        $tables = NavigationService::getTablesForUser(auth()->user());

        return Inertia::render('Admin/ProductCategories/Index', [
            'categories' => $categories,
            'tables' => $tables,
            'permissions' => $this->getCrudPermissions('product_categories')
        ]);
    }

    public function create()
    {
        $this->checkPermission('create product_categories');

        $tables = NavigationService::getTablesForUser(auth()->user());

        return Inertia::render('Admin/ProductCategories/Create', [
            'tables' => $tables,
            'permissions' => $this->getCrudPermissions('product_categories')
        ]);
    }

    public function store(Request $request)
    {
        $this->checkPermission('create product_categories');

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:product_categories,name',
            'description' => 'nullable|string',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['is_active'] = true;

        ProductCategory::create($validated);
        
        // Return to settings page with only success message
        return redirect()->route('admin.settings')->with('success', 'Product category created successfully.');
    }

    public function edit(ProductCategory $productCategory)
    {
        $this->checkPermission('edit product_categories');

        $tables = NavigationService::getTablesForUser(auth()->user());

        return Inertia::render('Admin/ProductCategories/Edit', [
            'category' => $productCategory,
            'tables' => $tables,
            'permissions' => $this->getCrudPermissions('product_categories')
        ]);
    }

    public function update(Request $request, ProductCategory $productCategory)
    {
        $this->checkPermission('edit product_categories');

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:product_categories,name,' . $productCategory->id,
            'description' => 'nullable|string',
            'is_active' => 'required|boolean',
        ]);

        $productCategory->update($validated);
        
        // Return to settings page with only success message
        return redirect()->route('admin.settings')->with('success', 'Product category updated successfully.');
    }

    public function destroy(ProductCategory $productCategory)
    {
        $this->checkPermission('delete product_categories');

        // Check if category has products
        if ($productCategory->products()->count() > 0) {
            return redirect()->route('admin.settings')->with('error', 'Cannot delete category that has products assigned.');
        }

        $productCategory->delete();
        
        // Return to settings page with only success message
        return redirect()->route('admin.settings')->with('success', 'Product category deleted successfully.');
    }
}