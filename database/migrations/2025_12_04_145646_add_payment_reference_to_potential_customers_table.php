<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('potential_customers', function (Blueprint $table) {
        $table->string('payment_reference', 100)->nullable()->after('payment_remarks');
    });
}

    

public function down()
{
    Schema::table('potential_customers', function (Blueprint $table) {
        $table->dropColumn('payment_reference');
    });
}
};
