<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('contracts', function (Blueprint $table) {
            // Drop unnecessary columns
            $table->dropColumn(['sales_person_id', 'sales_manager_id', 'manager_review', 'superadmin_approved_at', 'superadmin_rejected_at']);
            
            // Add status column
            $table->string('status')->default('unsigned')->after('file');
        });
    }

    public function down()
    {
        Schema::table('contracts', function (Blueprint $table) {
            // Add back the dropped columns
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