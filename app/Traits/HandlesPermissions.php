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
            if (in_array($resource, ['customers', 'contracts', 'proposals', 'opportunities', 'products'])) {
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
        
        return [
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
        ];
    }
}