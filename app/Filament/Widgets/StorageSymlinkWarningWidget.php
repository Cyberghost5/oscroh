<?php

namespace App\Filament\Widgets;

use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class StorageSymlinkWarningWidget extends Widget
{
    public bool $symlinkFixed = false;

    protected string $view = 'filament.widgets.storage-symlink-warning-widget';

    public function getColumnSpan(): int|string|array
    {
        return 2;
    }

    public function createSymlink(): void
    {
        $link   = public_path('storage');
        $target = storage_path('app/public');

        // Ensure the real target directory exists
        if (!is_dir($target)) {
            File::makeDirectory($target, 0755, true);
        }

        // If public/storage exists as a real directory (not a symlink),
        // migrate its contents into storage/app/public then delete it.
        if (file_exists($link) && !is_link($link) && is_dir($link)) {
            // Copy files from public/storage → storage/app/public (no overwrite)
            foreach (File::allFiles($link) as $file) {
                $relative = $file->getRelativePathname();
                $dest     = $target . DIRECTORY_SEPARATOR . $relative;

                if (!File::exists($dest)) {
                    File::ensureDirectoryExists(dirname($dest));
                    File::copy($file->getRealPath(), $dest);
                }
            }

            // Remove the real directory so storage:link can create the symlink
            File::deleteDirectory($link);
        }

        $exitCode = Artisan::call('storage:link');

        // Verify the symlink actually resolves to the right target
        $resolvedLink   = realpath($link);
        $resolvedTarget = realpath($target);
        $success = $resolvedLink && $resolvedTarget
            && strtolower(str_replace('\\', '/', $resolvedLink))
            === strtolower(str_replace('\\', '/', $resolvedTarget));

        if (!$success) {
            Notification::make()
                ->title('Could not create the symlink automatically.')
                ->body('Please run `php artisan storage:link` from the command line as an administrator.')
                ->danger()
                ->send();
            return;
        }

        Notification::make()
            ->title('Storage symlink created successfully.')
            ->success()
            ->send();

        $this->symlinkFixed = true;
    }

    public static function canView(): bool
    {
        $link   = public_path('storage');
        $target = storage_path('app/public');

        if (!file_exists($link)) {
            return true;
        }

        $resolvedLink   = realpath($link);
        $resolvedTarget = realpath($target);

        if (!$resolvedLink || !$resolvedTarget) {
            return true;
        }

        // Normalize separators and casing for Windows compatibility
        $normalize = fn (string $p): string =>
            strtolower(rtrim(str_replace('\\', '/', $p), '/'));

        return $normalize($resolvedLink) !== $normalize($resolvedTarget);
    }
}

