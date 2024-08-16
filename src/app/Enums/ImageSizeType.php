<?php

namespace App\Enums;

enum ImageSizeType: string
{
    case EXTRA_SMALL = 'xs';
    case SMALL = 's';
    case MEDIUM = 'm';
    case LARGE = 'l';
    case EXTRA_LARGE = 'xl';

    public function getMaxWidth(): int
    {
        return match ($this) {
            self::EXTRA_SMALL => 40,
            self::SMALL => 100,
            self::MEDIUM => 360,
            self::LARGE => 800,
            self::EXTRA_LARGE => 1920,
        };
    }

    public function getMaxHeight(): int
    {
        return match ($this) {
            self::EXTRA_SMALL => 40,
            self::SMALL => 100,
            self::MEDIUM => 640,
            self::LARGE => 800,
            self::EXTRA_LARGE => 1080,
        };
    }

    public function getQuality(): int
    {
        return match ($this) {
            self::EXTRA_SMALL => 80,
            self::MEDIUM => 85,
            self::EXTRA_LARGE => 100,
            default => 90,
        };
    }
}
