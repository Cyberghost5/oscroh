<?php

namespace App\Migrations;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

abstract class CompatSettingsMigration extends SettingsMigration
{
    public function __construct()
    {
        $table = env('SETTINGS_TABLE');

        if (!$table) {
            try {
                $isLegacySettingsTable = Schema::hasTable('settings')
                    && Schema::hasColumn('settings', 'key')
                    && !Schema::hasColumn('settings', 'name');

                if ($isLegacySettingsTable && Schema::hasTable('settings_new')) {
                    $table = 'settings_new';
                }
            } catch (\Throwable $e) {
                // Fall back to default settings repository table.
            }
        }

        if ($table) {
            Config::set('settings.repositories.database.table', $table);
        }

        parent::__construct();
    }
}
