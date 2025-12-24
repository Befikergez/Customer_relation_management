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
        // Remove from opportunities table
        Schema::table('opportunities', function (Blueprint $table) {
            if (Schema::hasColumn('opportunities', 'location')) {
                $table->dropColumn('location');
            }
            if (Schema::hasColumn('opportunities', 'specific_location')) {
                $table->dropColumn('specific_location');
            }
        });

        // Remove from rejected_opportunities table
        Schema::table('rejected_opportunities', function (Blueprint $table) {
            if (Schema::hasColumn('rejected_opportunities', 'location')) {
                $table->dropColumn('location');
            }
            if (Schema::hasColumn('rejected_opportunities', 'specific_location')) {
                $table->dropColumn('specific_location');
            }
        });

        // Remove from potential_customers table
        Schema::table('potential_customers', function (Blueprint $table) {
            if (Schema::hasColumn('potential_customers', 'location')) {
                $table->dropColumn('location');
            }
            if (Schema::hasColumn('potential_customers', 'specific_location')) {
                $table->dropColumn('specific_location');
            }
        });

        // Remove from customers table
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'location')) {
                $table->dropColumn('location');
            }
            if (Schema::hasColumn('customers', 'specific_location')) {
                $table->dropColumn('specific_location');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add to customers table
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'location')) {
                $table->string('location')->nullable()->after('status');
            }
            if (!Schema::hasColumn('customers', 'specific_location')) {
                $table->string('specific_location')->nullable()->after('location');
            }
        });

        // Re-add to potential_customers table
        Schema::table('potential_customers', function (Blueprint $table) {
            if (!Schema::hasColumn('potential_customers', 'location')) {
                $table->string('location')->nullable()->after('status');
            }
            if (!Schema::hasColumn('potential_customers', 'specific_location')) {
                $table->string('specific_location')->nullable()->after('location');
            }
        });

        // Re-add to rejected_opportunities table
        Schema::table('rejected_opportunities', function (Blueprint $table) {
            if (!Schema::hasColumn('rejected_opportunities', 'location')) {
                $table->string('location')->nullable()->after('status');
            }
            if (!Schema::hasColumn('rejected_opportunities', 'specific_location')) {
                $table->string('specific_location')->nullable()->after('location');
            }
        });

        // Re-add to opportunities table
        Schema::table('opportunities', function (Blueprint $table) {
            if (!Schema::hasColumn('opportunities', 'location')) {
                $table->string('location')->nullable()->after('status');
            }
            if (!Schema::hasColumn('opportunities', 'specific_location')) {
                $table->string('specific_location')->nullable()->after('location');
            }
        });
    }
};