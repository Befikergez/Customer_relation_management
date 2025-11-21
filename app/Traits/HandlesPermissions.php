<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HandlesPermissions
{
    protected function checkPermission($permission)
    {
        if (!auth()->user()->can($permission)) {
            abort(403, 'Unauthorized action.');
        }
    }

    protected function getCrudPermissions($resource)
    {
        $user = auth()->user();
        
        $permissions = [
            'view' => $user->can("view {$resource}") || 
                      $user->can("{$resource}.view") ||
                      $user->can("read {$resource}"),
            
            'create' => $user->can("create {$resource}") || 
                        $user->can("{$resource}.create"),
            
            'edit' => $user->can("edit {$resource}") || 
                      $user->can("{$resource}.edit") ||
                      $user->can("{$resource}.update"),
            
            'delete' => $user->can("delete {$resource}") || 
                        $user->can("{$resource}.delete"),
        ];

        return $permissions;
    }

    protected function getExtendedPermissions($resource)
    {
        $user = auth()->user();
        
        $permissions = [
            'view' => $user->can("view {$resource}") || 
                      $user->can("{$resource}.view") ||
                      $user->can("read {$resource}"),
            
            'create' => $user->can("create {$resource}") || 
                        $user->can("{$resource}.create"),
            
            'edit' => $user->can("edit {$resource}") || 
                      $user->can("{$resource}.edit") ||
                      $user->can("{$resource}.update"),
            
            'delete' => $user->can("delete {$resource}") || 
                        $user->can("{$resource}.delete"),
            
            'approve' => $user->can("approve {$resource}") || 
                         $user->can("{$resource}.approve"),
            
            'reject' => $user->can("reject {$resource}") || 
                        $user->can("{$resource}.reject"),
        ];

        return $permissions;
    }
}