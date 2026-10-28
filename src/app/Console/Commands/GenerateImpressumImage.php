<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Random\Engine\Mt19937;
use Random\Randomizer;

/**
 * Renders the impressum contact or mail text as a noisy PNG, so scrapers
 * can't simply read it while people still can (FEAT-009).
 *
 * The text is only ever passed as arguments: the repository is public, so
 * the contact data must not be written into it as plain text.
 *
 * Each argument becomes one line of the image:
 *
 *   docker compose run --rm artisan impressum:image --contact "Name" "Street 1" "12345 City"
 *   docker compose run --rm artisan impressum:image --mail "someone@example.com"
 *
 * Output: public/images/impressum-{contact|mail}-protected.png
 */
class GenerateImpressumImage extends Command
{
    protected $signature = 'impressum:image
                            {lines* : The text, one argument per line}
                            {--contact : Write the address image}
                            {--mail : Write the mail image}
                            {--seed= : Fixed seed for a reproducible result}';

    protected $description = 'Render the impressum contact or mail text as a hard-to-scrape image';

    private const FONT_SIZE = 30;
    private const PADDING = 18;
    private const LINE_HEIGHT = 30;
    private const MAX_ROTATION = 3;
    private const MAX_SHIFT = 3;
    private const MAX_GAP = 3;

    private Randomizer $random;

    public function handle(): int
    {
        if ($this->option('contact') === $this->option('mail')) {
            $this->error('Pass exactly one of --contact or --mail.');

            return self::FAILURE;
        }

        $seed = $this->option('seed');
        $this->random = new Randomizer($seed === null ? null : new Mt19937((int) $seed));

        $type = $this->option('contact') ? 'contact' : 'mail';
        $target = public_path("images/impressum-{$type}-protected.png");

        imagepng($this->render($this->argument('lines')), $target, 9);

        $this->info("Written to {$target}");

        return self::SUCCESS;
    }

    private function render(array $lines): \GdImage
    {
        $font = resource_path('fonts/Figtree-Medium.ttf');

        $widest = max(array_map(
            fn ($line) => $this->textWidth($font, $line) + self::MAX_GAP * mb_strlen($line), $lines));
        $width = (int) $widest + 2 * self::PADDING;
        $height = self::LINE_HEIGHT * count($lines) + 2 * self::PADDING;

        $image = $this->noisyBackground($width, $height);

        foreach (array_values($lines) as $row => $line) {
            $baseline = self::PADDING + $row * self::LINE_HEIGHT + self::FONT_SIZE + 8;
            $x = self::PADDING + $this->between(0, 6);
            $previous = '';

            // each character gets its own angle, height and spacing, that is what breaks OCR
            foreach (mb_str_split($line) as $char) {
                // the bounding box only covers ink, a trailing "H" makes it measure the real advance
                $advance = $this->textWidth($font, $previous . $char . 'H') - $this->textWidth($font, $previous . 'H');
                $previous .= $char;

                if (trim($char) !== '') {
                    $shade = $this->random->getInt(20, 70);
                    imagettftext(
                        $image,
                        self::FONT_SIZE,
                        $this->between(-self::MAX_ROTATION, self::MAX_ROTATION),
                        (int) $x,
                        (int) ($baseline + $this->between(-self::MAX_SHIFT, self::MAX_SHIFT)),
                        imagecolorallocate($image, $shade, $shade, $shade + 20),
                        $font,
                        $char,
                    );
                }

                $x += $advance + $this->between(0, self::MAX_GAP);
            }
        }

        $this->strikeLines($image);

        return $image;
    }

    private function noisyBackground(int $width, int $height): \GdImage
    {
        $image = imagecreatetruecolor($width, $height);
        imagefill($image, 0, 0, imagecolorallocate($image, 238, 236, 230));

        for ($i = 0; $i < intdiv($width * $height, 40); $i++) {
            $shade = $this->random->getInt(150, 235);
            imagesetpixel($image, $this->random->getInt(0, $width - 1), $this->random->getInt(0, $height - 1),
                imagecolorallocate($image, $shade, $shade, $shade - 10));
        }

        imagesetthickness($image, 2);
        for ($i = 0; $i < 12; $i++) {
            $diameter = 2 * $this->random->getInt(8, 30);
            imagearc($image, $this->random->getInt(0, $width), $this->random->getInt(0, $height),
                $diameter, $diameter, 0, 360, imagecolorallocate($image,
                    $this->random->getInt(190, 230), $this->random->getInt(190, 230), $this->random->getInt(190, 230)));
        }

        imagefilter($image, IMG_FILTER_GAUSSIAN_BLUR);

        return $image;
    }

    private function strikeLines(\GdImage $image): void
    {
        $width = imagesx($image);
        $height = imagesy($image);

        imagesetthickness($image, 3);
        for ($i = 0; $i < 2; $i++) {
            $shade = $this->random->getInt(190, 240);
            $color = imagecolorallocatealpha($image, $shade, $shade, $shade, 25);
            $y = $this->random->getInt(self::PADDING, $height - self::PADDING);

            for ($x = 0; $x < $width; $x += 40) {
                $nextY = $this->random->getInt(self::PADDING, $height - self::PADDING);
                imageline($image, $x, $y, $x + 40, $nextY, $color);
                $y = $nextY;
            }
        }
    }

    private function textWidth(string $font, string $text): float
    {
        $box = imagettfbbox(self::FONT_SIZE, 0, $font, $text);

        return $box[2] - $box[0];
    }

    private function between(float $min, float $max): float
    {
        return $min + $this->random->nextFloat() * ($max - $min);
    }
}
