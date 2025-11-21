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
        $table->dropForeign(['opportunity_id']);
    });
}

public function down(): void
{
    Schema::table('rejected_opportunities', function (Blueprint $table) {
        $table->foreign('opportunity_id')->references('id')->on('opportunities')->onDelete('cascade');
    });
}
};