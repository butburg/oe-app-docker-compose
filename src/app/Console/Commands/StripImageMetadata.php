<?php

namespace App\Console\Commands;

use App\Actions\StoreImage;
use App\Models\ImageVariant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Removes EXIF/GPS and other metadata from images stored before FEAT-015.
 * Files without metadata are skipped, so running it again changes nothing.
 *
 *   docker compose run --rm artisan images:strip-metadata --dry-run
 *   docker compose run --rm artisan images:strip-metadata
 */
class StripImageMetadata extends Command
{
    protected $signature = 'images:strip-metadata
                            {--dry-run : Only list the files that still contain metadata}';

    protected $description = 'Remove EXIF, GPS, XMP and IPTC data from all stored image variants';

    public function handle(StoreImage $storeImage): int
    {
        $disk = Storage::disk('public');
        $found = 0;
        $missing = 0;

        foreach (ImageVariant::lazyById() as $variant) {
            if (!$disk->exists($variant->path)) {
                $missing++;
                continue;
            }

            $imagick = new \Imagick($disk->path($variant->path));
            if (!$this->hasMetadata($imagick)) {
                continue;
            }

            $found++;
            $this->line(($this->option('dry-run') ? 'Would strip: ' : 'Stripped: ') . $variant->path);

            if (!$this->option('dry-run')) {
                // Stored variants are already oriented, stripping only drops the data blocks
                $storeImage->stripMetadata($imagick);
                $imagick->setImageCompressionQuality($variant->quality ?: 80);
                $disk->put($variant->path, $imagick->getImageBlob());
            }
        }

        $this->info(($this->option('dry-run') ? "{$found} files contain metadata" : "{$found} files stripped")
            . ($missing ? ", {$missing} files missing" : '') . '.');

        return self::SUCCESS;
    }

    private function hasMetadata(\Imagick $imagick): bool
    {
        $profiles = array_diff($imagick->getImageProfiles('*', false), ['icc']);

        return $profiles !== [] || $imagick->getImageProperties('exif:*', false) !== [];
    }
}
