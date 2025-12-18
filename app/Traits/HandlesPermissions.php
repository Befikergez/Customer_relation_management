<?php

namespace App\Traits;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

trait HandlesPermissions
{
    /**
     * Admin role names (configurable)
     */
    protected function getAdminRoles()
    {
        return config('permission.admin_roles', ['admin', 'superadmin', 'administrator']);
    }

    /**
     * Permission formats to check (configurable)
     */
    protected function getPermissionFormats($permission)
    {
        $formats = [
            $permission, // Original: "view customers"
            str_replace(' ', '_', $permission), // "view_customers"
            str_replace(' ', '.', $permission), // "view.customers"
            strtolower($permission), // "view customers" (lowercase)
            strtolower(str_replace(' ', '_', $permission)), // "view_customers" (lowercase)
            strtolower(str_replace(' ', '.', $permission)), // "view.customers" (lowercase)
        ];

        // Add wildcard patterns based on permission structure
        $parts = explode(' ', $permission);
        if (count($parts) === 2) {
            [$action, $resource] = $parts;
            $resourceSingular = Str::singular($resource);
            
            // Add resource wildcards
            $formats[] = "{$resource}.*";
            $formats[] = "{$resourceSingular}.*";
            $formats[] = "*.{$action}";
            
            // Add specific patterns for common resources
            if (in_array($resource, ['customers', 'contracts', 'proposals', 'opportunities', 'products', 'settings', 'search'])) {
                $formats[] = "{$resource}.{$action}";
                $formats[] = "{$resourceSingular}.{$action}";
                $formats[] = "{$action}_{$resource}";
                $formats[] = "{$action}_{$resourceSingular}";
                $formats[] = "{$resource}_{$action}";
                $formats[] = "{$resourceSingular}_{$action}";
            }
        }

        return array_unique($formats);
    }

