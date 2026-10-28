<?php

namespace Database\Seeders;

use App\Actions\CreateImageVariants;
use App\Enums\ImageSizeType;
use App\Models\Comment;
use App\Models\Image;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Static demo data: three users, 31 curated CC0 images from data/posts.json and profile images from data/profiles.json.
 * Sources and licenses of the images are listed in those files as well.
 */
class DatabaseSeeder extends Seeder
{
    private const COMMENTS = [
        'Wow, the colours!', 'Love this one.', 'Where was this taken?', 'Great light here.',
        'This is my new wallpaper.', 'So calm.', 'Beautiful composition.', 'I want to go there.',
        'The details are amazing.', 'Nice shot!', 'That perspective though.', 'Made my day.',
        'How long did you wait for this?', 'Stunning.', 'Looks like a painting.', 'Pure magic.',
        'I can almost hear it.', 'More of these please.', 'Saved!', 'The contrast is perfect.',
        'Simple and beautiful.', 'Reminds me of my holidays.', 'What camera did you use?',
        'This deserves more attention.', 'Incredible textures.', 'Such a mood.', 'Haha, love it.',
        'Perfect timing.', 'Never seen this before.', 'Wonderful.', 'Thanks for sharing!',
    ];

    public function run(): void
    {
        $users = [
            'admin' => User::factory()->create([
                'name' => 'Test-Admin User',
                'email' => 'admin@user.com',
                'password' => bcrypt('admin'),
                'usertype' => 'admin',
                'previous_name' => null,
            ]),
            'none' => User::factory()->create([
                'name' => 'Test-User ohne Bilder',
                'email' => 'user-ohne-bilder@user.com',
                'previous_name' => null,
            ]),
            'many' => User::factory()->create([
                'name' => 'Test-User mit ganz vielen Bildern',
                'email' => 'user-viele-bilder@user.com',
                'previous_name' => null,
            ]),
        ];
        $commenters = array_values($users);
        $sizes = array_map(fn ($size) => $size->value, [
            ImageSizeType::SMALL, ImageSizeType::MEDIUM, ImageSizeType::LARGE, ImageSizeType::EXTRA_LARGE,
        ]);

        // profile images in the sizes the profile upload creates
        $profileSizes = array_map(fn ($size) => $size->value, [
            ImageSizeType::EXTRA_SMALL, ImageSizeType::SMALL, ImageSizeType::LARGE,
        ]);
        foreach (json_decode(file_get_contents(__DIR__ . '/data/profiles.json'), true) as $entry) {
            $this->storeImage(['user_id' => $users[$entry['owner']]->id], 'profiles', $entry['file'], $profileSizes, 'profiles/');
        }

        $entries = json_decode(file_get_contents(__DIR__ . '/data/posts.json'), true);
        $commentIndex = 0;

        foreach ($entries as $index => $entry) {
            $owner = $users[$entry['owner']];
            // spread the dates over the past months, newest first like the gallery
            $date = now()->subDays(($index + 1) * 5);

            $post = Post::create([
                'user_id' => $owner->id,
                'username' => $owner->name,
                'title' => $entry['title'],
                'is_published' => $entry['published'],
                'is_sensitive' => $entry['sensitive'],
                'published_at' => $entry['published'] ? $date : null,
            ]);
            $post->timestamps = false;
            $post->forceFill(['created_at' => $date, 'updated_at' => $date])->save();

            $this->storeImage(['post_id' => $post->id], 'posts', $entry['file'], $sizes, 'posts/images/');

            for ($i = 0; $i < $entry['comments']; $i++) {
                Comment::create([
                    'post_id' => $post->id,
                    'user_id' => $commenters[$commentIndex % count($commenters)]->id,
                    'comment' => self::COMMENTS[$commentIndex % count(self::COMMENTS)],
                ]);
                $commentIndex++;
            }
        }
    }

    /**
     * Store a file from data/{folder} as image with variants, in the same storage path as an upload.
     */
    private function storeImage(array $owner, string $folder, string $filename, array $sizes, string $path): void
    {
        $file = new SplFileInfo(__DIR__ . "/data/{$folder}/{$filename}", '', $filename);
        $image = Image::create([...$owner, 'upload_size' => $file->getSize()]);
        app(CreateImageVariants::class)->handleVariant($image, $file, $sizes, $path);
    }
}
