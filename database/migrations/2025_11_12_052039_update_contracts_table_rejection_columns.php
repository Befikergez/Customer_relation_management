<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('contracts', function (Blueprint $table) {
            // Drop the old rejected_at column
            $table->dropColumn('rejected_at');
            
            // Add new rejection columns
            $table->timestamp('customer_rejected_at')->nullable();
            $table->timestamp('superadmin_rejected_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('contracts', function (Blueprint $table) {
            // Add back the old column
            $table->timestamp('rejected_at')->nullable();
            
            // Drop the new columns
            $table->dropColumn(['customer_rejected_at', 'superadmin_rejected_at']);
        });
    }
};