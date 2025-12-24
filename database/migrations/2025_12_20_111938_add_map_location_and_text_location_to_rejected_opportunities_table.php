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
        Schema::table('rejected_opportunities', function (Blueprint $table) {
            // Add map_location column
            if (!Schema::hasColumn('rejected_opportunities', 'map_location')) {
                $table->text('map_location')->nullable()->after('address');
            }
            
            // Add text_location column
            if (!Schema::hasColumn('rejected_opportunities', 'text_location')) {
                $table->text('text_location')->nullable()->after('map_location');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rejected_opportunities', function (Blueprint $table) {
            // Remove the columns in reverse migration
            if (Schema::hasColumn('rejected_opportunities', 'map_location')) {
                $table->dropColumn('map_location');
            }
            
            if (Schema::hasColumn('rejected_opportunities', 'text_location')) {
                $table->dropColumn('text_location');
            }
        });
    }
};