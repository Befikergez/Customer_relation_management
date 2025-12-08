<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Payment tracking columns
            $table->string('payment_status')->default('not_paid')->comment('not_paid, pending, half_paid, paid');
            $table->decimal('total_payment_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            
            // Commission tracking columns
            $table->decimal('commission_rate', 5, 2)->nullable();
            $table->decimal('commission_amount', 12, 2)->default(0);
            $table->string('commission_status')->default('not_paid')->comment('not_paid, pending, half_paid, paid');
            $table->decimal('paid_commission', 12, 2)->default(0);
            
            // Salesperson reference
            $table->unsignedBigInteger('salesperson_id')->nullable();
            $table->foreign('salesperson_id')->references('id')->on('users')->onDelete('set null');
            
            // Approval tracking
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            
            // Rejection tracking
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->unsignedBigInteger('rejected_by')->nullable();
            $table->foreign('rejected_by')->references('id')->on('users')->onDelete('set null');
            
            // Existing payment fields (if they don't exist already from potential_customer import)
            if (!Schema::hasColumn('customers', 'payment_amount')) {
                $table->decimal('payment_amount', 12, 2)->nullable();
                $table->string('payment_method')->nullable();
                $table->string('payment_schedule')->nullable();
                $table->date('payment_date')->nullable();
                $table->text('payment_remarks')->nullable();
                $table->string('payment_reference')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['salesperson_id']);
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['rejected_by']);
            
            // Drop payment status columns
            $table->dropColumn('payment_status');
            $table->dropColumn('total_payment_amount');
            $table->dropColumn('paid_amount');
            
            // Drop commission columns
            $table->dropColumn('commission_rate');
            $table->dropColumn('commission_amount');
            $table->dropColumn('commission_status');
            $table->dropColumn('paid_commission');
            
            // Drop salesperson column
            $table->dropColumn('salesperson_id');
            
            // Drop approval columns
            $table->dropColumn('approved_at');
            $table->dropColumn('approved_by');
            $table->dropColumn('rejected_at');
            $table->dropColumn('rejection_reason');
            $table->dropColumn('rejected_by');
            
            // Drop existing payment fields (optional - only if you added them)
            if (Schema::hasColumn('customers', 'payment_amount')) {
                $table->dropColumn('payment_amount');
                $table->dropColumn('payment_method');
                $table->dropColumn('payment_schedule');
                $table->dropColumn('payment_date');
                $table->dropColumn('payment_remarks');
                $table->dropColumn('payment_reference');
            }
        });
    }
};