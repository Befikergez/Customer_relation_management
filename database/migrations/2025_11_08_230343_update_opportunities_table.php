<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('opportunities', function (Blueprint $table) {
            // Drop foreign key first before dropping the column
            if (Schema::hasColumn('opportunities', 'salesperson_id')) {
                $table->dropForeign(['salesperson_id']); // remove FK constraint
                $table->dropColumn('salesperson_id');    // remove column
            }

            // Drop 'status' column if exists
            if (Schema::hasColumn('opportunities', 'status')) {
                $table->dropColumn('status');
            }

            // Skip adding columns because they already exist
        });
    }

    public function down(): void
    {
        Schema::table('opportunities', function (Blueprint $table) {
            // Re-add dropped columns
            if (!Schema::hasColumn('opportunities', 'salesperson_id')) {
                $table->foreignId('salesperson_id')
                    ->constrained('users')
                    ->after('industry_id')
                    ->onDelete('cascade');
            }

            if (!Schema::hasColumn('opportunities', 'status')) {
                $table->enum('status', [
                    'pending_approach',
                    'proposal_sent',
                    'waiting_response',
                    'rejected',
                    'successful'
                ])->default('pending_approach')->after('salesperson_id');
            }
        });
    }
};
