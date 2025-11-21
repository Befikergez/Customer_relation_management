<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;

class NavigationService
{
    public static function getTablesForUser(User $user)
    {
        $allTables = [
            ['name' => 'Users', 'route' => 'users.index', 'resource' => 'users'],
            ['name' => 'Roles', 'route' => 'roles.index', 'resource' => 'roles'],
            ['name' => 'Products', 'route' => 'products.index', 'resource' => 'products'],
            ['name' => 'Product Categories', 'route' => 'product-categories.index', 'resource' => 'product_categories'], // ADD THIS LINE
            ['name' => 'Customers', 'route' => 'customers.index', 'resource' => 'customers'],
            ['name' => 'Industries', 'route' => 'industries.index', 'resource' => 'industries'],
            ['name' => 'Opportunities', 'route' => 'opportunities.index', 'resource' => 'opportunities'],
            ['name' => 'Rejected Opportunities', 'route' => 'rejectedopportunities.index', 'resource' => 'rejected opportunities'],
            ['name' => 'Contracts', 'route' => 'contracts.index', 'resource' => 'contracts'],
            ['name' => 'Proposals', 'route' => 'proposals.index', 'resource' => 'proposals'],
             ['name' => 'Potential Customers', 'route' => 'potential-customers.index', 'resource' => 'potential_customers'],
        ];

        return array_values(array_filter($allTables, function ($table) use ($user) {
            return self::canViewResource($user, $table['resource']);
        }));
    }

    /**
     * Check if user can view a resource with multiple permission formats
     */
    protected static function canViewResource(User $user, $resource)
    {
        $permissions = [
            // Standard formats
            "view {$resource}",
            "{$resource}.view",
            "{$resource}s.view",
            "read {$resource}",
            
            // Singular formats
            Str::singular($resource) . ".view",
            
            // Alternative formats
            "{$resource} view",
            "show {$resource}",
            "{$resource}.read",
        ];
        
        foreach ($permissions as $permission) {
            if ($user->can($permission)) {
                return true;
            }
        }
        return false;
    }

    public static function getPermissionsForResource(string $resource): array
    {
        return [
            "view {$resource}",
            "create {$resource}",
            "edit {$resource}",
            "delete {$resource}",
        ];
    }

    /**
     * Get user permissions for dashboard or specific resource
     */
    public static function getUserPermissions(User $user, string $resource = null)
    {
        if ($resource) {
            return [
                'can_view' => self::canViewResource($user, $resource),
                'can_create' => $user->can("create {$resource}") || $user->can("{$resource}.create") || $user->can("{$resource}s.create"),
                'can_edit' => $user->can("edit {$resource}") || $user->can("{$resource}.edit") || $user->can("{$resource}s.edit") || $user->can("{$resource}.update"),
                'can_delete' => $user->can("delete {$resource}") || $user->can("{$resource}.delete") || $user->can("{$resource}s.delete"),
            ];
        }

        return [];
    }
}