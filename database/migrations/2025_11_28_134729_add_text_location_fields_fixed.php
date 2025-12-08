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
        // For opportunities table - just add text_location, keep specific_location as is
        Schema::table('opportunities', function (Blueprint $table) {
            $table->text('text_location')->nullable()->after('specific_location');
        });

        // For customers table
        Schema::table('customers', function (Blueprint $table) {
            $table->text('text_location')->nullable()->after('specific_location');
        });

        // For potential_customers table
        Schema::table('potential_customers', function (Blueprint $table) {
            $table->text('text_location')->nullable()->after('specific_location');
        });

        // For rejected_opportunities table
        Schema::table('rejected_opportunities', function (Blueprint $table) {
            $table->text('text_location')->nullable()->after('specific_location');
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
        });

        // For customers table
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('text_location');
        });

        // For potential_customers table
        Schema::table('potential_customers', function (Blueprint $table) {
            $table->dropColumn('text_location');
        });

        // For rejected_opportunities table
        Schema::table('rejected_opportunities', function (Blueprint $table) {
            $table->dropColumn('text_location');
        });
    }
};