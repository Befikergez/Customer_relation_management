<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    public function up()
    {
        // Get current table structure
        $columns = Schema::getColumnListing('rejected_opportunities');
        echo "Current columns in rejected_opportunities: " . implode(', ', $columns) . "\n";
        Log::info('Current rejected_opportunities columns:', $columns);
        
        // Let's see what data we have
        $sampleData = DB::table('rejected_opportunities')->first();
        echo "Sample data: " . json_encode($sampleData) . "\n";
    }

    public function down()
    {
        // Nothing to rollback for diagnostic
    }
};