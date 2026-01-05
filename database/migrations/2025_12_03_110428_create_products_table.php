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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->unique();
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->text('specifications')->nullable();
            
            // Pricing
            $table->decimal('price', 10, 2);
            $table->decimal('compare_at_price', 10, 2)->nullable();
            $table->decimal('cost_price', 10, 2)->nullable();
            
            // Inventory
            $table->integer('stock_quantity')->default(0);
            $table->boolean('track_inventory')->default(true);
            $table->integer('low_stock_threshold')->nullable();
            $table->boolean('allow_backorder')->default(false);
            
            // Images
            $table->string('main_image')->nullable();
            $table->json('gallery_images')->nullable();
            
            // Relationships
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->foreignId('collection_id')->nullable()->constrained('collections')->nullOnDelete();
            
            // Status flags
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_new')->default(false);
            $table->boolean('is_bestseller')->default(false);
            $table->boolean('is_digital')->default(false);
            $table->boolean('is_virtual')->default(false);
            
            // Physical attributes
            $table->decimal('weight', 8, 2)->nullable();
            $table->string('weight_unit')->default('kg');
            $table->decimal('length', 8, 2)->nullable();
            $table->decimal('width', 8, 2)->nullable();
            $table->decimal('height', 8, 2)->nullable();
            $table->string('dimension_unit')->default('cm');
            
            // Tax & Shipping
            $table->boolean('taxable')->default(true);
            $table->decimal('tax_rate', 5, 2)->nullable();
            $table->boolean('requires_shipping')->default(true);
            $table->decimal('shipping_weight', 8, 2)->nullable();
            
            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            
            // Additional
            $table->integer('sort_order')->default(0);
            $table->integer('view_count')->default(0);
            $table->integer('sold_count')->default(0);
            $table->decimal('rating', 3, 2)->nullable();
            $table->integer('rating_count')->default(0);
            
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['is_active', 'is_featured']);
            $table->index('category_id');
            $table->index('brand_id');
            $table->index('collection_id');
            $table->index('sku');
            $table->index('sort_order');
            $table->index('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
