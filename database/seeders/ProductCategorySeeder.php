<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            [
                'name' => 'Hardware',
                'description' => 'Physical technology equipment and devices',
                'is_active' => true,
                'created_by' => 1,
            ],
            [
                'name' => 'Software',
                'description' => 'Applications, platforms, and digital solutions',
                'is_active' => true,
                'created_by' => 1,
            ],
            [
                'name' => 'IT Services',
                'description' => 'Professional technology services and consulting',
                'is_active' => true,
                'created_by' => 1,
            ],
            [
                'name' => 'Support & Maintenance',
                'description' => 'Ongoing technical support and maintenance services',
                'is_active' => true,
                'created_by' => 1,
            ],
            [
                'name' => 'Training',
                'description' => 'Technology training and educational services',
                'is_active' => true,
                'created_by' => 1,
            ],
            [
    'name' => 'Mobile Applications',
    'description' => 'Mobile app development and solutions',
    'is_active' => true,
    'created_by' => 1,
],
[
    'name' => 'Web Development',
    'description' => 'Website and web application development',
    'is_active' => true,
    'created_by' => 1,
],
[
    'name' => 'Database Solutions',
    'description' => 'Database management and optimization',
    'is_active' => true,
    'created_by' => 1,
]
        ];

        foreach ($categories as $category) {
            ProductCategory::create($category);
        }
    }
}