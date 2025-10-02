<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Drop the table if it exists with the long constraint name issue
        Schema::dropIfExists('user_version_notifications');
        
        // Recreate with proper constraint names
        Schema::create('user_version_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('version_notification_id')->constrained()->onDelete('cascade');
            $table->timestamp('viewed_at')->nullable();
            $table->timestamp('dismissed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'version_notification_id'], 'user_version_notif_unique');
            $table->index(['user_id', 'viewed_at'], 'user_version_viewed_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_version_notifications');
    }
};