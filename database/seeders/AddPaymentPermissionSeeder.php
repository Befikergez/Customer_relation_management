<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class AddPaymentPermissionSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            'create payments',
            'edit payments',
            'view payments',
            'delete payments',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}