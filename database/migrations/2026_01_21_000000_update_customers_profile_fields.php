<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'first_name')) {
                $table->string('first_name')->nullable();
            }
            if (!Schema::hasColumn('customers', 'last_name')) {
                $table->string('last_name')->nullable();
            }
            if (!Schema::hasColumn('customers', 'city')) {
                $table->string('city')->nullable();
            }
            if (!Schema::hasColumn('customers', 'state')) {
                $table->string('state')->nullable();
            }
            if (!Schema::hasColumn('customers', 'postcode')) {
                $table->string('postcode')->nullable();
            }
            if (!Schema::hasColumn('customers', 'country')) {
                $table->string('country')->nullable();
            }

            // Rename phone to phone_number when present; otherwise ensure phone_number exists
            if (Schema::hasColumn('customers', 'phone')) {
                $table->renameColumn('phone', 'phone_number');
            } elseif (!Schema::hasColumn('customers', 'phone_number')) {
                $table->string('phone_number')->nullable();
            }

        });

        // Add index only if it does not already exist
        if (Schema::hasColumn('customers', 'phone_number')) {
            $hasIndex = collect(DB::select("SHOW INDEX FROM customers WHERE Key_name = 'customers_phone_number_index'"))->isNotEmpty();
            if (!$hasIndex) {
                Schema::table('customers', function (Blueprint $table) {
                    $table->index('phone_number');
                });
            }
        }
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'phone_number')) {
                $table->dropIndex(['phone_number']);
            }

            if (Schema::hasColumn('customers', 'phone_number') && !Schema::hasColumn('customers', 'phone')) {
                $table->renameColumn('phone_number', 'phone');
            }

            $columns = ['first_name', 'last_name', 'city', 'state', 'postcode', 'country'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('customers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
