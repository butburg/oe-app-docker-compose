<?php

namespace Tests\Feature\Mail;

use App\Jobs\SendMailBundle;
use App\Mail\MailBundle;
use App\Models\AdminMail;
use App\Models\Comment;
use App\Models\PendingMail;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class MailBundleTest extends TestCase
{
    use RefreshDatabase, RunsMailBundles;

    private User $anna;
    private User $ben;
    private Post $post;

    protected function setUp(): void
    {
        parent::setUp();

        $this->anna = User::factory()->create(['name' => 'Anna', 'email' => 'anna@example.test']);
        $this->ben = User::factory()->create(['name' => 'Ben', 'email' => 'ben@example.test']);
        $this->post = Post::factory()->forUser($this->anna)->create([
            'title' => 'Sunset', 'is_published' => true, 'is_sensitive' => false, 'published_at' => now(), 'notified_at' => now(),
        ]);
        Queue::fake();
        Mail::fake();
    }

    private function comment(User $user, string $text = 'Great light!', ?Post $post = null): void
    {
        $this->actingAs($user)->post(route('comments.store'), ['post_id' => ($post ?? $this->post)->id, 'comment' => $text]);
    }

    private function draft(User $user, string $title): Post
    {
        return Post::factory()->forUser($user)->create([
            'title' => $title, 'is_published' => false, 'published_at' => null, 'is_sensitive' => false,
        ]);
    }

    private function publish(Post $post): void
    {
        $this->actingAs($post->user)->get(route('posts.publish', $post->id))->assertRedirect();
    }

    private function announce(string $subject, string $body): void
    {
        $admin = User::factory()->create(['usertype' => 'admin']);
        $this->actingAs($admin)->post(route('admin.mails.send'), [
            'subject' => $subject, 'body' => $body, 'group' => 'verified', 'send_token' => (string) Str::uuid(),
        ]);
    }

    private function sentTo(User $user): \Illuminate\Support\Collection
    {
        return Mail::sent(MailBundle::class, fn (MailBundle $mail) => $mail->hasTo($user->email));
    }

    // --- collecting and the window ---

    public function test_first_item_starts_the_window_and_everything_until_then_goes_out_in_one_mail(): void
    {
        $this->comment($this->ben, 'First');
        $this->travel(20)->minutes();
        $this->comment($this->ben, 'Second');
        $this->publish($this->draft($this->ben, 'Fox in the snow'));

        Queue::assertPushed(SendMailBundle::class, 1);
        $this->travel(39)->minutes();
        $this->runDueBundles();
        Mail::assertNothingSent();

        $this->travel(2)->minutes();
        $this->runDueBundles();

        $this->assertCount(1, $this->sentTo($this->anna));
        $mail = $this->sentTo($this->anna)->first();
        $html = $mail->render();
        $this->assertStringContainsString('First', $html);
        $this->assertStringContainsString('Second', $html);
        $this->assertStringContainsString('Fox in the snow', $html);
        $this->assertSame('2 new comments on your images · Ben published 1 new image', $mail->envelope()->subject);
        $this->assertDatabaseCount('pending_mails', 0);
    }

    public function test_items_after_a_sent_bundle_start_a_new_window(): void
    {
        $this->comment($this->ben, 'First');
        $this->sendBundles();
        $this->comment($this->ben, 'Later');
        $this->runDueBundles();
        $this->assertCount(1, $this->sentTo($this->anna));

        $this->sendBundles();

        $this->assertCount(2, $this->sentTo($this->anna));
        $this->assertSame([[['name' => 'Ben', 'text' => 'Later']]], array_column($this->sentTo($this->anna)->last()->comments, 'comments'));
    }

    public function test_a_job_that_runs_too_early_waits_for_the_oldest_item(): void
    {
        $this->comment($this->ben);
        (new SendMailBundle($this->anna->id))->handle();

        Mail::assertNothingSent();
        $this->sendBundles();
        $this->assertCount(1, $this->sentTo($this->anna));
    }

    public function test_items_that_arrive_while_sending_get_their_own_job(): void
    {
        $this->comment($this->ben, 'First');
        $this->travel(61)->minutes();
        // an item that came in after the job read the items, but before it finished
        Mail::shouldReceive('to')->andReturnUsing(function () {
            PendingMail::insert(['user_id' => $this->anna->id, 'type' => PendingMail::COMMENT, 'comment_id' => Comment::first()->id, 'created_at' => now()]);
            return new class { public function send() {} };
        });
        $this->runDueBundles();

        $this->assertDatabaseCount('pending_mails', 1);
        $this->assertTrue(Queue::pushed(SendMailBundle::class)->contains(fn ($job) => $job->delay > now()));
    }

    public function test_a_lost_job_does_not_block_the_user_the_next_item_starts_a_new_one(): void
    {
        $this->comment($this->ben, 'First');
        // the job is gone: failed for good or the jobs table was emptied
        Queue::fake();
        $this->travel(SendMailBundle::minutes() + SendMailBundle::GRACE_MINUTES + 1)->minutes();

        $this->comment($this->ben, 'Second');
        Queue::assertPushed(SendMailBundle::class, 1);
        $this->sendBundles();

        $this->assertCount(1, $this->sentTo($this->anna));
        $this->assertSame(['First', 'Second'], array_column($this->sentTo($this->anna)->first()->comments[0]['comments'], 'text'));
        $this->assertDatabaseCount('pending_mails', 0);
    }

    // --- comments ---

    public function test_comment_mail_has_name_title_excerpt_and_link(): void
    {
        $this->comment($this->ben, str_repeat('Lovely colours. ', 30));
        $this->sendBundles();

        $mail = $this->sentTo($this->anna)->first();
        $html = $mail->render();
        $this->assertSame('Ben commented on your image', $mail->envelope()->subject);
        $this->assertStringContainsString('Sunset', $html);
        $this->assertStringContainsString('Lovely colours', $html);
        $this->assertStringContainsString('...', $html);
        $this->assertStringNotContainsString(str_repeat('Lovely colours. ', 14), $html);
        $this->assertStringContainsString(e($this->post->galleryUrl()), $html);
    }

    public function test_no_comment_mail_for_own_post_switched_off_or_unverified_owner(): void
    {
        $this->comment($this->anna);
        $this->assertDatabaseCount('pending_mails', 0);

        $this->anna->forceFill(['notify_comments' => false])->save();
        $this->comment($this->ben);
        $this->assertDatabaseCount('pending_mails', 0);

        $this->anna->forceFill(['notify_comments' => true, 'email_verified_at' => null])->save();
        $this->comment($this->ben);
        $this->assertDatabaseCount('pending_mails', 0);
    }

    public function test_settings_and_deletions_are_checked_again_when_sending(): void
    {
        $this->comment($this->ben, 'Deleted later');
        $this->comment($this->ben, 'Switched off later');
        Comment::where('comment', 'Deleted later')->delete();
        $this->anna->forceFill(['notify_comments' => false])->save();

        $this->sendBundles();

        Mail::assertNothingSent();
        $this->assertDatabaseCount('pending_mails', 0);
    }

    public function test_comment_text_cannot_add_links_or_html(): void
    {
        $this->comment($this->ben, '[Click here](https://evil.test) <b>bold</b> **strong**');
        $this->sendBundles();

        $html = $this->sentTo($this->anna)->first()->render();
        $this->assertStringNotContainsString('href="https://evil.test"', $html);
        $this->assertStringNotContainsString('<b>', $html);
        $this->assertStringNotContainsString('<strong>strong</strong>', $html);
        $this->assertStringContainsString('[Click here](https://evil.test)', $html);
    }

    // --- new images ---

    public function test_new_images_go_to_other_verified_users_with_the_switch_on(): void
    {
        $unverified = User::factory()->unverified()->create();
        $quiet = User::factory()->create();
        $quiet->forceFill(['notify_new_images' => false])->save();

        $this->publish($this->draft($this->anna, 'First'));
        $this->publish($this->draft($this->anna, 'Second'));
        $this->sendBundles();

        Mail::assertSentCount(1);
        $mail = $this->sentTo($this->ben)->first();
        $this->assertSame([['name' => 'Anna', 'titles' => ['First', 'Second']]], $mail->uploads);
        $this->assertSame('Anna published 2 new images', $mail->envelope()->subject);
        foreach ([$this->anna, $unverified, $quiet] as $user) {
            $this->assertCount(0, $this->sentTo($user));
        }
    }

    public function test_images_of_several_uploaders_in_one_mail(): void
    {
        $carla = User::factory()->create(['name' => 'Carla']);
        $this->publish($this->draft($this->anna, 'Sea'));
        $this->publish($this->draft($carla, 'Hill'));
        $this->sendBundles();

        $mail = $this->sentTo($this->ben)->first();
        $this->assertSame('2 new images from Anna and Carla', $mail->envelope()->subject);
        $this->assertStringContainsString('Carla</strong> just uploaded a new image', $mail->render());
    }

    public function test_republishing_does_not_mail_again_and_drafts_or_deleted_posts_drop_out(): void
    {
        $post = $this->draft($this->anna, 'First');
        $this->publish($post);
        $this->sendBundles();
        $this->assertNotNull($post->fresh()->notified_at);

        $admin = User::factory()->create(['usertype' => 'admin']);
        $this->actingAs($admin)->get(route('posts.make-draft', $post->id));
        $this->publish($post);
        $this->assertDatabaseCount('pending_mails', 0);

        $second = $this->draft($this->anna, 'Second');
        $third = $this->draft($this->anna, 'Third');
        $this->publish($second);
        $this->publish($third);
        $second->fresh()->update(['is_published' => false]);
        $third->fresh()->delete();
        $this->sendBundles();

        Mail::assertSentCount(1);
    }

    public function test_mail_has_titles_as_text_no_images_and_only_gallery_and_settings_links(): void
    {
        $titles = array_map(fn ($i) => "Image {$i}", range(1, 12));
        $titles[0] = '[Click](https://evil.test) <b>x</b>';

        $html = (new MailBundle($this->ben, [], [], [['name' => 'Anna **[x](https://evil.test)**', 'titles' => $titles]]))->render();

        $this->assertStringNotContainsString('<img src="' . url('/img'), $html);
        $this->assertSame(1, substr_count($html, '<img'), 'only the logo');
        preg_match_all('/<a href="([^"]+)"/', $html, $links);
        $this->assertEqualsCanonicalizing([route('dashboard'), route('profile.notifications')], array_values(array_unique($links[1])));
        $this->assertStringContainsString('Image 10', $html);
        $this->assertStringNotContainsString('Image 11', $html);
        $this->assertStringContainsString('and 2 more', $html);
        $this->assertStringContainsString('[Click](https://evil.test)', $html);
        $this->assertStringNotContainsString('<b>', $html);
    }

    // --- announcements and everything together ---

    public function test_announcement_comment_and_images_in_one_mail(): void
    {
        $this->comment($this->ben, 'Nice one');
        $this->publish($this->draft($this->ben, 'Fox'));
        $this->announce('Maintenance on Sunday', "Hi **{name}**, we update the server.");
        $this->sendBundles();

        $this->assertCount(1, $this->sentTo($this->anna));
        $mail = $this->sentTo($this->anna)->first();
        $html = $mail->render();
        $this->assertSame('Maintenance on Sunday · Ben commented on your image · Ben published 1 new image', $mail->envelope()->subject);
        $this->assertStringContainsString('Hi <strong>Anna</strong>, we update the server.', $html);
        $this->assertStringContainsString('Nice one', $html);
        $this->assertStringContainsString('Fox', $html);
        $this->assertStringContainsString('mails about comments and new images are switched on', $html);
    }

    public function test_announcement_only_has_no_settings_link_button_or_unsubscribe_header(): void
    {
        $this->anna->forceFill(array_fill_keys(array_keys(User::NOTIFICATION_SETTINGS), false))->save();
        $this->announce('Maintenance', 'Offline on Sunday.');
        $this->sendBundles();

        $mail = $this->sentTo($this->anna)->first();
        $html = $mail->render();
        $this->assertSame('Maintenance', $mail->envelope()->subject);
        $this->assertStringNotContainsString('mail settings', $html);
        $this->assertStringNotContainsString('Open the gallery', $html);
        $this->assertSame([], $mail->headers()->text);
    }

    public function test_deleting_the_announcement_or_the_user_removes_their_items(): void
    {
        $this->announce('Oops', 'Wrong text');
        AdminMail::first()->delete();
        $this->comment($this->ben);
        $this->anna->delete();

        $this->assertDatabaseCount('pending_mails', 0);
    }

    public function test_bundle_with_notifications_has_settings_link_and_list_unsubscribe_header(): void
    {
        $mail = new MailBundle($this->anna, [], [['title' => 'Sunset', 'url' => route('dashboard'), 'comments' => [['name' => 'Ben', 'text' => 'Nice']]]], []);

        $this->assertStringContainsString('href="' . route('profile.notifications') . '"', $mail->render());
        $this->assertStringContainsString('Change your mail settings', $mail->render());
        $this->assertSame(['List-Unsubscribe' => '<' . route('profile.notifications') . '>'], $mail->headers()->text);
    }

    // --- settings page ---

    public function test_settings_page_shows_and_saves_the_switches(): void
    {
        $this->actingAs($this->anna)->get(route('profile.edit'))
            ->assertSee('Mail notifications')
            ->assertSee('Comments on my images')
            ->assertSee('New images')
            ->assertSee('at most one mail per 60 minutes');

        $this->actingAs($this->anna)->patch(route('profile.notifications.update'), ['notify_comments' => '0', 'notify_new_images' => '1'])
            ->assertRedirect(route('profile.edit') . '#notifications');
        $this->assertFalse($this->anna->fresh()->notify_comments);
        $this->assertTrue($this->anna->fresh()->notify_new_images);

        $fresh = User::factory()->create()->fresh();
        $this->assertTrue($fresh->notify_comments);
        $this->assertTrue($fresh->notify_new_images);
    }

    public function test_guest_is_sent_to_login_and_back_to_the_settings(): void
    {
        $this->get(route('profile.notifications'))->assertRedirect(route('login'));

        $this->post(route('login'), ['email' => 'anna@example.test', 'password' => 'password'])
            ->assertRedirect(route('profile.notifications'));

        $this->get(route('profile.notifications'))->assertRedirect(route('profile.edit') . '#notifications');
    }
}
