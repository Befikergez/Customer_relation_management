<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('contracts', function (Blueprint $table) {
            // Drop foreign keys first, then columns
            if (Schema::hasColumn('contracts', 'sales_person_id')) {
                $table->dropForeign(['sales_person_id']);
                $table->dropColumn('sales_person_id');
            }
            
            if (Schema::hasColumn('contracts', 'sales_manager_id')) {
                $table->dropForeign(['sales_manager_id']);
                $table->dropColumn('sales_manager_id');
            }
            
            // Drop other columns without foreign keys
            $columnsToDrop = ['manager_review', 'superadmin_approved_at', 'superadmin_rejected_at'];
            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('contracts', $column)) {
                    $table->dropColumn($column);
                }
            }
            
            // Add status column
            if (!Schema::hasColumn('contracts', 'status')) {
                $table->string('status')->default('unsigned')->after('file');
            }
        });
    }

    public function down()
    {
        Schema::table('contracts', function (Blueprint $table) {
            // Add back the columns in reverse order
            $table->foreignId('sales_person_id')->nullable()->constrained('users');
            $table->foreignId('sales_manager_id')->nullable()->constrained('users');
            $table->text('manager_review')->nullable();
            $table->timestamp('superadmin_approved_at')->nullable();
            $table->timestamp('superadmin_rejected_at')->nullable();
            
            // Remove status column
            $table->dropColumn('status');
        });
    }
};