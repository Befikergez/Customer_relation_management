<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('proposals', function (Blueprint $table) {
            // Remove columns
            $table->dropColumn(['customer_id', 'customer_review', 'sales_person_id', 'customer_approved_at', 'opportunity_id']);
            
            // Add new columns
            $table->decimal('price', 10, 2)->nullable()->after('description');
            $table->string('potential_customer')->after('price');
            $table->enum('status', ['unsigned', 'signed'])->default('unsigned')->after('potential_customer');
            $table->foreignId('created_by')->constrained('users')->after('status');
        });
    }

    public function down()
    {
        Schema::table('proposals', function (Blueprint $table) {
            // Add back removed columns
            $table->foreignId('customer_id')->nullable()->constrained('users');
            $table->text('customer_review')->nullable();
            $table->foreignId('sales_person_id')->nullable()->constrained('users');
            $table->timestamp('customer_approved_at')->nullable();
            $table->foreignId('opportunity_id')->nullable()->constrained('opportunities');
            
            // Remove new columns
            $table->dropColumn(['price', 'potential_customer', 'status', 'created_by']);
        });
    }
};