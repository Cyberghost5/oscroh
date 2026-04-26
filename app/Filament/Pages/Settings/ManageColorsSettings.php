<?php

namespace App\Filament\Pages\Settings;

use App\Settings\ColorsSettings;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\File;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;
use Symfony\Component\Process\Process;
use BackedEnum;

class ManageColorsSettings extends SettingsPage
{
    use HasPageShield;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-paint-brush';

    protected static ?string $slug = 'settings/colors';

    protected static string $settings = ColorsSettings::class;

    protected static ?string $title = 'Theme Settings';

    public bool $includeRtlVersion = false;

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Customize your theme colors')
                ->description('Customize your branding by adjusting theme colors.')
                ->schema([

                    Placeholder::make('theme_generation')
                        ->label('')
                        ->hiddenLabel()
                        ->columnSpanFull()
                        ->content(new HtmlString(view('filament.partials.colors')->render())),

                    ColorPicker::make('theme_color_code')
                        ->label('Primary color')
                        ->helperText('Used for buttons, accents, and highlights.')
                        ->required(),

                    ColorPicker::make('theme_gradient_from')
                        ->label('Gradient start color')
                        ->helperText('Starting color for gradients and background transitions.')
                        ->required(),

                    ColorPicker::make('theme_gradient_to')
                        ->label('Gradient end color')
                        ->helperText('Ending color for gradients and background transitions.')
                        ->required(),

                    Toggle::make('include_rtl_version')
                        ->label('Include RTL version')
                        ->helperText('Includes a right-to-left CSS file in the generated theme.')
                        ->live()
                        ->afterStateUpdated(fn ($state) => $this->includeRtlVersion = $state)
                        ->dehydrated(false)
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->columnSpanFull(),
        ]);
    }

    protected function afterSave(): void
    {
        try {
            $state = $this->form->getState();
            $primary = $this->normalizeHexColor($state['theme_color_code'] ?? null);
            $gradientFrom = $this->normalizeHexColor($state['theme_gradient_from'] ?? null);
            $gradientTo = $this->normalizeHexColor($state['theme_gradient_to'] ?? null);

            if (!$primary || !$gradientFrom || !$gradientTo) {
                throw new \RuntimeException('Invalid theme colors provided.');
            }

            $this->compileThemeLocally($primary, $gradientFrom, $gradientTo, $this->includeRtlVersion);

            Notification::make()
                ->title('Theme generated & applied locally.')
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Theme generation failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function compileThemeLocally(string $primary, string $gradientFrom, string $gradientTo, bool $includeRtlVersion): void
    {
        $buildId = 'theme-build-'.date('YmdHis').'-'.bin2hex(random_bytes(4));
        $tmpRelativeDir = 'storage/app/tmp/'.$buildId;
        $tmpAbsoluteDir = base_path($tmpRelativeDir);

        File::ensureDirectoryExists($tmpAbsoluteDir);
        File::ensureDirectoryExists(public_path('css/theme'));

        $compileTargets = [
            'bootstrap.css' => 'bootstrap',
            'bootstrap.dark.css' => 'bootstrap.dark',
        ];

        if ($includeRtlVersion) {
            $compileTargets['bootstrap.rtl.css'] = 'bootstrap.rtl';
            $compileTargets['bootstrap.rtl.dark.css'] = 'bootstrap.rtl.dark';
        }

        try {
            foreach ($compileTargets as $outputFile => $entryImport) {
                $entryFile = str_replace('.css', '.scss', $outputFile);
                $entryRelativePath = $tmpRelativeDir.'/'.$entryFile;
                $outputRelativePath = 'public/css/theme/'.$outputFile;
                $entryAbsolutePath = base_path($entryRelativePath);

                File::put(
                    $entryAbsolutePath,
                    $this->buildSassEntryContent($primary, $gradientFrom, $gradientTo, $entryImport)
                );

                $this->runSassCompileCommand($entryRelativePath, $outputRelativePath);
            }
        } finally {
            File::deleteDirectory($tmpAbsoluteDir);
        }
    }

    protected function buildSassEntryContent(string $primary, string $gradientFrom, string $gradientTo, string $entryImport): string
    {
        return implode("\n", [
            '$primary: '.$primary.';',
            '$primary-alt: '.$primary.';',
            '$primary-gradient: '.$gradientFrom.';',
            '$primary-gradient-state: '.$gradientTo.';',
            '@import "resources/sass/'.$entryImport.'";',
            '',
        ]);
    }

    protected function runSassCompileCommand(string $inputRelativePath, string $outputRelativePath): void
    {
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        $sassBinary = $isWindows
            ? base_path('node_modules/.bin/sass.cmd')
            : base_path('node_modules/.bin/sass');

        if (!File::exists($sassBinary)) {
            throw new \RuntimeException('Sass binary not found. Run npm install first.');
        }

        $process = new Process([
            $sassBinary,
            '--no-source-map',
            '--style=compressed',
            '--load-path=.',
            $inputRelativePath.':'.$outputRelativePath,
        ], base_path(), null, null, 300);

        $process->run();

        if (!$process->isSuccessful()) {
            $error = trim($process->getErrorOutput()) ?: trim($process->getOutput());
            throw new \RuntimeException($error !== '' ? $error : 'Local theme compilation failed.');
        }
    }

    protected function normalizeHexColor(?string $color): ?string
    {
        if (!$color) {
            return null;
        }

        $normalized = '#'.ltrim(trim($color), '#');

        return preg_match('/^#([0-9a-fA-F]{6})$/', $normalized) ? strtolower($normalized) : null;
    }
}
