<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['industry_id']);
            
            // Remove columns we don't need
            $table->dropColumn(['industry_id', 'price', 'address']);
            
            // Add location column for map links
            $table->text('location')->nullable()->after('phone');
        });
    }

    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            // Reverse the changes
            $table->dropColumn('location');
            
            // Add back the old columns
            $table->foreignId('industry_id')->nullable()->constrained();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('address')->nullable();
        });
    }
};