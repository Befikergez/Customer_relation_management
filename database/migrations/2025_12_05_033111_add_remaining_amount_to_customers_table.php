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
            // Add the remaining_amount column as a decimal with 2 decimal places
            $table->decimal('remaining_amount', 15, 2)->nullable()->after('paid_amount');
            
            // Optional: Add comment for clarity
            $table->comment('Calculated field: total_payment_amount - paid_amount');
        });
        
        // Update existing records to calculate remaining_amount
        DB::statement("
            UPDATE customers 
            SET remaining_amount = 
                CASE 
                    WHEN total_payment_amount IS NULL THEN 0
                    WHEN paid_amount IS NULL THEN total_payment_amount
                    ELSE GREATEST(0, total_payment_amount - COALESCE(paid_amount, 0))
                END
            WHERE remaining_amount IS NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('remaining_amount');
        });
    }
};