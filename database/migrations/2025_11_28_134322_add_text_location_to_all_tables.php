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
        // For opportunities table
        Schema::table('opportunities', function (Blueprint $table) {
            $table->text('text_location')->nullable()->after('specific_location');
            // Rename the existing column to be more clear
            $table->renameColumn('specific_location', 'map_location');
        });

        // For customers table
        Schema::table('customers', function (Blueprint $table) {
            $table->text('text_location')->nullable()->after('specific_location');
            $table->renameColumn('specific_location', 'map_location');
        });

        // For potential_customers table
        Schema::table('potential_customers', function (Blueprint $table) {
            $table->text('text_location')->nullable()->after('specific_location');
            $table->renameColumn('specific_location', 'map_location');
        });

        // For rejected_opportunities table
        Schema::table('rejected_opportunities', function (Blueprint $table) {
            $table->text('text_location')->nullable()->after('specific_location');
            $table->renameColumn('specific_location', 'map_location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // For opportunities table
        Schema::table('opportunities', function (Blueprint $table) {
            $table->dropColumn('text_location');
            $table->renameColumn('map_location', 'specific_location');
        });

        // For customers table
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('text_location');
            $table->renameColumn('map_location', 'specific_location');
        });

        // For potential_customers table
        Schema::table('potential_customers', function (Blueprint $table) {
            $table->dropColumn('text_location');
            $table->renameColumn('map_location', 'specific_location');
        });

        // For rejected_opportunities table
        Schema::table('rejected_opportunities', function (Blueprint $table) {
            $table->dropColumn('text_location');
            $table->renameColumn('map_location', 'specific_location');
        });
    }
};