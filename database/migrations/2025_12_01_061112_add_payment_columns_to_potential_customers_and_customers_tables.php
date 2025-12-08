<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Add payment columns to potential_customers table
        Schema::table('potential_customers', function (Blueprint $table) {
            $table->decimal('payment_amount', 10, 2)->nullable()->after('remarks');
            $table->string('payment_method', 100)->nullable()->after('payment_amount');
            $table->string('payment_schedule', 100)->nullable()->after('payment_method');
            $table->date('payment_date')->nullable()->after('payment_schedule');
            $table->text('payment_remarks')->nullable()->after('payment_date');
        });

        // Add payment columns to customers table
        Schema::table('customers', function (Blueprint $table) {
            $table->decimal('payment_amount', 10, 2)->nullable()->after('remarks');
            $table->string('payment_method', 100)->nullable()->after('payment_amount');
            $table->string('payment_schedule', 100)->nullable()->after('payment_method');
            $table->date('payment_date')->nullable()->after('payment_schedule');
            $table->text('payment_remarks')->nullable()->after('payment_date');
        });
    }

    public function down()
    {
        Schema::table('potential_customers', function (Blueprint $table) {
            $table->dropColumn([
                'payment_amount',
                'payment_method',
                'payment_schedule',
                'payment_date',
                'payment_remarks'
            ]);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'payment_amount',
                'payment_method',
                'payment_schedule',
                'payment_date',
                'payment_remarks'
            ]);
        });
    }
};