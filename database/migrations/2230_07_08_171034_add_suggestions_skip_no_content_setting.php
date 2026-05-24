<?php

use App\Migrations\CompatSettingsMigration;

return new class extends CompatSettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('feed.suggestions_skip_no_content', false);
    }

    public function down(): void
    {
        $this->migrator->delete('feed.suggestions_skip_no_content');
    }
};
