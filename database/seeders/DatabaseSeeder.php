<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create roles (idempotent)
        $roles = ['superadmin', 'admin', 'salesperson', 'customer', 'sales_manager'];
        foreach ($roles as $r) {
            Role::firstOrCreate(['name' => $r]);
        }

        // Create users and assign roles
        $users = [
            [
                'name' => 'superadmin',
                'email' => 'admin@example.com',
                'password' => 'password',
                'role' => 'superadmin',
            ],
            [
                'name' => 'salesperson',
                'email' => 'salesperson@example.com',
                'password' => '8567',
                'role' => 'salesperson',
            ],
            [
                'name' => 'customer',
                'email' => 'customer@example.com',
                'password' => '2002',
                'role' => 'customer',
            ],
            [
                'name' => 'sales_manager',
                'email' => 'salesmanager@example.com',
                'password' => '2018',
                'role' => 'sales_manager',
            ],
        ];

        foreach ($users as $u) {
            // If user exists, update passwords/role; otherwise create
            $user = User::updateOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'password' => Hash::make($u['password']),
                ]
            );

            // Assign role (Spatie)
            if (! $user->hasRole($u['role'])) {
                $user->assignRole($u['role']);
            }
        }

        
        // User::factory(10)->create();
        // Or a single test user (uncomment if you want it)
        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
