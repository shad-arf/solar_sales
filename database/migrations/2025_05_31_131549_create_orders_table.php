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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('line_discount', 10, 2)->default(0);
            $table->decimal('line_total', 10, 2);
            $table->enum('status', ['pending','completed','cancelled'])->default('pending');
            $table->string('note')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // (Optional) If you want to ensure that each sale+item pair is unique:
            // $table->unique(['sale_id','item_id']);
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
