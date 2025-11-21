<?php
// app/Http/Controllers/SettingsController.php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Industry;
use App\Services\NavigationService;
use Inertia\Inertia;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $data = [
            'users_count' => User::count(),
            'roles_count' => $this->getRolesCount(),
            'products_count' => Product::count(),
            'categories_count' => ProductCategory::count(),
            'industries_count' => Industry::count(),
            'active_users_count' => $this->getActiveUsersCount(),
            'active_products_count' => $this->getActiveProductsCount(),
            'active_categories_count' => $this->getActiveCategoriesCount(),
            'active_industries_count' => $this->getActiveIndustriesCount(),
            'tables' => $this->getFilteredTablesForUser($user),
            // Add data for all sections
            'users_data' => $this->getUsersData(),
            'roles_data' => $this->getRolesData(),
            'products_data' => $this->getProductsData(),
            'categories_data' => $this->getCategoriesData(),
            'industries_data' => $this->getIndustriesData(),
            // Additional data for forms
            'all_roles' => $this->getAllRoles(),
            'all_permissions' => $this->getAllPermissions(),
            'all_categories' => $this->getAllCategories(),
            'all_industries' => $this->getAllIndustries(),
        ];

        return Inertia::render('Admin/Settings', $data);
    }

    /**
     * Get filtered tables for user (exclude settings tables from sidebar)
     */
    protected function getFilteredTablesForUser($user)
    {
        try {
            $allTables = NavigationService::getTablesForUser($user);
            
            if (!is_array($allTables)) {
                return [];
            }
            
            $settingsTables = ['Users', 'Roles', 'Products', 'Product Categories', 'Industries'];
            
            $filteredTables = array_filter($allTables, function($table) use ($settingsTables) {
                return is_array($table) && 
                       isset($table['name']) && 
                       !in_array($table['name'], $settingsTables);
            });
            
            return array_values($filteredTables);
            
        } catch (\Exception $e) {
            Log::error('Error getting filtered tables: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get roles count safely
     */
    protected function getRolesCount()
    {
        try {
            if (class_exists('Spatie\Permission\Models\Role') && Schema::hasTable('roles')) {
                return \Spatie\Permission\Models\Role::count();
            }
            return 0;
        } catch (\Exception $e) {
            Log::error('Error getting roles count: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get active users count
     */
    protected function getActiveUsersCount()
    {
        try {
            if (Schema::hasColumn('users', 'is_active')) {
                return User::where('is_active', true)->count();
            }
            return User::count();
        } catch (\Exception $e) {
            return User::count();
        }
    }

    /**
     * Get active products count
     */
    protected function getActiveProductsCount()
    {
        try {
            if (Schema::hasColumn('products', 'is_active')) {
                return Product::where('is_active', true)->count();
            }
            return Product::count();
        } catch (\Exception $e) {
            return Product::count();
        }
    }

    /**
     * Get active categories count
     */
    protected function getActiveCategoriesCount()
    {
        try {
            if (Schema::hasColumn('product_categories', 'is_active')) {
                return ProductCategory::where('is_active', true)->count();
            }
            return ProductCategory::count();
        } catch (\Exception $e) {
            return ProductCategory::count();
        }
    }

    /**
     * Get active industries count
     */
    protected function getActiveIndustriesCount()
    {
        try {
            if (Schema::hasColumn('industries', 'is_active')) {
                return Industry::where('is_active', true)->count();
            }
            return Industry::count();
        } catch (\Exception $e) {
            return Industry::count();
        }
    }

    /**
     * Get users data - increased limit for better display
     */
    protected function getUsersData()
    {
        try {
            return User::with(['roles'])
                ->latest()
                ->limit(12) // Increased from 6 to show more users
                ->get()
                ->map(function($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'is_active' => $user->is_active ?? true,
                        'roles' => $user->roles,
                        'profile_image_url' => $user->profile_image ? asset('storage/' . $user->profile_image) : null,
                        'profile_image' => $user->profile_image,
                        'created_at' => $user->created_at,
                    ];
                });
        } catch (\Exception $e) {
            Log::error('Error getting users data: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get roles data
     */
    protected function getRolesData()
    {
        try {
            if (class_exists('Spatie\Permission\Models\Role') && Schema::hasTable('roles')) {
                return \Spatie\Permission\Models\Role::with(['permissions'])
                    ->latest()
                    ->get()
                    ->map(function($role) {
                        return [
                            'id' => $role->id,
                            'name' => $role->name,
                            'permissions' => $role->permissions,
                            'created_at' => $role->created_at,
                        ];
                    });
            }
            return [];
        } catch (\Exception $e) {
            Log::error('Error getting roles data: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get products data - increased limit for better display
     */
    protected function getProductsData()
    {
        try {
            return Product::with(['category', 'industry', 'creator'])
                ->latest()
                ->limit(12) // Increased from 6 to show more products
                ->get()
                ->map(function($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'description' => $product->description,
                        'category_id' => $product->category_id,
                        'industry_id' => $product->industry_id,
                        'category' => $product->category,
                        'industry' => $product->industry,
                        'creator' => $product->creator,
                        'created_at' => $product->created_at,
                    ];
                });
        } catch (\Exception $e) {
            Log::error('Error getting products data: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get categories data
     */
    protected function getCategoriesData()
    {
        try {
            return ProductCategory::latest()
                ->get()
                ->map(function($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'description' => $category->description,
                        'is_active' => $category->is_active ?? true,
                        'created_at' => $category->created_at,
                    ];
                });
        } catch (\Exception $e) {
            Log::error('Error getting categories data: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get industries data
     */
    protected function getIndustriesData()
    {
        try {
            return Industry::latest()
                ->get()
                ->map(function($industry) {
                    return [
                        'id' => $industry->id,
                        'name' => $industry->name,
                        'description' => $industry->description,
                        'created_at' => $industry->created_at,
                    ];
                });
        } catch (\Exception $e) {
            Log::error('Error getting industries data: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all roles for user forms
     */
    protected function getAllRoles()
    {
        try {
            if (class_exists('Spatie\Permission\Models\Role') && Schema::hasTable('roles')) {
                return \Spatie\Permission\Models\Role::all()
                    ->map(function($role) {
                        return [
                            'id' => $role->id,
                            'name' => $role->name,
                        ];
                    });
            }
            return [];
        } catch (\Exception $e) {
            Log::error('Error getting all roles: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all permissions for role forms
     */
    protected function getAllPermissions()
    {
        try {
            if (class_exists('Spatie\Permission\Models\Permission') && Schema::hasTable('permissions')) {
                return \Spatie\Permission\Models\Permission::all()
                    ->map(function($permission) {
                        return [
                            'id' => $permission->id,
                            'name' => $permission->name,
                        ];
                    });
            }
            return [];
        } catch (\Exception $e) {
            Log::error('Error getting all permissions: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all categories for product forms
     */
    protected function getAllCategories()
    {
        try {
            return ProductCategory::where('is_active', true)
                ->get()
                ->map(function($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                    ];
                });
        } catch (\Exception $e) {
            Log::error('Error getting all categories: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all industries for product forms
     */
    protected function getAllIndustries()
    {
        try {
            return Industry::all()
                ->map(function($industry) {
                    return [
                        'id' => $industry->id,
                        'name' => $industry->name,
                    ];
                });
        } catch (\Exception $e) {
            Log::error('Error getting all industries: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Handle AJAX requests for specific data (optional - for better performance)
     */
    public function getData($type)
    {
        try {
            switch ($type) {
                case 'users':
                    return response()->json([
                        'users_data' => $this->getUsersData(),
                        'users_count' => User::count(),
                    ]);
                
                case 'roles':
                    return response()->json([
                        'roles_data' => $this->getRolesData(),
                        'roles_count' => $this->getRolesCount(),
                    ]);
                
                case 'products':
                    return response()->json([
                        'products_data' => $this->getProductsData(),
                        'products_count' => Product::count(),
                    ]);
                
                case 'categories':
                    return response()->json([
                        'categories_data' => $this->getCategoriesData(),
                        'categories_count' => ProductCategory::count(),
                    ]);
                
                case 'industries':
                    return response()->json([
                        'industries_data' => $this->getIndustriesData(),
                        'industries_count' => Industry::count(),
                    ]);
                
                default:
                    return response()->json(['error' => 'Invalid data type'], 400);
            }
        } catch (\Exception $e) {
            Log::error('Error getting data for type ' . $type . ': ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load data'], 500);
        }
    }
}