<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Remove the enum category column
            $table->dropColumn('category');
            
            // Add foreign key to product_categories
            $table->foreignId('category_id')->nullable()->constrained('product_categories')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
            
            // Add back the enum column
            $table->enum('category', [
                'Software', 
                'Hardware', 
                'Services', 
                'Subscriptions', 
                'Training & Workshops', 
                'Custom Solutions', 
                'Support & Maintenance'
            ])->nullable();
        });
    }
};