<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Remove the problematic migrations from the migrations table
        DB::table('migrations')
            ->whereIn('migration', [
                '2025_11_14_112255_update_proposals_table_for_new_requirements',
                '2025_11_18_184553_finalize_proposals_table_structure'
            ])
            ->delete();
            
        echo "Cleaned up migrations table\n";
    }

    public function down()
    {
        // Can't really rollback this safely
    }
};