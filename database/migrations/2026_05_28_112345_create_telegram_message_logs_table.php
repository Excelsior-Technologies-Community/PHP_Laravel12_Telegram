<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('telegram_message_logs', function (Blueprint $table) {
            $table->id();
            $table->string('chat_id');
            $table->string('direction');
            $table->string('message_type')->default('info');
            $table->text('content');
            $table->string('title')->nullable();
            $table->string('status')->default('sent');
            $table->string('error_message')->nullable();
            $table->boolean('is_scheduled')->default(false);
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamps();

            $table->index(['chat_id', 'direction']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_message_logs');
    }
};