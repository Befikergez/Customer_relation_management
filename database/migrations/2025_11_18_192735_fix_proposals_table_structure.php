<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // First, let's safely modify the existing table without breaking foreign keys
        Schema::table('proposals', function (Blueprint $table) {
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('proposals', 'potential_customer_id')) {
                $table->foreignId('potential_customer_id')->nullable()->constrained('potential_customers')->onDelete('cascade');
            }
            
            if (!Schema::hasColumn('proposals', 'created_by')) {
                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('cascade');
            }
            
            if (!Schema::hasColumn('proposals', 'price')) {
                $table->decimal('price', 10, 2)->nullable();
            }
            
            if (!Schema::hasColumn('proposals', 'customer_approved_at')) {
                $table->timestamp('customer_approved_at')->nullable();
            }
            
            if (!Schema::hasColumn('proposals', 'customer_rejected_at')) {
                $table->timestamp('customer_rejected_at')->nullable();
            }
            
            if (!Schema::hasColumn('proposals', 'customer_review')) {
                $table->text('customer_review')->nullable();
            }
            
            // Update status column to correct enum values if it exists
            if (Schema::hasColumn('proposals', 'status')) {
                // We'll handle this separately with raw SQL to avoid issues
            }
        });
        
        // Update status enum using raw SQL (safer)
        DB::statement("ALTER TABLE proposals MODIFY COLUMN status ENUM('unsigned', 'signed') DEFAULT 'unsigned'");
        
        // Add indexes for performance
        Schema::table('proposals', function (Blueprint $table) {
            $table->index(['potential_customer_id', 'status']);
            $table->index(['created_by', 'status']);
        });
    }

    public function down()
    {
        Schema::table('proposals', function (Blueprint $table) {
            // Remove indexes
            $table->dropIndex(['potential_customer_id', 'status']);
            $table->dropIndex(['created_by', 'status']);
            
            // Remove the columns we added (keep existing ones)
            $columnsToRemove = [
                'potential_customer_id',
                'created_by',
                'price',
                'customer_approved_at',
                'customer_rejected_at',
                'customer_review'
            ];
            
            foreach ($columnsToRemove as $column) {
                if (Schema::hasColumn('proposals', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
        
        // Revert status if needed
        DB::statement("ALTER TABLE proposals MODIFY COLUMN status ENUM('pending_review', 'sent_to_customer', 'accepted', 'rejected') DEFAULT 'pending_review'");
    }
};