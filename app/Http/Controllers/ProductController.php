<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Industry;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\NavigationService;
use App\Traits\HandlesPermissions;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    use HandlesPermissions;

    public function index()
    {
        $this->checkPermission('view products');

        $products = Product::with(['creator', 'category', 'industry'])->latest()->paginate(10);
        $tables = NavigationService::getTablesForUser(auth()->user());

        return Inertia::render('Admin/Products/Index', [
            'products' => $products,
            'tables' => $tables,
            'permissions' => $this->getCrudPermissions('products')
        ]);
    }

    public function create()
    {
        $this->checkPermission('create products');

        $categories = ProductCategory::active()->get();
        $industries = Industry::all();
        $tables = NavigationService::getTablesForUser(auth()->user());

        return Inertia::render('Admin/Products/Create', [
            'categories' => $categories,
            'industries' => $industries,
            'tables' => $tables,
            'permissions' => $this->getCrudPermissions('products')
        ]);
    }

    public function store(Request $request)
    {
        $this->checkPermission('create products');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:product_categories,id',
            'industry_id' => 'nullable|exists:industries,id',
        ]);

        $validated['created_by'] = auth()->id();

        Product::create($validated);
        
        // Return to settings page with only success message
        return redirect()->route('admin.settings')->with('success', 'Product added successfully.');
    }

    public function edit(Product $product)
    {
        $this->checkPermission('edit products');

        $categories = ProductCategory::active()->get();
        $industries = Industry::all();
        $tables = NavigationService::getTablesForUser(auth()->user());

        return Inertia::render('Admin/Products/Edit', [
            'product' => $product->load(['category', 'industry']),
            'categories' => $categories,
            'industries' => $industries,
            'tables' => $tables,
            'permissions' => $this->getCrudPermissions('products')
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $this->checkPermission('edit products');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:product_categories,id',
            'industry_id' => 'nullable|exists:industries,id',
        ]);

        $product->update($validated);
        
        // Return to settings page with only success message
        return redirect()->route('admin.settings')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $this->checkPermission('delete products');

        $product->delete();
        
        // Return to settings page with only success message
        return redirect()->route('admin.settings')->with('success', 'Product deleted successfully.');
    }
}