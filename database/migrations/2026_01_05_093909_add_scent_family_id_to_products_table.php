<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('scent_family_id')->nullable()->after('gender_id')->constrained('scent_families')->nullOnDelete();
            $table->index('scent_family_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['scent_family_id']);
            $table->dropIndex(['scent_family_id']);
            $table->dropColumn('scent_family_id');
        });
    }
};
