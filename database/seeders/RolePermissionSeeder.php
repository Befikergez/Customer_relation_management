<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    protected $resources = [
        'users',
        'roles',
        'products',
        'product_categories',
        'customers',
        'industries',
        'opportunities',
        'rejected_opportunities',
        'contracts',
        'proposals',
        'potential_customers',
        // Settings resources
        'settings',
        'search',
    ];

    protected $actions = ['view', 'create', 'edit', 'delete', 'approve', 'reject'];

    public function run()
    {
        // Create permissions for all resources
        foreach ($this->resources as $resource) {
            foreach ($this->actions as $action) {
                // Skip approve/reject for resources that don't need them
                if (in_array($action, ['approve', 'reject'])) {
                    if (in_array($resource, ['opportunities', 'potential_customers', 'proposals', 'contracts', 'customers'])) {
                        // These resources need approve/reject permissions
                        Permission::firstOrCreate(['name' => "{$action} {$resource}"]);
                    }
                    // For settings and search, don't create approve/reject permissions
                    elseif (!in_array($resource, ['settings', 'search'])) {
                        continue;
                    }
                } else {
                    // Create view, create, edit, delete permissions for all resources
                    // But skip create/edit/delete for settings and search if not applicable
                    if (in_array($resource, ['settings', 'search'])) {
                        // For settings and search, only create view permission
                        if ($action === 'view') {
                            Permission::firstOrCreate(['name' => "{$action} {$resource}"]);
                        }
                    } else {
                        Permission::firstOrCreate(['name' => "{$action} {$resource}"]);
                    }
                }
            }
        }

        // Assign permissions to roles
        $superadmin = Role::firstOrCreate(['name' => 'superadmin']);
        $salesManager = Role::firstOrCreate(['name' => 'sales_manager']);
        $salesperson = Role::firstOrCreate(['name' => 'salesperson']);
        $customer = Role::firstOrCreate(['name' => 'customer']);

        // Superadmin gets all permissions
        $superadmin->syncPermissions(Permission::all());

        // Sales Manager permissions
        $salesManagerPermissions = [
            // Products
            'view products',
            'view product_categories',
            
            // Customers
            'view customers',
            'reject customers',
            
            // Industries
            'view industries',
            
            // Contracts
            'view contracts',
            
            // Proposals
            'view proposals',
            'edit proposals',
            'approve proposals',
            'reject proposals',
            
            // Opportunities
            'view opportunities',
            'approve opportunities',
            'reject opportunities',
            
            // Potential Customers
            'view potential_customers',
            'approve potential_customers',
            'reject potential_customers',
            'edit potential_customers',
            
            // Rejected Opportunities
            'view rejected_opportunities',
            
            // Settings & Search
            'view settings',
            'view search',
        ];

        $salesManager->syncPermissions($salesManagerPermissions);

        // Salesperson permissions
        $salespersonPermissions = [
            // Products
            'view products',
            'view product_categories',
            
            // Customers
            'view customers',
            'create customers',
            'edit customers',
            'delete customers',
            'reject customers',
            
            // Industries
            'view industries',
            'create industries',
            'edit industries',
            'delete industries',
            
            // Opportunities
            'view opportunities',
            'create opportunities',
            'edit opportunities',
            'delete opportunities',
            'approve opportunities',
            'reject opportunities',
            
            // Rejected Opportunities
            'view rejected_opportunities',
            
            // Contracts
            'view contracts',
            'create contracts',
            'edit contracts',
            'delete contracts',
            
            // Proposals
            'view proposals',
            'create proposals',
            'edit proposals',
            'delete proposals',
            'approve proposals',
            'reject proposals',
            
            // Potential Customers
            'view potential_customers',
            'approve potential_customers',
            'reject potential_customers',
            
            // Settings & Search
            'view search', // Salesperson can search but not access settings
        ];

        $salesperson->syncPermissions($salespersonPermissions);

        // Customer permissions
        $customerPermissions = [
            // Contracts
            'view contracts',
            'approve contracts',
            'reject contracts',
            
            // Proposals
            'view proposals',
            'approve proposals',
            'reject proposals',
            
            // Settings & Search - Customers can only search
            'view search',
        ];

        $customer->syncPermissions($customerPermissions);

        // Additional specific permissions
        $this->addSpecificPermissions();
    }

    protected function addSpecificPermissions()
    {
        // Add any additional specific permissions that don't fit the pattern
        $additionalPermissions = [
            // Dashboard access
            'view dashboard',
            
            // Alternative permission formats for compatibility
            'settings.view',
            'search.view',
            'view_settings',
            'view_search',
            'settings.access',
            'search.access',
            
            // Global permissions
            'access_admin',
            'view_reports',
            
            // User management specific
            'manage_users',
            'assign_roles',
            
            // Data management
            'export_data',
            'import_data',
            
            // System operations
            'clear_cache',
            'view_logs',
            'backup_system',
            
            // Communication
            'send_bulk_emails',
            'manage_templates',
            
            // Notifications
            'manage_notifications',
            'send_notifications',
        ];

        foreach ($additionalPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
        
        // Assign some additional permissions to roles
        $superadmin = Role::where('name', 'superadmin')->first();
        $salesManager = Role::where('name', 'sales_manager')->first();
        
        if ($superadmin) {
            $superadmin->givePermissionTo([
                'access_admin',
                'view_reports',
                'manage_users',
                'assign_roles',
                'export_data',
                'import_data',
                'clear_cache',
                'view_logs',
                'backup_system',
                'send_bulk_emails',
                'manage_templates',
                'manage_notifications',
                'send_notifications',
            ]);
        }
        
        if ($salesManager) {
            $salesManager->givePermissionTo([
                'access_admin',
                'view_reports',
                'export_data',
                'send_bulk_emails',
                'manage_templates',
                'send_notifications',
            ]);
        }
    }
}