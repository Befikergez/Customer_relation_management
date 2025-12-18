<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Add the missing columns
            $table->decimal('payment_progress', 5, 2)->default(0)->after('payment_status');
            $table->decimal('commission_progress', 5, 2)->default(0)->after('commission_status');
            
            // Also check for other potentially missing columns
            $table->decimal('remaining_amount', 10, 2)->default(0)->change();
            $table->decimal('commission_amount', 10, 2)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['payment_progress', 'commission_progress']);
        });
    }
};