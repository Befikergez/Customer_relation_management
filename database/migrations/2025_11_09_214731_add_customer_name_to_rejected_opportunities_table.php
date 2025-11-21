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
    Schema::table('rejected_opportunities', function (Blueprint $table) {
        $table->string('customer_name')->after('opportunity_id');
    });
}

public function down()
{
    Schema::table('rejected_opportunities', function (Blueprint $table) {
        $table->dropColumn('customer_name');
    });
}
};