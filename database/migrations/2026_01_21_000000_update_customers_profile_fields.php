<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postcode')->nullable();
            $table->string('country')->nullable();

            // Rename phone to phone_number to avoid confusion and keep uniqueness
            $table->renameColumn('phone', 'phone_number');
            $table->index('phone_number');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['phone_number']);
            $table->renameColumn('phone_number', 'phone');
            $table->dropColumn(['first_name', 'last_name', 'city', 'state', 'postcode', 'country']);
        });
    }
};
