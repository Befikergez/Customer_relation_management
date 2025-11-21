<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('potential_customers', function (Blueprint $table) {
            $table->id();
            $table->string('potential_customer_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('location')->nullable(); // map link
            $table->text('remarks')->nullable();
            $table->string('status')->default('draft'); // draft, proposal_sent, rejected
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('potential_customers');
    }
};