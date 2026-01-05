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
        Schema::create('price_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('price_list_id')->nullable()->constrained('price_lists')->onDelete('set null');
            $table->foreignId('price_rule_id')->nullable()->constrained('price_rules')->onDelete('set null');
            $table->decimal('old_price', 10, 2)->nullable();
            $table->decimal('new_price', 10, 2);
            $table->decimal('compare_at_price', 10, 2)->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('reason')->nullable(); // e.g., 'manual_update', 'bulk_import', 'promotion', 'cost_change'
            $table->text('notes')->nullable();
            $table->timestamp('changed_at')->useCurrent();
            $table->timestamps();

            $table->index('product_id');
            $table->index('price_list_id');
            $table->index('price_rule_id');
            $table->index('changed_by');
            $table->index('changed_at');
            $table->index('reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_history');
    }
};
