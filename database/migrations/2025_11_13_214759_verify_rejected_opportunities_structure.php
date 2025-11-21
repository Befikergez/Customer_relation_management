<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        $columns = Schema::getColumnListing('rejected_opportunities');
        echo "Final columns in rejected_opportunities: " . implode(', ', $columns) . "\n";
        
        // Check if remarks column exists and has data
        if (in_array('remarks', $columns)) {
            $hasRemarks = DB::table('rejected_opportunities')->whereNotNull('remarks')->exists();
            echo "Remarks column exists. Has data: " . ($hasRemarks ? 'YES' : 'NO') . "\n";
        }
        
        // Check foreign key
        $foreignKeyExists = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE TABLE_NAME = 'rejected_opportunities' 
            AND COLUMN_NAME = 'created_by'
            AND REFERENCED_TABLE_NAME = 'users'
        ");
        
        echo "Foreign key exists: " . (!empty($foreignKeyExists) ? 'YES' : 'NO') . "\n";
        
        // Check data integrity
        $invalidRecords = DB::table('rejected_opportunities as ro')
            ->leftJoin('users as u', 'ro.created_by', '=', 'u.id')
            ->whereNull('u.id')
            ->count();
            
        echo "Invalid created_by records: " . $invalidRecords . "\n";
    }

    public function down()
    {
        // Nothing to rollback for verification
    }
};