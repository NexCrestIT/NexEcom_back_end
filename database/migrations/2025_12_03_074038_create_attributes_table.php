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
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('type', ['text', 'number', 'select', 'multiselect', 'boolean', 'date', 'textarea'])->default('text');
            $table->boolean('is_required')->default(false);
            $table->boolean('is_filterable')->default(true);
            $table->boolean('is_searchable')->default(true);
            $table->boolean('is_active')->default(true);
            $table->string('default_value')->nullable();
            $table->json('validation_rules')->nullable();
            $table->json('options')->nullable(); // For select/multiselect options
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'is_filterable']);
            $table->index('sort_order');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attributes');
    }
};
