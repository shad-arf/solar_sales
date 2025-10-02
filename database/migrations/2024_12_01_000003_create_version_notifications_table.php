<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('version_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('version')->unique();
            $table->string('title');
            $table->text('description');
            $table->json('features')->nullable();
            $table->date('release_date');
            $table->boolean('is_active')->default(true);
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->timestamps();

            $table->index(['is_active', 'release_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('version_notifications');
    }
};