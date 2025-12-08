<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('role_commission_settings', function (Blueprint $table) {
            $table->id();
            $table->string('role_name');
            $table->boolean('has_commission')->default(false);
            $table->decimal('default_commission_rate', 5, 2)->nullable()->default(0);
            $table->boolean('is_commission_editable')->default(true);
            $table->timestamps();
            
            $table->unique('role_name');
        });
    }

    public function down()
    {
        Schema::dropIfExists('role_commission_settings');
    }
};