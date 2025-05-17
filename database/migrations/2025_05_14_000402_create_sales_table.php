<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->restrictOnDelete(); // keep item FK
            $table->unsignedBigInteger('customer_id'); // no FK constraint
            $table->unsignedInteger('quantity');
            $table->string('paid');
            $table->string('discount')->default(0);
            $table->string('total');
            $table->date('date');
            $table->timestamps();
            $table->softDeletes();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
