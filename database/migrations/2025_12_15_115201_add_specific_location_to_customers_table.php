<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('customers', function (Blueprint $table) {
        $table->string('specific_location')->nullable()->after('location');
    });
}

public function down()
{
    Schema::table('customers', function (Blueprint $table) {
        $table->dropColumn('specific_location');
    });
}
};
