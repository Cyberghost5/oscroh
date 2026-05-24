<?php

use App\Migrations\CompatSettingsMigration;

return new class extends CompatSettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('ai.ai_auto_reply_enabled', false);
        $this->migrator->add('ai.ai_auto_reply_model', 'gpt-4o');
        $this->migrator->add('ai.ai_auto_reply_system_prompt', '');
        $this->migrator->add('ai.ai_auto_reply_max_per_conversation', 10);
        $this->migrator->add('ai.ai_auto_reply_context_messages', 10);
    }

    public function down(): void
    {
        $this->migrator->delete('ai.ai_auto_reply_enabled');
        $this->migrator->delete('ai.ai_auto_reply_model');
        $this->migrator->delete('ai.ai_auto_reply_system_prompt');
        $this->migrator->delete('ai.ai_auto_reply_max_per_conversation');
        $this->migrator->delete('ai.ai_auto_reply_context_messages');
    }
};
