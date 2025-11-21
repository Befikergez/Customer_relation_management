<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            // Drop existing columns that need to be changed
            $table->dropForeign(['opportunity_id']);
            $table->dropForeign(['customer_id']);
            $table->dropColumn(['opportunity_id', 'contract_status']);
            
            // Add new columns
            $table->foreignId('proposal_id')->after('id')->constrained('proposals')->onDelete('cascade');
            $table->foreignId('sales_person_id')->after('customer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('sales_manager_id')->after('sales_person_id')->constrained('users')->onDelete('cascade');
            $table->text('contract_description')->after('contract_title')->nullable();
            $table->text('manager_review')->after('payment_terms')->nullable();
            $table->string('file')->after('manager_review')->nullable();
            $table->timestamp('customer_signed_at')->after('file')->nullable();
            $table->timestamp('superadmin_approved_at')->after('customer_signed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            // Reverse the changes
            $table->dropForeign(['proposal_id']);
            $table->dropForeign(['sales_person_id']);
            $table->dropForeign(['sales_manager_id']);
            $table->dropColumn([
                'proposal_id',
                'sales_person_id', 
                'sales_manager_id',
                'contract_description',
                'manager_review',
                'file',
                'customer_signed_at',
                'superadmin_approved_at'
            ]);
            
            // Restore original columns
            $table->foreignId('opportunity_id')->constrained('opportunities')->onDelete('cascade');
            $table->enum('contract_status', [
                'draft', 
                'active', 
                'completed', 
                'terminated'
            ])->default('draft');
        });
    }
};