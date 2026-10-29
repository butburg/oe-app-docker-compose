<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;
    // Define the fillable attributes for mass assignment
    protected $fillable = ['upload_size', 'post_id', 'user_id'];
    // Define the relationship with ImageVariant
    public function variants()
    {
        return $this->hasMany(ImageVariant::class);
    }
    // Define the relationship with Post
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
    // Define the relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Link to a variant of this image, valid for the current and the next window (FEAT-011).
     * Null if the variant does not exist.
     */
    public function url(string $sizeType): ?string
    {
        $variant = $this->variants->firstWhere('size_type', $sizeType);

        return $variant ? route('images.show', [
            'variant' => $variant->id,
            'token' => self::token($variant->id, $this->token_version ?? 0, self::currentWindow()),
        ]) : null;
    }

    /**
     * Make all links to this image invalid at once, e.g. when the post becomes sensitive.
     */
    public function invalidateLinks(): void
    {
        $this->increment('token_version');
    }

    public static function windowSeconds(): int
    {
        return config('images.link_window_days') * 86400;
    }

    public static function currentWindow(): int
    {
        return intdiv(now()->getTimestamp(), self::windowSeconds());
    }

    /**
     * Nothing is stored: the token is computed from the variant, the time window and the version,
     * so it changes every window by itself and cannot be computed without the APP_KEY.
     */
    public static function token(int $variantId, int $tokenVersion, int $window): string
    {
        return substr(hash_hmac('sha256', "{$variantId}|{$window}|{$tokenVersion}", config('app.key')), 0, 32);
    }
}
