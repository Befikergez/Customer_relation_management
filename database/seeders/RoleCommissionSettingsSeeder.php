<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RoleCommissionSetting;

class RoleCommissionSettingsSeeder extends Seeder
{
    public function run()
    {
        $settings = [
            [
                'role_name' => 'salesperson',
                'has_commission' => true,
                'default_commission_rate' => 10.00,
                'is_commission_editable' => true,
            ],
            [
                'role_name' => 'sales-manager',
                'has_commission' => true,
                'default_commission_rate' => 5.00,
                'is_commission_editable' => true,
            ],
            [
                'role_name' => 'admin',
                'has_commission' => false,
                'default_commission_rate' => null,
                'is_commission_editable' => false,
            ],
            [
                'role_name' => 'user',
                'has_commission' => false,
                'default_commission_rate' => null,
                'is_commission_editable' => false,
            ],
        ];

        foreach ($settings as $setting) {
            RoleCommissionSetting::create($setting);
        }
    }
}