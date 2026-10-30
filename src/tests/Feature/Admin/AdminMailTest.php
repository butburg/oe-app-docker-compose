<?php

namespace Tests\Feature\Admin;

use App\Jobs\SendMailBundle;
use App\Mail\AdminMessage;
use App\Mail\MailBundle;
use App\Models\AdminMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\Feature\Mail\RunsMailBundles;
use Tests\TestCase;

class AdminMailTest extends TestCase
{
    use RefreshDatabase, RunsMailBundles;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['usertype' => 'admin', 'email' => 'admin@example.test']);
        Queue::fake();
        Mail::fake();
    }

    private function mailData(array $overrides = []): array
    {
        return array_merge([
            'subject' => 'Maintenance',
            'body' => "# Hello\n\nThe gallery is **offline** on Sunday.",
            'group' => 'verified',
            'send_token' => (string) Str::uuid(),
        ], $overrides);
    }

    public function test_non_admins_and_guests_cannot_open_the_page_or_send(): void
    {
        $user = User::factory()->create();

        $this->get(route('admin.mails.index'))->assertRedirect(route('login'));
        $this->actingAs($user)->get(route('admin.mails.index'))->assertRedirect('/');
        $this->actingAs($user)->post(route('admin.mails.send'), $this->mailData())->assertRedirect('/');

        $this->assertDatabaseCount('pending_mails', 0);
        $this->assertDatabaseCount('admin_mails', 0);
    }

    public function test_page_shows_the_recipient_counts(): void
    {
        User::factory()->count(2)->create();
        User::factory()->unverified()->create();

        $this->actingAs($this->admin)->get(route('admin.mails.index'))
            ->assertOk()
            ->assertSee('All verified users')
            ->assertSee('(3 users)')
            ->assertSee('All users including unverified')
            ->assertSee('(4 users)')
            ->assertDontSee('Admins only')
            ->assertSee('{name}')
            ->assertSee('{comments}')
            ->assertSee('{website}');
    }

    public function test_preview_renders_the_mail_and_the_recipient_count(): void
    {
        User::factory()->count(2)->create();

        $this->actingAs($this->admin)->post(route('admin.mails.preview'), $this->mailData())
            ->assertOk()
            ->assertSee('Send to 3 users')
            ->assertSee('offline', false)
            ->assertSee('name="send_token"', false);
    }

    public function test_all_verified_users_get_it_in_their_own_bundle_also_with_notifications_off(): void
    {
        $users = User::factory()->count(2)->create();
        $unverified = User::factory()->unverified()->create();
        // notification switches do not apply to announcements
        $quiet = User::factory()->create();
        $quiet->forceFill(array_fill_keys(array_keys(User::NOTIFICATION_SETTINGS), false))->save();

        $this->actingAs($this->admin)->post(route('admin.mails.send'), $this->mailData())
            ->assertRedirect(route('admin.mails.index'))
            ->assertSessionHas('notif.success', 'Mail goes to 4 users with their next mail bundle, at the latest in 60 minutes.');
        Mail::assertNothingSent();
        $this->sendBundles();

        foreach ([...$users, $quiet, $this->admin] as $user) {
            Mail::assertSent(MailBundle::class, fn ($mail) => $mail->hasTo($user->email) && count($mail->to) === 1
                && $mail->envelope()->subject === 'Maintenance');
        }
        Mail::assertNotSent(MailBundle::class, fn ($mail) => $mail->hasTo($unverified->email));
        Mail::assertSentCount(4);
        $this->assertDatabaseHas('admin_mails', ['subject' => 'Maintenance', 'group' => 'verified', 'recipient_count' => 4, 'user_id' => $this->admin->id]);
    }

    public function test_everyone_group_includes_unverified_users_and_single_user_can_be_unverified(): void
    {
        $verified = User::factory()->create();
        $unverified = User::factory()->unverified()->create(['email' => 'old@example.test']);

        $this->actingAs($this->admin)->post(route('admin.mails.send'), $this->mailData(['group' => 'everyone']));
        $this->sendBundles();
        Mail::assertSentCount(3);
        Mail::assertSent(MailBundle::class, fn ($mail) => $mail->hasTo($unverified->email));
        Mail::assertSent(MailBundle::class, fn ($mail) => $mail->hasTo($verified->email));

        $this->actingAs($this->admin)->post(route('admin.mails.send'), $this->mailData(['group' => 'user', 'recipient' => 'old@example.test']));
        $this->sendBundles();
        Mail::assertSentCount(4);
        $this->assertDatabaseHas('admin_mails', ['group' => 'user', 'recipient_email' => 'old@example.test', 'recipient_count' => 1]);
    }

    public function test_admins_group_does_not_exist_anymore(): void
    {
        $this->actingAs($this->admin)->post(route('admin.mails.send'), $this->mailData(['group' => 'admins']))
            ->assertSessionHasErrors('group');
    }

    public function test_placeholders_are_filled_per_recipient_and_escaped(): void
    {
        $anna = User::factory()->create(['name' => 'Anna <b>', 'email' => 'anna@example.test']);
        \App\Models\Post::factory()->count(2)->forUser($anna)->create();
        \App\Models\Comment::create(['user_id' => $anna->id, 'post_id' => \App\Models\Post::first()->id, 'comment' => 'Nice']);

        $this->actingAs($this->admin)->post(route('admin.mails.send'), $this->mailData([
            'group' => 'user', 'recipient' => 'anna@example.test',
            'subject' => 'News for {name}',
            'body' => "Hi **{name}** ({email}), you have {posts} images and {comments} comments.",
        ]));
        $this->sendBundles();

        Mail::assertSent(MailBundle::class, function (MailBundle $mail) {
            $html = $mail->render();

            return $mail->envelope()->subject === 'News for Anna <b>'
                && str_contains($html, '<strong>Anna &lt;b&gt;</strong> (anna@example.test), you have 2 images and 1 comments.')
                && !str_contains($html, '{name}');
        });
    }

    public function test_website_placeholder_becomes_a_link(): void
    {
        config(['app.url' => 'https://gallery.example.test']);
        $mail = new AdminMessage('See {website}', "Visit {website} or [the gallery]({website}).");
        $html = $mail->render();

        $this->assertSame('See https://gallery.example.test', $mail->envelope()->subject);
        $this->assertStringContainsString('<a href="https://gallery.example.test"', $html);
        $this->assertStringContainsString('>the gallery</a>', $html);
        $this->assertStringNotContainsString('{website}', $html);
    }

    public function test_preview_fills_placeholders_with_the_admins_data(): void
    {
        $this->actingAs($this->admin)->post(route('admin.mails.preview'), $this->mailData(['body' => 'Hello {email}']))
            ->assertOk()
            ->assertSee('Hello admin@example.test')
            ->assertSee('Placeholders are filled with your own data');
    }

    public function test_single_user_must_exist(): void
    {

        $this->actingAs($this->admin)->post(route('admin.mails.send'), $this->mailData(['group' => 'user', 'recipient' => 'nobody@example.test']))
            ->assertSessionHasErrors('recipient');
        $this->actingAs($this->admin)->post(route('admin.mails.send'), $this->mailData(['group' => 'user']))
            ->assertSessionHasErrors('recipient');

        $this->assertDatabaseCount('pending_mails', 0);
    }

    public function test_test_mail_goes_only_to_the_admin(): void
    {
        User::factory()->count(3)->create();

        $this->actingAs($this->admin)->post(route('admin.mails.preview'), $this->mailData(['action' => 'test']))
            ->assertOk()
            ->assertSee('Test mail sent to admin@example.test');

        Mail::assertSent(AdminMessage::class, fn ($mail) => $mail->hasTo('admin@example.test'));
        Mail::assertSentCount(1);
        Queue::assertNothingPushed();
        $this->assertDatabaseCount('pending_mails', 0);
        $this->assertDatabaseCount('admin_mails', 0);
    }

    public function test_sending_the_same_confirm_page_twice_sends_only_once(): void
    {
        User::factory()->create();
        $data = $this->mailData();

        $this->actingAs($this->admin)->post(route('admin.mails.send'), $data);
        $this->actingAs($this->admin)->post(route('admin.mails.send'), $data)
            ->assertSessionHas('notif.success', 'This mail was already sent.');

        $this->assertDatabaseCount('pending_mails', 2);
        $this->assertDatabaseCount('admin_mails', 1);
    }

    public function test_every_bundle_waits_for_the_bundle_window(): void
    {
        $this->freezeTime();
        User::factory()->count(4)->create();

        $this->actingAs($this->admin)->post(route('admin.mails.send'), $this->mailData());

        // the rate limit is the worker's job (--max-jobs), the jobs are not spread here
        $minutes = Queue::pushed(SendMailBundle::class)->map(fn ($job) => (int) now()->diffInMinutes($job->delay))->countBy()->all();
        $this->assertSame([60 => 5], $minutes);
    }

    public function test_body_is_markdown_with_html_escaped_and_no_settings_link(): void
    {
        $html = (new AdminMessage('Hi', "**Bold** <script>alert(1)</script> <a href=\"https://evil.test\">x</a> [bad](javascript:alert(1))"))->render();

        $this->assertStringContainsString('<strong', $html);
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringContainsString('&lt;a href="https://evil.test"&gt;x&lt;/a&gt;', $html);
        $this->assertStringNotContainsString('<a href="https://evil.test"', $html);
        $this->assertStringNotContainsString('javascript:', $html);
        // same footer as all other mails, no settings link
        $this->assertStringContainsString('You get this mail because you have an account on', $html);
        $this->assertStringNotContainsString('mail settings', $html);
    }

    public function test_sent_mails_are_listed(): void
    {
        AdminMail::create([
            'user_id' => $this->admin->id, 'subject' => 'Old news', 'body' => 'x', 'group' => 'verified',
            'recipient_count' => 7, 'send_token' => (string) Str::uuid(),
        ]);

        $this->actingAs($this->admin)->get(route('admin.mails.index'))
            ->assertSee('Old news')
            ->assertSee('7 users');
    }

    public function test_navigation_shows_users_announcements_and_the_admin_label(): void
    {
        $this->actingAs($this->admin)->get(route('admin.mails.index'))
            ->assertSeeInOrder(['Users', 'Announcements'])
            ->assertSee('<span class="text-c-accent">Admin</span>', false);

        $this->actingAs(User::factory()->create())->get(route('dashboard'))
            ->assertDontSee('Announcements')
            ->assertDontSee('<span class="text-c-accent">Admin</span>', false);
    }
}
