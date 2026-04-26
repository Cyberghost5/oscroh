<?php

namespace App\Filament\Pages\Settings;

use App\Settings\CodeAndAdsSettings;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\Textarea;
use Filament\Pages\SettingsPage;
use BackedEnum;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ManageCodeAndAdsSettings extends SettingsPage
{
    use HasPageShield;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-code-bracket';

    protected static ?string $slug = 'settings/custom-code-ads';

    protected static string $settings = CodeAndAdsSettings::class;

    protected static ?string $title = 'Code & Ads Settings';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->columnSpanFull()
                ->columns(1)
                ->schema([
                    Textarea::make('custom_css')
                        ->label('Custom CSS code')
                        ->rows(6)
                        ->hint('Paste raw <style> code or rules.'),

                    Textarea::make('custom_js')
                        ->label('Custom JS code')
                        ->rows(6)
                        ->hint('Paste raw <script> code or JavaScript.')
                        ->helperText("Paste JS or a <script>. Analytics/ads must be consent-gated (type='text/plain' data-category='analytics')."),

                    Textarea::make('sidebar_ad_spot')
                        ->label('Sidebar ad HTML')
                        ->rows(6)
                        ->hint('Will be shown on user feed & profile sidebars.'),
                ]),
        ]);
    }
}
