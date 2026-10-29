<?php

namespace App\Http\Controllers\Dev;

use App\Http\Controllers\Controller;
use App\Mail\AccountAlreadyExists;
use App\Mail\AdminMessage;
use App\Mail\ConfirmNewEmail;
use App\Mail\ConfirmRegistration;
use App\Mail\NewComment;
use App\Mail\NewImages;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Str;

/**
 * Renders every mail with sample data in the browser (FEAT-016). Only registered in the local environment.
 */
class MailPreviewController extends Controller
{
    /**
     * A comment with post and users that are not saved, so the preview never changes the database.
     */
    private function sampleComment(): Comment
    {
        $owner = new User(['name' => 'Anna']);
        $post = new Post(['title' => 'Sunset over the harbour', 'is_published' => false]);
        $post->setRelation('user', $owner);
        $comment = new Comment(['comment' => 'What a light! Was this taken in the evening? The colours in the water look *amazing*, I want to go there too.']);
        $comment->setRelation('user', new User(['name' => 'Ben']));
        $comment->setRelation('post', $post);

        return $comment;
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
            'new-comment' => fn () => new NewComment($this->sampleComment()),
            'new-images' => fn () => new NewImages('Anna', [
                'Sunset over the harbour', 'Fox in the snow', 'Old stone bridge', 'Morning fog', 'Tulips', 'City at night',
                'Cactus flower', 'Waves', 'Train station', 'Aurora', 'Desert dunes', 'Castle on the hill',
            ]),
            'admin-message' => fn () => new AdminMessage('Maintenance on Sunday', "# Hi {name},\n\nthe gallery is **offline from 8 to 9 am** while we update the server.\n\n- Your images and comments stay as they are.\n- Please log in again afterwards.\n\nYou have {posts} images and {comments} comments, all safe. See you on {website}!", ['{name}' => 'Anna', '{email}' => 'anna@example.test', '{posts}' => '12', '{comments}' => '34']),
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

        return $this->mails()[$mail]();
    }
}
