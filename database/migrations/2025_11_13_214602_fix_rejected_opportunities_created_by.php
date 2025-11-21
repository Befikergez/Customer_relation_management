<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Get the first valid user ID
        $firstUserId = DB::table('users')->value('id');
        
        if (!$firstUserId) {
            // Create a default user if none exists
            $firstUserId = DB::table('users')->insertGetId([
                'name' => 'System Admin',
                'email' => 'admin@system.com',
                'password' => bcrypt('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Update all records with invalid created_by values (0 or null)
        DB::table('rejected_opportunities')
            ->where('created_by', 0)
            ->orWhereNull('created_by')
            ->update(['created_by' => $firstUserId]);

        // Now add the foreign key constraint
        try {
            DB::statement('
                ALTER TABLE rejected_opportunities 
                ADD CONSTRAINT rejected_opportunities_created_by_foreign 
                FOREIGN KEY (created_by) REFERENCES users(id)
            ');
            echo "Foreign key constraint added successfully\n";
        } catch (\Exception $e) {
            echo "Foreign key might already exist: " . $e->getMessage() . "\n";
        }
    }

    public function down()
    {
        // Drop the foreign key constraint
        try {
            DB::statement('ALTER TABLE rejected_opportunities DROP FOREIGN KEY rejected_opportunities_created_by_foreign');
        } catch (\Exception $e) {
            // Ignore if doesn't exist
        }
    }
};