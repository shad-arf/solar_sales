<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_adjustments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->integer('system_quantity'); // What system thinks we have
            $table->integer('actual_quantity'); // What we actually have
            $table->integer('adjustment_quantity'); // Difference (actual - system)
            $table->enum('adjustment_type', ['increase', 'decrease']);
            $table->enum('reason', ['damaged', 'lost', 'theft', 'found', 'recount', 'other']);
            $table->text('notes')->nullable();
            $table->date('adjustment_date');
            $table->decimal('financial_impact', 10, 2)->default(0); // Cost of lost/found items
            $table->timestamps();
            
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_adjustments');
    }
};