<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('proposals', function (Blueprint $table) {
            // First, safely drop unnecessary columns if they exist
            $columnsToDrop = ['sales_person_id', 'opportunity_id', 'sales_manager_id', 'details', 'manager_review', 'superadmin_approved_at'];
            
            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('proposals', $column)) {
                    // Drop foreign keys first if they exist
                    try {
                        $table->dropForeign(['proposals_' . $column . '_foreign']);
                    } catch (Exception $e) {
                        // Ignore if foreign key doesn't exist
                    }
                    $table->dropColumn($column);
                    echo "Dropped column: $column\n";
                }
            }
            
            // Add missing required columns
            if (!Schema::hasColumn('proposals', 'potential_customer_id')) {
                $table->foreignId('potential_customer_id')->nullable()->constrained('potential_customers')->onDelete('cascade');
                echo "Added potential_customer_id\n";
            }
            
            if (!Schema::hasColumn('proposals', 'created_by')) {
                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('cascade');
                echo "Added created_by\n";
            }
            
            if (!Schema::hasColumn('proposals', 'price')) {
                $table->decimal('price', 10, 2)->nullable();
                echo "Added price\n";
            }
            
            if (!Schema::hasColumn('proposals', 'customer_rejected_at')) {
                $table->timestamp('customer_rejected_at')->nullable();
                echo "Added customer_rejected_at\n";
            }
            
            // Add status column if it doesn't exist, or modify existing one
            if (!Schema::hasColumn('proposals', 'status')) {
                $table->enum('status', ['unsigned', 'signed'])->default('unsigned');
                echo "Added status column\n";
            }
        });
        
        // Update status enum values if column exists but has wrong values
        if (Schema::hasColumn('proposals', 'status')) {
            try {
                DB::statement("ALTER TABLE proposals MODIFY COLUMN status ENUM('unsigned', 'signed') DEFAULT 'unsigned'");
                echo "Updated status enum values\n";
            } catch (Exception $e) {
                echo "Status column might already be correct\n";
            }
        }
        
        // Add indexes for better performance
        Schema::table('proposals', function (Blueprint $table) {
            $table->index(['potential_customer_id', 'status']);
            $table->index(['created_by', 'status']);
            $table->index('customer_approved_at');
            $table->index('customer_rejected_at');
        });
    }

    public function down()
    {
        Schema::table('proposals', function (Blueprint $table) {
            // Remove indexes
            $table->dropIndex(['potential_customer_id', 'status']);
            $table->dropIndex(['created_by', 'status']);
            $table->dropIndex(['customer_approved_at']);
            $table->dropIndex(['customer_rejected_at']);
            
            // Remove the columns we added
            $columnsToRemove = [
                'potential_customer_id',
                'created_by',
                'price',
                'customer_rejected_at',
                'status'
            ];
            
            foreach ($columnsToRemove as $column) {
                if (Schema::hasColumn('proposals', $column)) {
                    $table->dropColumn($column);
                }
            }
            
            // Re-add old columns (nullable for rollback safety)
            $oldColumns = [
                'sales_person_id' => 'bigint',
                'opportunity_id' => 'bigint', 
                'sales_manager_id' => 'bigint',
                'details' => 'text',
                'manager_review' => 'text',
                'superadmin_approved_at' => 'timestamp'
            ];
            
            foreach ($oldColumns as $column => $type) {
                if (!Schema::hasColumn('proposals', $column)) {
                    if ($type === 'bigint') {
                        $table->foreignId($column)->nullable()->constrained('users');
                    } elseif ($type === 'timestamp') {
                        $table->timestamp($column)->nullable();
                    } else {
                        $table->text($column)->nullable();
                    }
                }
            }
        });
    }
};