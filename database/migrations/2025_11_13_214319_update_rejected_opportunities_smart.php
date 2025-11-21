<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        $columns = Schema::getColumnListing('rejected_opportunities');
        
        // Check if we have potential_customer_name or customer_name
        if (in_array('potential_customer_name', $columns)) {
            echo "potential_customer_name already exists\n";
        } elseif (in_array('customer_name', $columns)) {
            // Rename customer_name to potential_customer_name
            DB::statement('ALTER TABLE rejected_opportunities CHANGE customer_name potential_customer_name VARCHAR(255)');
            echo "Renamed customer_name to potential_customer_name\n";
        } else {
            // Neither exists, add potential_customer_name
            DB::statement('ALTER TABLE rejected_opportunities ADD COLUMN potential_customer_name VARCHAR(255) NULL AFTER id');
            echo "Added potential_customer_name column\n";
        }

        // Add other columns if they don't exist
        $columns = Schema::getColumnListing('rejected_opportunities');
        
        $newColumns = [
            'email' => 'VARCHAR(255) NULL AFTER potential_customer_name',
            'phone' => 'VARCHAR(20) NULL AFTER email',
            'location' => 'TEXT NULL AFTER phone',
            'created_by' => 'BIGINT UNSIGNED NULL AFTER location',
            'remarks' => 'TEXT NULL AFTER created_by',
        ];

        foreach ($newColumns as $columnName => $definition) {
            if (!in_array($columnName, $columns)) {
                DB::statement("ALTER TABLE rejected_opportunities ADD COLUMN $columnName $definition");
                echo "Added $columnName column\n";
            } else {
                echo "$columnName already exists\n";
            }
        }

        // Handle created_by foreign key
        $this->handleCreatedByForeignKey();
    }

    private function handleCreatedByForeignKey()
    {
        // Get first user ID
        $firstUserId = DB::table('users')->value('id');
        
        if (!$firstUserId) {
            // Create default user
            $firstUserId = DB::table('users')->insertGetId([
                'name' => 'System Admin',
                'email' => 'admin@system.com',
                'password' => bcrypt('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Update null values
        DB::table('rejected_opportunities')
            ->whereNull('created_by')
            ->update(['created_by' => $firstUserId]);

        // Make sure created_by is not null and add foreign key
        try {
            DB::statement('ALTER TABLE rejected_opportunities MODIFY created_by BIGINT UNSIGNED NOT NULL');
            
            // Check if foreign key already exists
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                WHERE TABLE_NAME = 'rejected_opportunities' 
                AND COLUMN_NAME = 'created_by'
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ");
            
            if (empty($foreignKeys)) {
                DB::statement('
                    ALTER TABLE rejected_opportunities 
                    ADD CONSTRAINT rejected_opportunities_created_by_foreign 
                    FOREIGN KEY (created_by) REFERENCES users(id)
                ');
            }
        } catch (\Exception $e) {
            echo "Error with foreign key: " . $e->getMessage() . "\n";
        }
    }

    public function down()
    {
        // We'll keep this simple - just drop the foreign key
        try {
            DB::statement('ALTER TABLE rejected_opportunities DROP FOREIGN KEY rejected_opportunities_created_by_foreign');
        } catch (\Exception $e) {
            // Ignore if doesn't exist
        }
    }
};