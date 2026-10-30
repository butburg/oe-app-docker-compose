<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImageVariant extends Model
{
    use HasFactory;
    // Define the fillable attributes for mass assignment
    protected $fillable = ['image_id', 'path', 'size_type', 'quality', 'width', 'height'];

    protected static function booted(): void
    {
        static::creating(function (ImageVariant $variant) {
            $variant->public_id ??= static::newPublicId();
        });
    }

    /**
     * Random id for the image link (FEAT-011): 16 hex characters, nothing to count or guess.
     */
    public static function newPublicId(): string
    {
        return bin2hex(random_bytes(8));
    }

    // Define the relationship with Image
    public function image()
    {
        return $this->belongsTo(Image::class);
    }
}
