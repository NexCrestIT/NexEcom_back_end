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
        Schema::create('attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->constrained('attributes')->onDelete('cascade');
            $table->string('value');
            $table->string('slug')->nullable();
            $table->string('display_value')->nullable(); // For display purposes (e.g., color codes, icons)
            $table->string('color_code')->nullable(); // For color attributes
            $table->string('image')->nullable(); // For image-based values
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['attribute_id', 'is_active']);
            $table->index('sort_order');
            $table->unique(['attribute_id', 'value']); // Unique value per attribute
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attribute_values');
    }
};
