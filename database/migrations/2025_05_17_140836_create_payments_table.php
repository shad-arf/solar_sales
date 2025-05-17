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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_id'); // FK to the sale
            $table->decimal('amount', 10, 2); // Amount paid in this transaction
            $table->timestamp('paid_at')->useCurrent(); // When it was paid
            $table->text('note')->nullable(); // Optional (e.g., “Paid in cash”)
            $table->timestamps();

            $table->softDeletes();
            // optional: you can add FK if you want
            $table->foreign('sale_id')->references('id')->on('sales')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
