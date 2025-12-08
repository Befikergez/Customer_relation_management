<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('opportunities', function (Blueprint $table) {
            // Add city_id and subcity_id as nullable foreign keys
            $table->foreignId('city_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('subcity_id')->nullable()->constrained()->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('opportunities', function (Blueprint $table) {
            // Drop the foreign keys first
            $table->dropForeign(['city_id']);
            $table->dropForeign(['subcity_id']);
            
            // Then drop the columns
            $table->dropColumn(['city_id', 'subcity_id']);
        });
    }
};