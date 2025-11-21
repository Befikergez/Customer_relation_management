<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('rejected_opportunities', function (Blueprint $table) {
            $table->unsignedBigInteger('original_id')->nullable()->after('rejected_from');
        });
    }

    public function down()
    {
        Schema::table('rejected_opportunities', function (Blueprint $table) {
            $table->dropColumn('original_id');
        });
    }
};