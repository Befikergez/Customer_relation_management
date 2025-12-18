<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            // Rename salesperson_id to commission_user_id
            $table->renameColumn('salesperson_id', 'commission_user_id');
            
            // Add foreign key constraint if not exists
            $table->foreign('commission_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['commission_user_id']);
            $table->renameColumn('commission_user_id', 'salesperson_id');
            $table->foreign('salesperson_id')->references('id')->on('users')->onDelete('set null');
        });
    }
};