<?php

namespace Tests\Feature\Mail;

use App\Jobs\SendNewImagesMail;
use App\Mail\NewImages;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class NewImagesMailTest extends TestCase
{
    use RefreshDatabase;

    private User $uploader;
    private User $reader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->uploader = User::factory()->create(['name' => 'Anna']);
        $this->reader = User::factory()->create(['email' => 'ben@example.test']);
        Queue::fake();
        Mail::fake();
    }

    private function draft(string $title): Post
    {
        return Post::factory()->forUser($this->uploader)->create([
            'title' => $title, 'is_published' => false, 'published_at' => null, 'is_sensitive' => false,
        ]);
    }

    private function publish(Post $post): void
    {
        $this->actingAs($this->uploader)->get(route('posts.publish', $post->id))->assertRedirect();
    }

    /**
     * Runs the queued jobs whose delay is over, like the worker would.
     */
    private function runDueJobs(): void
    {
        Queue::pushed(SendNewImagesMail::class)
            ->filter(fn ($job) => $job->delay <= now() && !in_array($job, $this->ran, true))
            ->each(function ($job) {
                $this->ran[] = $job;
                app()->call([$job, 'handle']);
            });
    }

    private array $ran = [];

    public function test_three_publications_within_an_hour_give_one_mail_with_three_titles(): void
    {
        $this->publish($this->draft('First'));
        $this->travel(20)->minutes();
        $this->publish($this->draft('Second'));
        $this->travel(20)->minutes();
        $this->publish($this->draft('Third'));

        Queue::assertPushed(SendNewImagesMail::class, 3);
        $this->travel(30)->minutes();
        $this->runDueJobs();
        Mail::assertNothingQueued();

        $this->travel(31)->minutes();
        $this->runDueJobs();

        Mail::assertQueued(NewImages::class, 1);
        Mail::assertQueued(NewImages::class, fn (NewImages $mail) => $mail->hasTo('ben@example.test')
            && $mail->titles === ['First', 'Second', 'Third']
            && $mail->envelope()->subject === 'Anna published 3 new images');
    }

    public function test_publication_after_the_window_gives_a_new_mail(): void
    {
        $this->publish($this->draft('First'));
        $this->travel(61)->minutes();
        $this->runDueJobs();

        $this->publish($this->draft('Later'));
        $this->travel(61)->minutes();
        $this->runDueJobs();

        Mail::assertQueued(NewImages::class, 2);
        Mail::assertQueued(NewImages::class, fn (NewImages $mail) => $mail->titles === ['Later']);
    }

    public function test_uploader_unverified_users_and_switched_off_users_get_no_mail(): void
    {
        $unverified = User::factory()->unverified()->create();
        $quiet = User::factory()->create();
        $quiet->forceFill(['notify_new_images' => false])->save();

        $this->publish($this->draft('First'));
        $this->travel(61)->minutes();
        $this->runDueJobs();

        Mail::assertQueued(NewImages::class, 1);
        foreach ([$this->uploader, $unverified, $quiet] as $user) {
            Mail::assertNotQueued(NewImages::class, fn ($mail) => $mail->hasTo($user->email));
        }
    }

    public function test_republishing_does_not_mail_again(): void
    {
        $post = $this->draft('First');
        $this->publish($post);
        $this->travel(61)->minutes();
        $this->runDueJobs();
        $this->assertNotNull($post->fresh()->notified_at);

        $admin = User::factory()->create(['usertype' => 'admin']);
        $this->actingAs($admin)->get(route('posts.make-draft', $post->id));
        $this->publish($post);

        Queue::assertPushed(SendNewImagesMail::class, 1);
        Mail::assertQueued(NewImages::class, 1);
    }

    public function test_no_mail_when_all_posts_became_drafts_or_were_deleted(): void
    {
        $first = $this->draft('First');
        $second = $this->draft('Second');
        $this->publish($first);
        $this->publish($second);

        $first->fresh()->update(['is_published' => false]);
        $second->fresh()->delete();
        $this->travel(61)->minutes();
        $this->runDueJobs();

        Mail::assertNothingQueued();
    }

    public function test_mail_has_titles_as_text_no_images_and_only_gallery_and_settings_links(): void
    {
        $titles = array_map(fn ($i) => "Image {$i}", range(1, 12));
        $titles[0] = '[Click](https://evil.test) <b>x</b>';

        $html = (new NewImages('Anna **[x](https://evil.test)**', $titles))->render();

        $this->assertStringNotContainsString('<img src="' . url('/img'), $html);
        $this->assertSame(1, substr_count($html, '<img'), 'only the logo');
        preg_match_all('/href="([^"]+)"/', $html, $links);
        $this->assertEqualsCanonicalizing(
            [route('dashboard'), route('profile.notifications')],
            array_values(array_unique($links[1])),
        );
        $this->assertStringContainsString('Image 10', $html);
        $this->assertStringNotContainsString('Image 11', $html);
        $this->assertStringContainsString('and 2 more', $html);
        $this->assertStringContainsString('[Click](https://evil.test)', $html);
        $this->assertStringNotContainsString('<b>', $html);
    }

    public function test_settings_page_saves_the_new_images_switch(): void
    {
        $this->actingAs($this->reader)->get(route('profile.edit'))->assertSee('New images');

        $this->actingAs($this->reader)->patch(route('profile.notifications.update'), ['notify_comments' => '1', 'notify_new_images' => '0']);

        $this->assertFalse($this->reader->fresh()->notify_new_images);
        $this->assertTrue($this->reader->fresh()->notify_comments);
        $this->assertTrue(User::factory()->create()->fresh()->notify_new_images);
    }
}
