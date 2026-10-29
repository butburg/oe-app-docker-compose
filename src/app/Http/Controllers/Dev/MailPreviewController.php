<?php

namespace App\Http\Controllers\Dev;

use App\Http\Controllers\Controller;
use App\Mail\AccountAlreadyExists;
use App\Mail\AdminMessage;
use App\Mail\ConfirmNewEmail;
use App\Mail\ConfirmRegistration;
use App\Mail\MailAssets;
use App\Mail\MailBundle;
use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Str;

/**
 * Renders every mail with sample data in the browser (FEAT-016). Only registered in the local environment.
 */
class MailPreviewController extends Controller
{
    /**
     * A bundle with sample data that is not saved, so the preview never changes the database.
     */
    private function bundle(bool $announcement, bool $comments, bool $uploads): MailBundle
    {
        $user = new User(['name' => 'Anna', 'email' => 'anna@example.test']);
        $url = route('dashboard', ['page' => 1]) . '#post-sunset-over-the-harbour';

        return new MailBundle(
            $user,
            $announcement ? [$this->announcement()] : [],
            $comments ? [
                ['title' => 'Sunset over the harbour', 'url' => $url, 'comments' => [
                    ['name' => 'Ben', 'text' => 'What a light! Was this taken in the evening? The colours in the water look *amazing*, I want to go there too.'],
                    ['name' => 'Carla', 'text' => 'Wow, love it!'],
                ]],
                ['title' => 'Fox in the snow', 'url' => $url, 'comments' => [['name' => 'Ben', 'text' => 'So cute.']]],
            ] : [],
            $uploads ? [
                ['name' => 'Ben', 'titles' => ['Old stone bridge', 'Morning fog', 'Tulips', 'City at night', 'Cactus flower', 'Waves',
                    'Train station', 'Aurora', 'Desert dunes', 'Castle on the hill', 'Harbour lights', 'Red kite']],
                ['name' => 'Carla', 'titles' => ['Rainy window']],
            ] : [],
        );
    }

    private function announcement(): AdminMessage
    {
        return new AdminMessage('Maintenance on Sunday', "# Hi {name},\n\nthe gallery is **offline from 8 to 9 am** while we update the server.\n\n- Your images and comments stay as they are.\n- Please log in again afterwards.\n\nYou have {posts} images and {comments} comments, all safe. See you on {website}!", ['{name}' => 'Anna', '{email}' => 'anna@example.test', '{posts}' => '12', '{comments}' => '34']);
    }

    /**
     * @return array<string, callable(): Mailable>
     */
    private function mails(): array
    {
        return [
            'confirm-registration' => fn () => new ConfirmRegistration('Anna', url('/register/confirm/' . Str::random(40))),
            'confirm-new-email' => fn () => new ConfirmNewEmail('Anna', url('/profile/email/confirm/1/' . sha1('x')), User::EMAIL_CHANGE_VALID_MINUTES),
            'account-already-exists' => fn () => new AccountAlreadyExists(),
            'bundle-comments' => fn () => $this->bundle(false, true, false),
            'bundle-new-images' => fn () => $this->bundle(false, false, true),
            'bundle-announcement' => fn () => $this->bundle(true, false, false),
            'bundle-everything' => fn () => $this->bundle(true, true, true),
            'admin-message' => fn () => $this->announcement(),
        ];
    }

    public function index()
    {
        $links = collect(array_keys($this->mails()))
            ->map(fn ($name) => '<li><a href="' . route('dev.mail', $name) . '">' . $name . '</a></li>')
            ->implode('');

        return response("<h1>Mail previews</h1><ul>{$links}</ul>");
    }

    public function show(string $mail)
    {
        abort_unless(isset($this->mails()[$mail]), 404);

        return response(MailAssets::forBrowser($this->mails()[$mail]()->render()));
    }
}
