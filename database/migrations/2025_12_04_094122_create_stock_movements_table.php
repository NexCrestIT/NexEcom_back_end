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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('inventory_id')->nullable()->constrained('inventory')->onDelete('set null');
            $table->enum('type', ['in', 'out', 'adjustment', 'transfer', 'return', 'damage', 'expired'])->default('adjustment');
            $table->integer('quantity');
            $table->integer('quantity_before');
            $table->integer('quantity_after');
            $table->string('reference_type')->nullable(); // order, purchase, adjustment, etc.
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('location')->nullable()->default('main');
            $table->string('to_location')->nullable(); // For transfers
            $table->decimal('cost_price', 10, 2)->nullable();
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('product_id');
            $table->index('inventory_id');
            $table->index('type');
            $table->index(['reference_type', 'reference_id']);
            $table->index('location');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
