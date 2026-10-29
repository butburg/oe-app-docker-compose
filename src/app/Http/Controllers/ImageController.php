<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\ImageVariant;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/**
 * Serves uploaded images behind expiring links (FEAT-011). Runs without the web middleware:
 * no session, no cookies, no DB write per image.
 */
class ImageController extends Controller
{
    public function show(string $variant, string $token): Response
    {
        $variant = ImageVariant::with('image')->firstWhere('public_id', $variant);
        abort_if(!$variant || !$variant->image, 404);

        // Accept the token of the current and the previous window, so a page opened
        // shortly before the window changes still loads its lazy images
        $current = Image::currentWindow();
        $window = collect([$current, $current - 1])->first(
            fn (int $window) => hash_equals(Image::token($variant->id, $variant->image->token_version, $window), $token)
        );
        abort_if($window === null, 404);

        // The browser may keep the image until the link expires, not longer
        $maxAge = ($window + 2) * Image::windowSeconds() - now()->getTimestamp();
        $headers = [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => "private, max-age={$maxAge}",
        ];

        $disk = Storage::disk('public');
        abort_unless($disk->exists($variant->path), 404);

        // file() marks the response as public by default, a proxy must not keep the image
        return response()->file($disk->path($variant->path), $headers)->setPrivate();
    }
}
