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
        Schema::table('payments', function (Blueprint $table) {
            // Commission tracking for individual payments
            $table->decimal('commission_earned', 12, 2)->default(0);
            $table->decimal('commission_paid', 12, 2)->default(0);
            $table->date('commission_payment_date')->nullable();
            $table->boolean('commission_paid_status')->default(false);
            
            // Make sure customer_id is nullable (for potential_customer payments)
            if (!Schema::hasColumn('payments', 'customer_id')) {
                $table->unsignedBigInteger('customer_id')->nullable();
                $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('commission_earned');
            $table->dropColumn('commission_paid');
            $table->dropColumn('commission_payment_date');
            $table->dropColumn('commission_paid_status');
            
            // Only drop foreign key if it exists
            if (Schema::hasColumn('payments', 'customer_id')) {
                $table->dropForeign(['customer_id']);
                $table->dropColumn('customer_id');
            }
        });
    }
};