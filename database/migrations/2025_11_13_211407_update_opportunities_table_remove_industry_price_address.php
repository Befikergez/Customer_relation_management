<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('opportunities', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['industry_id']);
            $table->dropForeign(['customer_id']);
            
            // Remove columns we don't need
            $table->dropColumn(['industry_id', 'price', 'address', 'customer_id']);
            
            // Rename customer_name to potential_customer_name
            $table->renameColumn('customer_name', 'potential_customer_name');
            
            // Add new columns
            $table->text('location')->nullable()->after('phone');
            $table->foreignId('created_by')->constrained('users')->after('location');
            $table->foreignId('potential_customer_id')->nullable()->constrained('potential_customers')->after('created_by');
        });
    }

    public function down()
    {
        Schema::table('opportunities', function (Blueprint $table) {
            // Reverse the changes
            $table->dropForeign(['created_by']);
            $table->dropForeign(['potential_customer_id']);
            
            $table->dropColumn(['location', 'created_by', 'potential_customer_id']);
            
            // Add back the old columns
            $table->renameColumn('potential_customer_name', 'customer_name');
            $table->foreignId('industry_id')->nullable()->constrained();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('address')->nullable();
            $table->foreignId('customer_id')->nullable()->constrained();
        });
    }
};