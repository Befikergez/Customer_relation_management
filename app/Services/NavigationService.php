<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;

class NavigationService
{
    public static function getTablesForUser(User $user)
    {
        $allTables = [
            ['name' => 'Users', 'route' => 'admin.users.index', 'resource' => 'users'],
            ['name' => 'Roles', 'route' => 'admin.roles.index', 'resource' => 'roles'],
            ['name' => 'Products', 'route' => 'admin.products.index', 'resource' => 'products'],
            ['name' => 'Product Categories', 'route' => 'admin.product-categories.index', 'resource' => 'product_categories'],
            ['name' => 'Customers', 'route' => 'admin.customers.index', 'resource' => 'customers'],
            ['name' => 'Industries', 'route' => 'admin.industries.index', 'resource' => 'industries'],
            ['name' => 'Opportunities', 'route' => 'admin.opportunities.index', 'resource' => 'opportunities'],
            ['name' => 'Rejected Opportunities', 'route' => 'admin.rejectedopportunities.index', 'resource' => 'rejected_opportunities'],
            ['name' => 'Contracts', 'route' => 'admin.contracts.index', 'resource' => 'contracts'],
            ['name' => 'Proposals', 'route' => 'admin.proposals.index', 'resource' => 'proposals'],
            ['name' => 'Potential Customers', 'route' => 'admin.potential-customers.index', 'resource' => 'potential_customers'],
        ];

        return array_values(array_filter($allTables, function ($table) use ($user) {
            return self::canViewResource($user, $table['resource']);
        }));
    }

    /**
     * Check if user can view a resource with multiple permission formats
     */
    public static function canViewResource(User $user, $resource)
    {
        // Check if user has admin role first (superadmin should see everything)
        if (self::hasAdminRole($user)) {
            return true;
        }

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

    /**
     * Check if user has admin role
     */
    public static function hasAdminRole(User $user): bool
    {
        $adminRoles = ['superadmin', 'admin', 'administrator'];
        
        foreach ($adminRoles as $adminRole) {
            if ($user->hasRole($adminRole)) {
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

    /**
     * Check if user has search permission
     */
    public static function hasSearchPermission(User $user): bool
    {
        // Admin users always have search permission
        if (self::hasAdminRole($user)) {
            return true;
        }

        $searchPermissions = [
            'search',
            'search.view',
            'view_search',
            'view search',
            'global.search',
            'search.global',
            'view.search',
            'search.access',
        ];

        foreach ($searchPermissions as $permission) {
            if ($user->can($permission)) {
                return true;
            }
        }

        // Also check if user can view multiple resources (which implies search capability)
        $resources = ['customers', 'opportunities', 'potential_customers', 'proposals', 'contracts'];
        $viewableResources = 0;
        
        foreach ($resources as $resource) {
            if (self::canViewResource($user, $resource)) {
                $viewableResources++;
                if ($viewableResources >= 2) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if user has settings permission
     */
    public static function hasSettingsPermission(User $user): bool
    {
        // Admin users always have settings permission
        if (self::hasAdminRole($user)) {
            return true;
        }

        // Check direct settings permissions
        $settingsPermissions = [
            'settings',
            'settings.view',
            'view_settings',
            'view settings',
            'system.settings',
            'settings.system',
            'view.settings',
            'settings.access',
            'access_settings',
        ];

        foreach ($settingsPermissions as $permission) {
            if ($user->can($permission)) {
                return true;
            }
        }

        // Check if user can view any settings-related resource
        $settingsResources = ['users', 'roles', 'products', 'product_categories', 'industries'];
        
        foreach ($settingsResources as $resource) {
            if (self::canViewResource($user, $resource)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all permissions for the user in a flat array
     */
    public static function getAllPermissions(User $user): array
    {
        $allPermissions = [];
        
        // Add admin check
        $allPermissions['is_admin'] = self::hasAdminRole($user);
        $allPermissions['is_superadmin'] = $user->hasRole('superadmin');
        
        // Add search permission
        $searchPermission = self::hasSearchPermission($user);
        $allPermissions['search'] = $searchPermission;
        $allPermissions['view_search'] = $searchPermission;
        $allPermissions['search.view'] = $searchPermission;
        $allPermissions['view.search'] = $searchPermission;
        
        // Add settings permission
        $settingsPermission = self::hasSettingsPermission($user);
        $allPermissions['settings'] = $settingsPermission;
        $allPermissions['view_settings'] = $settingsPermission;
        $allPermissions['settings.view'] = $settingsPermission;
        $allPermissions['view.settings'] = $settingsPermission;
        
        // Add permissions for all resources
        $resources = [
            'users', 'roles', 'products', 'product_categories', 'customers', 'industries',
            'opportunities', 'rejected_opportunities', 'contracts', 'proposals', 'potential_customers'
        ];
        
        foreach ($resources as $resource) {
            $resourceKey = str_replace(' ', '_', $resource);
            
            // View permission
            $canView = self::canViewResource($user, $resource);
            $allPermissions["view_{$resourceKey}"] = $canView;
            $allPermissions["{$resourceKey}.view"] = $canView;
            $allPermissions["{$resourceKey}_view"] = $canView;
            $allPermissions["view {$resource}"] = $canView;
            
            // Create permission
            $canCreate = $user->can("create {$resource}") || $user->can("{$resource}.create") || $user->can("{$resource}s.create");
            $allPermissions["create_{$resourceKey}"] = $canCreate;
            $allPermissions["{$resourceKey}.create"] = $canCreate;
            $allPermissions["{$resourceKey}_create"] = $canCreate;
            $allPermissions["create {$resource}"] = $canCreate;
            
            // Edit permission
            $canEdit = $user->can("edit {$resource}") || $user->can("{$resource}.edit") || $user->can("{$resource}s.edit") || $user->can("{$resource}.update");
            $allPermissions["edit_{$resourceKey}"] = $canEdit;
            $allPermissions["{$resourceKey}.edit"] = $canEdit;
            $allPermissions["{$resourceKey}_edit"] = $canEdit;
            $allPermissions["edit {$resource}"] = $canEdit;
            
            // Delete permission
            $canDelete = $user->can("delete {$resource}") || $user->can("{$resource}.delete") || $user->can("{$resource}s.delete");
            $allPermissions["delete_{$resourceKey}"] = $canDelete;
            $allPermissions["{$resourceKey}.delete"] = $canDelete;
            $allPermissions["{$resourceKey}_delete"] = $canDelete;
            $allPermissions["delete {$resource}"] = $canDelete;
        }
        
        return $allPermissions;
    }

    /**
     * Get simplified permissions for sidebar
     */
    public static function getSidebarPermissions(User $user): array
    {
        return [
            'is_admin' => self::hasAdminRole($user),
            'is_superadmin' => $user->hasRole('superadmin'),
            'has_search' => self::hasSearchPermission($user),
            'has_settings' => self::hasSettingsPermission($user),
            'tables' => self::getTablesForUser($user),
        ];
    }
}