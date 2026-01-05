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
        Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('location')->nullable()->default('main'); // For multi-location support
            $table->integer('quantity')->default(0);
            $table->integer('reserved_quantity')->default(0); // Reserved for orders
            $table->integer('available_quantity')->default(0); // quantity - reserved_quantity
            $table->integer('low_stock_threshold')->nullable();
            $table->boolean('is_low_stock')->default(false);
            $table->decimal('cost_price', 10, 2)->nullable();
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['product_id', 'location']);
            $table->index('product_id');
            $table->index('location');
            $table->index('is_low_stock');
            $table->index('quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory');
    }
};
