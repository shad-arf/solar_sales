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
        Schema::create('item_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->string('name', 100); // e.g., "Regular Price", "Wholesale", "Per Kg"
            $table->decimal('price', 10, 2);
            $table->string('unit', 50)->nullable(); // e.g., "piece", "kg", "meter"
            $table->string('description', 255)->nullable();
            $table->unsignedInteger('category_id')->nullable(); // for future categorization
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            // Indexes
            $table->index(['item_id', 'is_active']);
            $table->index(['item_id', 'is_default']);
            $table->index('category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_prices');
    }
};