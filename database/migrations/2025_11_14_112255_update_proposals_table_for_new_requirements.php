<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('proposals', function (Blueprint $table) {
            // First drop foreign key constraints
            $table->dropForeign(['sales_person_id']);
            $table->dropForeign(['opportunity_id']);
            $table->dropForeign(['customer_id']);
            
            // Then drop the columns
            $table->dropColumn(['details', 'sales_person_id', 'opportunity_id', 'customer_id']);
            
            // Add new columns
            $table->foreignId('potential_customer_id')->nullable()->constrained('potential_customers')->onDelete('cascade');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('cascade');
            $table->decimal('price', 10, 2)->nullable();
            $table->timestamp('customer_rejected_at')->nullable();
            
            // Add indexes for better performance
            $table->index('potential_customer_id');
            $table->index('sales_manager_id');
            $table->index('created_by');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::table('proposals', function (Blueprint $table) {
            // Remove new columns first
            $table->dropForeign(['potential_customer_id']);
            $table->dropForeign(['created_by']);
            $table->dropColumn(['potential_customer_id', 'created_by', 'price', 'customer_rejected_at']);
            
            // Remove indexes
            $table->dropIndex(['potential_customer_id']);
            $table->dropIndex(['sales_manager_id']);
            $table->dropIndex(['created_by']);
            $table->dropIndex(['status']);
            
            // Re-add old columns
            $table->text('details')->nullable();
            $table->foreignId('sales_person_id')->nullable()->constrained('users');
            $table->foreignId('opportunity_id')->nullable()->constrained('opportunities');
            $table->foreignId('customer_id')->nullable()->constrained('customers');
        });
    }
};