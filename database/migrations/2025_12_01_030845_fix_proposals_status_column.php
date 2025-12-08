<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if we need to modify the status column
        Schema::table('proposals', function (Blueprint $table) {
            // First, let's check the current column type
            if (Schema::hasColumn('proposals', 'status')) {
                // Modify the status column to be longer or use enum
                $table->string('status', 20)->default('unsigned')->change();
            }
        });

        // Alternatively, if you want to use ENUM (more restrictive but ensures data integrity)
        // DB::statement("ALTER TABLE proposals MODIFY COLUMN status ENUM('unsigned', 'signed', 'rejected') DEFAULT 'unsigned'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back if needed
        Schema::table('proposals', function (Blueprint $table) {
            $table->string('status', 10)->default('unsigned')->change();
        });
    }
};