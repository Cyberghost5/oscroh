<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('ai_auto_reply_enabled')->default(false)->after('last_ip');
            $table->boolean('ai_auto_reply_respond_to_paid')->default(false)->after('ai_auto_reply_enabled');
            $table->unsignedSmallInteger('ai_auto_reply_delay_seconds')->default(0)->after('ai_auto_reply_respond_to_paid');
            $table->text('ai_auto_reply_system_prompt')->nullable()->after('ai_auto_reply_delay_seconds');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'ai_auto_reply_enabled',
                'ai_auto_reply_respond_to_paid',
                'ai_auto_reply_delay_seconds',
                'ai_auto_reply_system_prompt',
            ]);
        });
    }
};