    /**
     * Check if user has any admin role
     */
    protected function userHasAdminRole($user)
    {
        foreach ($this->getAdminRoles() as $adminRole) {
            if ($user->hasRole($adminRole)) {
                Log::info("✓ User has admin role: {$adminRole}");
                return true;
            }
        }
        
        // Check case-insensitive
        $userRoles = $user->getRoleNames()->map(fn($role) => strtolower($role))->toArray();
        $adminRolesLower = array_map('strtolower', $this->getAdminRoles());
        
        foreach ($userRoles as $userRole) {
            if (in_array($userRole, $adminRolesLower)) {
                Log::info("✓ User has admin role (case-insensitive): {$userRole}");
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check if user has permission
     */
    protected function checkPermission($permission)
    {
        $user = auth()->user();
        
        Log::info("=== PERMISSION CHECK: {$permission} ===");
        Log::info("User: {$user->name} (ID: {$user->id})");
        
        // If user has admin role, grant permission
        if ($this->userHasAdminRole($user)) {
            Log::info("✓ Permission granted via admin role: {$permission}");
            return;
        }
        
        // Special handling for search permission
        if ($this->isSearchPermission($permission) && $this->hasImplicitSearchAccess($user)) {
            Log::info("✓ Permission granted via implicit search access: {$permission}");
            return;
        }
        
        // Special handling for settings permission
        if ($this->isSettingsPermission($permission) && $this->hasSettingsAccessViaSubPermissions($user)) {
            Log::info("✓ Permission granted via settings sub-permissions: {$permission}");
            return;
        }
        
        // Get all user permissions
        $allUserPermissions = $user->getAllPermissions()->pluck('name')->toArray();
        Log::info("Total permissions: " . count($allUserPermissions));
        
        // Get formats to check
        $formatsToCheck = $this->getPermissionFormats($permission);
        Log::info("Checking formats: " . json_encode($formatsToCheck));
        
        // Check using direct array search
        foreach ($formatsToCheck as $format) {
            if (in_array($format, $allUserPermissions)) {
                Log::info("✓ Permission found in array: {$format}");
                return;
            }
        }
        
        // Check using can() method
        foreach ($formatsToCheck as $format) {
            try {
                if ($user->can($format)) {
                    Log::info("✓ Permission found via can(): {$format}");
                    return;
                }
            } catch (\Exception $e) {
                Log::warning("Error checking can('{$format}'): " . $e->getMessage());
            }
        }
        
        // Special handling for approve/reject permissions
        if ($this->shouldFallbackToEdit($permission)) {
            $editPermission = $this->getEditPermissionFallback($permission);
            if ($user->can($editPermission)) {
                Log::info("✓ Granting {$permission} via edit permission: {$editPermission}");
                return;
            }
        }
        
        // Check for wildcard permissions
        if ($this->checkWildcardPermissions($user, $permission)) {
            return;
        }
        
        Log::error("❌ Permission DENIED: {$permission}");
        Log::error("User permissions: " . json_encode($allUserPermissions));
        Log::error("User roles: " . json_encode($user->getRoleNames()->toArray()));
        
        abort(403, "You do not have permission to {$permission}.");
    }

    /**
     * Check if it's a search permission
     */
    protected function isSearchPermission($permission)
    {
        return Str::contains(strtolower($permission), 'search');
    }

    /**
     * Check if user has implicit search access
     * Users should have search access if they can view at least 2 different resources
     */
    protected function hasImplicitSearchAccess($user)
    {
        $resourcesToCheck = [
            'customers', 'opportunities', 'potential_customers', 
            'proposals', 'contracts', 'products', 'rejected_opportunities'
        ];
        
        $viewableResources = 0;
        
        foreach ($resourcesToCheck as $resource) {
            if ($this->checkPermissionForResource($user, 'view', $resource)) {
                $viewableResources++;
                if ($viewableResources >= 2) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Check if it's a settings permission
     */
    protected function isSettingsPermission($permission)
    {
        return Str::contains(strtolower($permission), 'settings');
    }

    /**
     * Check if user has settings access via sub-permissions
     * Users should have settings access if they can view any settings-related resource
     */
    protected function hasSettingsAccessViaSubPermissions($user)
    {
        $settingsResources = [
            'users', 'roles', 'products', 'product_categories', 'industries'
        ];
        
        foreach ($settingsResources as $resource) {
            if ($this->checkPermissionForResource($user, 'view', $resource)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check if permission should fallback to edit permission
     */
    protected function shouldFallbackToEdit($permission)
    {
        $actions = ['approve', 'reject', 'accept', 'decline'];
        foreach ($actions as $action) {
            if (Str::contains(strtolower($permission), $action)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get edit permission for fallback
     */
    protected function getEditPermissionFallback($permission)
    {
        $patterns = [
            '/approve/' => 'edit',
            '/reject/' => 'edit',
            '/accept/' => 'edit',
            '/decline/' => 'edit',
        ];
        
        return preg_replace(array_keys($patterns), array_values($patterns), $permission);
    }

    /**
     * Check for wildcard permissions
     */
    protected function checkWildcardPermissions($user, $permission)
    {
        $parts = explode(' ', $permission);
        if (count($parts) !== 2) {
            return false;
        }
        
        [$action, $resource] = $parts;
        $resourceSingular = Str::singular($resource);
        
        // Check wildcard patterns
        $wildcards = [
            '*', // Global wildcard
            "{$resource}.*", // Resource wildcard
            "{$resourceSingular}.*", // Singular resource wildcard
            "*.{$action}", // Action wildcard
        ];
        
        foreach ($wildcards as $wildcard) {
            if ($user->can($wildcard)) {
                Log::info("✓ Permission granted via wildcard: {$wildcard}");
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get extended permissions for a resource
     */
    protected function getExtendedPermissions($resource)
    {
        $user = auth()->user();
        
        // Define all possible actions
        $actions = ['view', 'create', 'edit', 'delete', 'approve', 'reject'];
        $permissions = [];
        
        foreach ($actions as $action) {
            $permissionString = "{$action} {$resource}";
            $permissions[$action] = $this->checkPermissionForResource($user, $action, $resource);
        }
        
        return $permissions;
    }

    /**
     * Check if user has permission for a specific resource action
     */
    protected function checkPermissionForResource($user, $action, $resource)
    {
        // Check admin role first
        if ($this->userHasAdminRole($user)) {
            return true;
        }
        
        // Special handling for search permissions
        if ($resource === 'search' && $action === 'view') {
            return $this->hasImplicitSearchAccess($user) || 
                   $this->checkExplicitPermission($user, $action, $resource);
        }
        
        // Special handling for settings permissions
        if ($resource === 'settings' && $action === 'view') {
            return $this->hasSettingsAccessViaSubPermissions($user) || 
                   $this->checkExplicitPermission($user, $action, $resource);
        }
        
        // Check explicit permission for other resources
        return $this->checkExplicitPermission($user, $action, $resource);
    }

    /**
     * Check explicit permission for a resource action
     */
    protected function checkExplicitPermission($user, $action, $resource)
    {
        // Generate all possible permission strings for this action/resource
        $permissionStrings = $this->generatePermissionStrings($action, $resource);
        
        // Check each permission string
        foreach ($permissionStrings as $permissionString) {
            if ($user->can($permissionString)) {
                return true;
            }
        }
        
        // For approve/reject actions, check edit permission as fallback
        if (in_array($action, ['approve', 'reject', 'accept', 'decline'])) {
            $editPermissions = $this->generatePermissionStrings('edit', $resource);
            foreach ($editPermissions as $editPermission) {
                if ($user->can($editPermission)) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Generate all possible permission string formats for an action/resource
     */
    protected function generatePermissionStrings($action, $resource)
    {
        $resourceSingular = Str::singular($resource);
        
        $permissionStrings = [
            // Standard formats
            "{$action} {$resource}",
            "{$resource}.{$action}",
            "{$action}_{$resource}",
            "{$resource}_{$action}",
            
            // Singular resource formats
            "{$action} {$resourceSingular}",
            "{$resourceSingular}.{$action}",
            "{$action}_{$resourceSingular}",
            "{$resourceSingular}_{$action}",
            
            // Alternative formats
            str_replace(' ', '.', "{$action} {$resource}"),
            str_replace(' ', '_', "{$action} {$resource}"),
            strtolower("{$action} {$resource}"),
            strtolower("{$resource}.{$action}"),
            strtolower("{$action}_{$resource}"),
            
            // Common permission aliases
            $action === 'view' ? "read {$resource}" : null,
            $action === 'view' ? "{$resource}.read" : null,
            $action === 'edit' ? "update {$resource}" : null,
            $action === 'edit' ? "{$resource}.update" : null,
        ];

        // Add specific permission patterns for settings and search
        if ($resource === 'settings' || $resource === 'search') {
            $permissionStrings[] = $resource; // Just "settings" or "search"
            $permissionStrings[] = "{$resource}.access";
            $permissionStrings[] = "access_{$resource}";
            $permissionStrings[] = "{$resource}_access";
        }

        // Filter out null values
        return array_filter($permissionStrings);
    }

    /**
     * Get all permissions for the current user (for debugging)
     */
    public function getUserPermissionsDebug()
    {
        $user = auth()->user();
        
        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'has_admin_role' => $this->userHasAdminRole($user),
            'admin_roles_configured' => $this->getAdminRoles(),
            'has_implicit_search_access' => $this->hasImplicitSearchAccess($user),
            'has_settings_access_via_sub_perms' => $this->hasSettingsAccessViaSubPermissions($user),
        ];
    }

    /**
     * Check if user can access settings based on sub-permissions
     */
    public function canAccessSettings($user = null)
    {
        $user = $user ?: auth()->user();
        
        if ($this->userHasAdminRole($user)) {
            return true;
        }
        
        return $this->hasSettingsAccessViaSubPermissions($user) || 
               $this->checkExplicitPermission($user, 'view', 'settings');
    }

    /**
     * Check if user can access search based on permissions or implicit access
     */
    public function canAccessSearch($user = null)
    {
        $user = $user ?: auth()->user();
        
        if ($this->userHasAdminRole($user)) {
            return true;
        }
        
        return $this->hasImplicitSearchAccess($user) || 
               $this->checkExplicitPermission($user, 'view', 'search');
    }
}