<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('proposals', function (Blueprint $table) {
            // Add missing columns
            if (!Schema::hasColumn('proposals', 'customer_review')) {
                $table->text('customer_review')->nullable();
            }
            
            if (!Schema::hasColumn('proposals', 'customer_approved_at')) {
                $table->timestamp('customer_approved_at')->nullable();
            }
            
            if (!Schema::hasColumn('proposals', 'superadmin_approved_at')) {
                $table->timestamp('superadmin_approved_at')->nullable();
            }
            
            // Rename description to details if it exists
            if (Schema::hasColumn('proposals', 'description') && !Schema::hasColumn('proposals', 'details')) {
                $table->renameColumn('description', 'details');
            }
        });
    }

    public function down()
    {
        Schema::table('proposals', function (Blueprint $table) {
            // Reverse the changes
            if (Schema::hasColumn('proposals', 'customer_review')) {
                $table->dropColumn('customer_review');
            }
            
            if (Schema::hasColumn('proposals', 'customer_approved_at')) {
                $table->dropColumn('customer_approved_at');
            }
            
            if (Schema::hasColumn('proposals', 'superadmin_approved_at')) {
                $table->dropColumn('superadmin_approved_at');
            }
            
            // Rename back if needed
            if (Schema::hasColumn('proposals', 'details') && !Schema::hasColumn('proposals', 'description')) {
                $table->renameColumn('details', 'description');
            }
        });
    }
};