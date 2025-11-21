<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Drop the existing proposals table if it exists
        Schema::dropIfExists('proposals');
        
        // Create the proper proposals table
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('file')->nullable();
            
            // Relationships
            $table->foreignId('potential_customer_id')->constrained('potential_customers')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            
            // Status
            $table->enum('status', ['unsigned', 'signed'])->default('unsigned');
            
            // Customer approval tracking
            $table->timestamp('customer_approved_at')->nullable();
            $table->timestamp('customer_rejected_at')->nullable();
            $table->text('customer_review')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['potential_customer_id', 'status']);
            $table->index(['created_by', 'status']);
            $table->index('customer_approved_at');
            $table->index('customer_rejected_at');
        });
        
        echo "Proposals table created successfully!\n";
    }

    public function down()
    {
        Schema::dropIfExists('proposals');
    }
};