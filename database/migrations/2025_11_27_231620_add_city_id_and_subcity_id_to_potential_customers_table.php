<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('potential_customers', function (Blueprint $table) {
            $table->foreignId('city_id')->nullable()->after('remarks')->constrained()->nullOnDelete();
            $table->foreignId('subcity_id')->nullable()->after('city_id')->constrained()->nullOnDelete();
            
            // Add indexes for better performance
            $table->index('city_id');
            $table->index('subcity_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('potential_customers', function (Blueprint $table) {
            $table->dropForeign(['city_id']);
            $table->dropForeign(['subcity_id']);
            $table->dropColumn(['city_id', 'subcity_id']);
        });
    }
};