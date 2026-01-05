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
        Schema::create('price_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('price_list_id')->constrained('price_lists')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->decimal('price', 10, 2);
            $table->decimal('compare_at_price', 10, 2)->nullable();
            $table->integer('min_quantity')->default(1);
            $table->integer('max_quantity')->nullable();
            $table->string('customer_group')->nullable(); // e.g., 'vip', 'wholesale', 'retail'
            $table->string('region')->nullable(); // e.g., 'US', 'EU', 'ASIA'
            $table->string('currency', 3)->default('USD');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->dateTime('valid_from')->nullable();
            $table->dateTime('valid_to')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('price_list_id');
            $table->index('product_id');
            $table->index('is_active');
            $table->index(['min_quantity', 'max_quantity']);
            $table->index('customer_group');
            $table->index('region');
            $table->index(['valid_from', 'valid_to']);
            $table->unique(['price_list_id', 'product_id', 'min_quantity', 'customer_group', 'region'], 'unique_price_rule');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_rules');
    }
};
