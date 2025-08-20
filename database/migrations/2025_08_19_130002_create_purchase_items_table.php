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
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('purchases')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items')->onDelete('restrict');
            $table->integer('quantity_purchased');
            $table->decimal('purchase_price', 10, 2); // The price we paid to buy this item
            $table->decimal('line_total', 12, 2); // quantity_purchased * purchase_price
            $table->timestamps();

            // Indexes
            $table->index(['purchase_id', 'item_id']);
            $table->index('item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
    }
};