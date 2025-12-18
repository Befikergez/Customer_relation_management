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
        Schema::table('potential_customers', function (Blueprint $table) {
            // Add total_payment_amount column after payment_amount
            $table->decimal('total_payment_amount', 12, 2)
                  ->nullable()
                  ->after('payment_amount')
                  ->comment('Sum of all payments for this potential customer');
            
            // Also add paid_amount and remaining_amount if needed
            $table->decimal('paid_amount', 12, 2)
                  ->nullable()
                  ->after('total_payment_amount')
                  ->default(0)
                  ->comment('Amount already paid');
                  
            $table->decimal('remaining_amount', 12, 2)
                  ->nullable()
                  ->after('paid_amount')
                  ->default(0)
                  ->comment('Remaining amount to be paid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('potential_customers', function (Blueprint $table) {
            $table->dropColumn(['total_payment_amount', 'paid_amount', 'remaining_amount']);
        });
    }
};