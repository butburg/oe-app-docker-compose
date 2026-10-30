<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Post extends Model {
    use HasFactory;
    // which fields can be mass-assigned
    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'username',
        'is_published',
        'is_sensitive',
        'published_at',
    ];

    // using $casts for date casting
    protected $casts = [
        'published_at' => 'datetime',
        'notified_at' => 'datetime',
    ];

    /**
     * Longest slug before the suffix, cut at a word boundary.
     */
    public const SLUG_MAX_LENGTH = 60;

    protected static function booted(): void {
        // Drafts have no slug: it is made from the title at the first publication and fixed from then on,
        // so shared links never break and drafts do not take names away (FEAT-014)
        static::saving(function (Post $post) {
            if ($post->slug === null && $post->published_at !== null) {
                $post->slug = static::uniqueSlug($post->title, $post->id);
            }
        });

        // A post that becomes sensitive or a draft gets new image links, old links stop working (FEAT-011)
        static::updated(function (Post $post) {
            $becameSensitive = $post->wasChanged('is_sensitive') && $post->is_sensitive;
            $becameDraft = $post->wasChanged('is_published') && !$post->is_published;

            if (($becameSensitive || $becameDraft) && $post->image) {
                $post->image->invalidateLinks();
            }
        });
    }

    /**
     * Slug from the title (umlauts transliterated, e.g. "Schöner Tag" → "schoener-tag"),
     * duplicates get a suffix: "tag", "tag-2", "tag-3". Titles without letters get a random slug.
     */
    public static function uniqueSlug(string $title, ?int $ignoreId = null): string {
        $base = Str::slug($title, '-', 'de');
        if (strlen($base) > self::SLUG_MAX_LENGTH) {
            $cut = substr($base, 0, self::SLUG_MAX_LENGTH + 1);
            $base = str_contains($cut, '-') ? substr($cut, 0, strrpos($cut, '-')) : substr($base, 0, self::SLUG_MAX_LENGTH);
        }
        $base = $base !== '' ? $base : Str::lower(Str::random(8));

        $taken = static::query()
            ->where(fn ($query) => $query->where('slug', $base)->orWhere('slug', 'like', "{$base}-%"))
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->pluck('slug')
            ->all();

        $slug = $base;
        for ($suffix = 2; in_array($slug, $taken, true); $suffix++) {
            $slug = "{$base}-{$suffix}";
        }

        return $slug;
    }

    /**
     * Element id of the post in the gallery. The prefix keeps slugs apart from other ids on the page.
     */
    public function anchor(): string {
        return "post-{$this->slug}";
    }

    /**
     * Gallery page of this published post, newest first like the gallery.
     */
    public function galleryPage(): int {
        $position = static::where('is_published', true)
            ->where('published_at', '>=', $this->published_at)
            ->count();

        return max(1, (int) ceil($position / config('app.posts_per_page')));
    }

    /**
     * Link to the post in the gallery, e.g. /?page=2#post-schoener-tag
     */
    public function galleryUrl(): string {
        return route('dashboard', ['page' => $this->galleryPage()]) . '#' . $this->anchor();
    }

    // Define the relationship with User
    public function user() {
        return $this->belongsTo(User::class);
    }
    // Define the relationship with Image
    public function image() {
        return $this->hasOne(Image::class);
    }

    public function comments() {
        return $this->hasMany(Comment::class);
    }

    // Laravel will handle converting these values to 1 and 0 when saving to the database 
    // and back to true and false when retrieving from the database.
    // protected $casts = [
    //     'is_published' => 'boolean',
    //     'is_sensitive' => 'boolean',
    // ];
}
