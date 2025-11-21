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
        'rejected_opportunities', // Changed from 'rejected opportunities'
        'contracts',
        'proposals',
        'potential_customers',
    ];

    protected $actions = ['view', 'create', 'edit', 'delete', 'approve', 'reject'];

    public function run()
    {
        // Create permissions
        foreach ($this->resources as $resource) {
            foreach ($this->actions as $action) {
                // Skip approve/reject for resources that don't need them
                if (in_array($action, ['approve', 'reject'])) {
                    if (in_array($resource, ['opportunities', 'potential_customers', 'proposals', 'contracts', 'customers'])) {
                        // These resources need approve/reject permissions
                        Permission::firstOrCreate(['name' => "{$action} {$resource}"]);
                    }
                    // Skip for other resources
                    continue;
                } else {
                    // Create view, create, edit, delete permissions for all resources
                    Permission::firstOrCreate(['name' => "{$action} {$resource}"]);
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
            'approve proposals', // ADDED
            'reject proposals',  // ADDED
            
            // Opportunities
            'view opportunities',
            'approve opportunities', // ADDED
            'reject opportunities',  // ADDED
            
            // Potential Customers
            'view potential_customers',
            'approve potential_customers',
            'reject potential_customers',
            'edit potential_customers',
            
            // Rejected Opportunities
            'view rejected_opportunities', // FIXED name
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
            'approve opportunities', // ADDED
            'reject opportunities',  // ADDED
            
            // Rejected Opportunities
            'view rejected_opportunities', // FIXED name
            
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
            'approve proposals', // ADDED
            'reject proposals',  // ADDED
            
            // Potential Customers
            'view potential_customers',
            'approve potential_customers', // ADDED
            'reject potential_customers',  // ADDED
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
        ];

        $customer->syncPermissions($customerPermissions);

        // Additional specific permissions
        $this->addSpecificPermissions();
    }

    protected function addSpecificPermissions()
    {
        // Add any additional specific permissions that don't fit the pattern
        $additionalPermissions = [
            // Add any custom permissions here if needed
        ];

        foreach ($additionalPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}