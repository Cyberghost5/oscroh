<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_reply_logs', function (Blueprint $table) {
            $table->bigIncrements('id');

            // The creator whose AI replied
            $table->unsignedBigInteger('creator_user_id')->index();
            $table->foreign('creator_user_id')->references('id')->on('users')->onDelete('cascade');

            // The fan who sent the triggering message
            $table->unsignedBigInteger('fan_user_id')->index();
            $table->foreign('fan_user_id')->references('id')->on('users')->onDelete('cascade');

            // The fan's message that triggered the AI reply
            $table->unsignedBigInteger('trigger_message_id')->nullable();
            $table->foreign('trigger_message_id')->references('id')->on('user_messages')->onDelete('set null');

            // The AI-generated reply message
            $table->unsignedBigInteger('reply_message_id')->nullable();
            $table->foreign('reply_message_id')->references('id')->on('user_messages')->onDelete('set null');

            // OpenAI usage tracking
            $table->integer('tokens_used')->nullable();
            $table->string('model', 60)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_reply_logs');
    }
};
