<?php

namespace App\Console\Commands;

use App\Models\ImageVariant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Moves image files stored before FEAT-011 from the public disk (reachable under /storage/...)
 * to the images disk, which is only reachable through /img/{variant}/{token}.
 * Files that were already moved are skipped, so running it again changes nothing.
 *
 *   docker compose run --rm artisan images:move-private --dry-run
 *   docker compose run --rm artisan images:move-private
 */
class MoveImagesPrivate extends Command
{
    protected $signature = 'images:move-private
                            {--dry-run : Only list the files that are still on the public disk}';

    protected $description = 'Move all stored image variants from the public disk to the private images disk';

    public function handle(): int
    {
        $public = Storage::disk('public');
        $images = Storage::disk('images');
        $moved = 0;

        foreach (ImageVariant::lazyById() as $variant) {
            if (!$public->exists($variant->path)) {
                continue;
            }

            $moved++;
            $this->line(($this->option('dry-run') ? 'Would move: ' : 'Moved: ') . $variant->path);

            if (!$this->option('dry-run')) {
                $images->writeStream($variant->path, $public->readStream($variant->path));
                $public->delete($variant->path);
            }
        }

        $this->info($this->option('dry-run') ? "{$moved} files to move." : "{$moved} files moved.");

        return self::SUCCESS;
    }
}
