<?php

namespace Tests\Feature\Mail;

use App\Jobs\SendNewCommentMail;
use App\Mail\NewComment;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class NewCommentMailTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private User $commenter;
    private Post $post;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['name' => 'Anna', 'email' => 'anna@example.test']);
        $this->commenter = User::factory()->create(['name' => 'Ben']);
        $this->post = Post::factory()->forUser($this->owner)->create([
            'title' => 'Sunset', 'is_published' => true, 'is_sensitive' => false, 'published_at' => now(),
        ]);
    }

    private function comment(User $user, string $text = 'Great light!'): void
    {
        $this->actingAs($user)->post(route('comments.store'), ['post_id' => $this->post->id, 'comment' => $text]);
    }

    public function test_comment_queues_a_job(): void
    {
        Queue::fake();

        $this->comment($this->commenter);

        Queue::assertPushed(SendNewCommentMail::class, fn ($job) => $job->commentId === Comment::first()->id);
    }

    public function test_owner_gets_a_mail_with_name_title_excerpt_and_link(): void
    {
        Mail::fake();

        $this->comment($this->commenter, str_repeat('Lovely colours. ', 30));

        Mail::assertSent(NewComment::class, function (NewComment $mail) {
            $html = $mail->render();

            return $mail->hasTo('anna@example.test')
                && $mail->envelope()->subject === 'Ben commented on your image'
                && str_contains($html, 'Sunset')
                && str_contains($html, 'Lovely colours')
                && str_contains($html, '...')
                && !str_contains($html, str_repeat('Lovely colours. ', 14))
                && str_contains($html, e($this->post->galleryUrl()));
        });
    }

    public function test_no_mail_for_a_comment_on_the_own_post(): void
    {
        Mail::fake();

        $this->comment($this->owner);

        Mail::assertNothingSent();
    }

    public function test_no_mail_when_switched_off(): void
    {
        Mail::fake();
        $this->owner->forceFill(['notify_comments' => false])->save();

        $this->comment($this->commenter);

        Mail::assertNothingSent();
    }

    public function test_no_mail_to_an_unverified_owner(): void
    {
        Mail::fake();
        $this->owner->forceFill(['email_verified_at' => null])->save();

        $this->comment($this->commenter);

        Mail::assertNothingSent();
    }

    public function test_nothing_is_sent_when_the_comment_or_post_was_deleted_before_the_job_runs(): void
    {
        Mail::fake();
        $comment = Comment::create(['user_id' => $this->commenter->id, 'post_id' => $this->post->id, 'comment' => 'x']);
        $job = new SendNewCommentMail($comment->id);

        $comment->delete();
        $job->handle();

        $other = Comment::create(['user_id' => $this->commenter->id, 'post_id' => $this->post->id, 'comment' => 'y']);
        $job = new SendNewCommentMail($other->id);
        $this->post->delete();
        $job->handle();

        Mail::assertNothingSent();
    }

    public function test_comment_text_cannot_add_links_or_html_to_the_mail(): void
    {
        $comment = Comment::create([
            'user_id' => $this->commenter->id, 'post_id' => $this->post->id,
            'comment' => '[Click here](https://evil.test) <b>bold</b> **strong**',
        ]);

        $html = (new NewComment($comment))->render();

        $this->assertStringNotContainsString('href="https://evil.test"', $html);
        $this->assertStringNotContainsString('<b>', $html);
        $this->assertStringNotContainsString('<strong>strong</strong>', $html);
        $this->assertStringContainsString('[Click here](https://evil.test)', $html);
    }

    public function test_mail_has_the_settings_link_and_list_unsubscribe_header(): void
    {
        $comment = Comment::create(['user_id' => $this->commenter->id, 'post_id' => $this->post->id, 'comment' => 'Nice']);
        Mail::to($this->owner)->send(new NewComment($comment));
        $message = Mail::mailer()->getSymfonyTransport()->messages()->last()->getOriginalMessage();

        $this->assertStringContainsString('href="' . route('profile.notifications') . '"', $message->getHtmlBody());
        $this->assertStringContainsString('Change mail settings', $message->getHtmlBody());
        $this->assertSame('<' . route('profile.notifications') . '>', $message->getHeaders()->get('List-Unsubscribe')->getBodyAsString());
    }

    public function test_settings_page_shows_and_saves_the_switch(): void
    {
        $this->actingAs($this->owner)->get(route('profile.edit'))
            ->assertSee('Mail notifications')
            ->assertSee('Comments on my images');

        $this->actingAs($this->owner)->patch(route('profile.notifications.update'), ['notify_comments' => '0'])
            ->assertRedirect(route('profile.edit') . '#notifications');
        $this->assertFalse($this->owner->fresh()->notify_comments);

        $this->actingAs($this->owner)->patch(route('profile.notifications.update'), ['notify_comments' => '1']);
        $this->assertTrue($this->owner->fresh()->notify_comments);
    }

    public function test_new_users_have_the_switch_on(): void
    {
        $this->assertTrue(User::factory()->create()->fresh()->notify_comments);
    }

    public function test_guest_is_sent_to_login_and_back_to_the_settings(): void
    {
        $this->get(route('profile.notifications'))->assertRedirect(route('login'));

        $this->post(route('login'), ['email' => 'anna@example.test', 'password' => 'password'])
            ->assertRedirect(route('profile.notifications'));

        $this->get(route('profile.notifications'))->assertRedirect(route('profile.edit') . '#notifications');
    }
}
