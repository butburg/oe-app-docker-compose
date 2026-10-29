<?php

namespace Tests\Feature\Admin;

use App\Mail\AdminMessage;
use App\Models\AdminMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminMailTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['usertype' => 'admin', 'email' => 'admin@example.test']);
    }

    private function mailData(array $overrides = []): array
    {
        return array_merge([
            'subject' => 'Maintenance',
            'body' => "# Hello\n\nThe gallery is **offline** on Sunday.",
            'group' => 'all',
            'send_token' => (string) Str::uuid(),
        ], $overrides);
    }

    public function test_non_admins_and_guests_cannot_open_the_page_or_send(): void
    {
        $user = User::factory()->create();
        Mail::fake();

        $this->get(route('admin.mails.index'))->assertRedirect(route('login'));
        $this->actingAs($user)->get(route('admin.mails.index'))->assertRedirect('/');
        $this->actingAs($user)->post(route('admin.mails.send'), $this->mailData())->assertRedirect('/');

        Mail::assertNothingQueued();
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
            ->assertSee('(1 user)');
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

    public function test_all_verified_users_get_their_own_queued_mail_also_with_notifications_off(): void
    {
        Mail::fake();
        $users = User::factory()->count(2)->create();
        $unverified = User::factory()->unverified()->create();
        // notification switches do not apply to announcements (columns come with FEAT-018/019)
        $quiet = User::factory()->create();
        if (\Schema::hasColumn('users', 'notify_comments')) {
            $quiet->forceFill(['notify_comments' => false, 'notify_new_images' => false])->save();
        }

        $this->actingAs($this->admin)->post(route('admin.mails.send'), $this->mailData())
            ->assertRedirect(route('admin.mails.index'));

        foreach ([...$users, $quiet, $this->admin] as $user) {
            Mail::assertQueued(AdminMessage::class, fn ($mail) => $mail->hasTo($user->email) && count($mail->to) === 1);
        }
        Mail::assertNotQueued(AdminMessage::class, fn ($mail) => $mail->hasTo($unverified->email));
        Mail::assertQueuedCount(4);
        $this->assertDatabaseHas('admin_mails', ['subject' => 'Maintenance', 'group' => 'all', 'recipient_count' => 4, 'user_id' => $this->admin->id]);
    }

    public function test_admins_group_and_single_user(): void
    {
        Mail::fake();
        $user = User::factory()->create(['email' => 'anna@example.test']);
        $otherAdmin = User::factory()->create(['usertype' => 'admin']);

        $this->actingAs($this->admin)->post(route('admin.mails.send'), $this->mailData(['group' => 'admins']));
        Mail::assertQueuedCount(2);
        Mail::assertQueued(AdminMessage::class, fn ($mail) => $mail->hasTo($otherAdmin->email));
        Mail::assertNotQueued(AdminMessage::class, fn ($mail) => $mail->hasTo($user->email));

        $this->actingAs($this->admin)->post(route('admin.mails.send'), $this->mailData(['group' => 'user', 'recipient' => 'anna@example.test']));
        Mail::assertQueuedCount(3);
        Mail::assertQueued(AdminMessage::class, fn ($mail) => $mail->hasTo('anna@example.test'));
    }

    public function test_single_user_must_be_a_verified_user(): void
    {
        Mail::fake();
        User::factory()->unverified()->create(['email' => 'new@example.test']);

        $this->actingAs($this->admin)->post(route('admin.mails.send'), $this->mailData(['group' => 'user', 'recipient' => 'new@example.test']))
            ->assertSessionHasErrors('recipient');
        $this->actingAs($this->admin)->post(route('admin.mails.send'), $this->mailData(['group' => 'user']))
            ->assertSessionHasErrors('recipient');

        Mail::assertNothingQueued();
    }

    public function test_test_mail_goes_only_to_the_admin(): void
    {
        Mail::fake();
        User::factory()->count(3)->create();

        $this->actingAs($this->admin)->post(route('admin.mails.preview'), $this->mailData(['action' => 'test']))
            ->assertOk()
            ->assertSee('Test mail sent to admin@example.test');

        Mail::assertSent(AdminMessage::class, fn ($mail) => $mail->hasTo('admin@example.test'));
        Mail::assertSentCount(1);
        Mail::assertNothingQueued();
        $this->assertDatabaseCount('admin_mails', 0);
    }

    public function test_sending_the_same_confirm_page_twice_sends_only_once(): void
    {
        Mail::fake();
        User::factory()->create();
        $data = $this->mailData();

        $this->actingAs($this->admin)->post(route('admin.mails.send'), $data);
        $this->actingAs($this->admin)->post(route('admin.mails.send'), $data)
            ->assertSessionHas('notif.success', 'This mail was already sent.');

        Mail::assertQueuedCount(2);
        $this->assertDatabaseCount('admin_mails', 1);
    }

    public function test_mails_are_spread_over_minutes_by_the_rate_limit(): void
    {
        Mail::fake();
        config(['mail.per_minute' => 2]);
        User::factory()->count(4)->create();

        $this->actingAs($this->admin)->post(route('admin.mails.send'), $this->mailData());

        $delays = Mail::queued(AdminMessage::class)->map(fn ($mail) => $mail->delay)->values();
        $this->assertSame(2, $delays->filter(fn ($delay) => $delay === null)->count());
        $this->assertSame(2, $delays->filter(fn ($delay) => $delay && now()->diffInSeconds($delay) >= 59 && now()->diffInSeconds($delay) <= 61)->count());
        $this->assertSame(1, $delays->filter(fn ($delay) => $delay && now()->diffInSeconds($delay) >= 119)->count());
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
        $this->assertStringContainsString('These mails cannot be switched off.', $html);
        $this->assertStringNotContainsString('mail settings', $html);
    }

    public function test_no_list_unsubscribe_header(): void
    {
        Mail::to('anna@example.test')->send(new AdminMessage('Hi', 'Text'));
        $message = Mail::mailer()->getSymfonyTransport()->messages()->last()->getOriginalMessage();

        $this->assertFalse($message->getHeaders()->has('List-Unsubscribe'));
    }

    public function test_sent_mails_are_listed(): void
    {
        AdminMail::create([
            'user_id' => $this->admin->id, 'subject' => 'Old news', 'body' => 'x', 'group' => 'all',
            'recipient_count' => 7, 'send_token' => (string) Str::uuid(),
        ]);

        $this->actingAs($this->admin)->get(route('admin.mails.index'))
            ->assertSee('Old news')
            ->assertSee('7 users');
    }
}
