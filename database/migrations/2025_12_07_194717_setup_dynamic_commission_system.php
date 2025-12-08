<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Add has_commission to users table
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'has_commission')) {
                $table->boolean('has_commission')->default(false)->after('commission_rate');
            }
        });

        // 2. Create role_commission_settings table
        if (!Schema::hasTable('role_commission_settings')) {
            Schema::create('role_commission_settings', function (Blueprint $table) {
                $table->id();
                $table->string('role_name');
                $table->boolean('has_commission')->default(false);
                $table->decimal('default_commission_rate', 5, 2)->nullable()->default(0);
                $table->boolean('is_commission_editable')->default(true);
                $table->timestamps();
                
                $table->unique('role_name');
            });
        }

        // 3. Update sales table column if it exists
        if (Schema::hasTable('sales')) {
            Schema::table('sales', function (Blueprint $table) {
                if (Schema::hasColumn('sales', 'salesperson_id')) {
                    $table->renameColumn('salesperson_id', 'commission_user_id');
                } else if (!Schema::hasColumn('sales', 'commission_user_id')) {
                    $table->foreignId('commission_user_id')->nullable()->constrained('users')->onDelete('set null');
                }
            });
        }
    }

    public function down()
    {
        // Reverse the changes
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'has_commission')) {
                $table->dropColumn('has_commission');
            }
        });

        Schema::dropIfExists('role_commission_settings');

        if (Schema::hasTable('sales')) {
            Schema::table('sales', function (Blueprint $table) {
                if (Schema::hasColumn('sales', 'commission_user_id')) {
                    $table->renameColumn('commission_user_id', 'salesperson_id');
                }
            });
        }
    }
};