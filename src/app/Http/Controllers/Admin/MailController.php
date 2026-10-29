<?php

namespace App\Http\Controllers\Admin;

use App\Actions\QueueMails;
use App\Http\Controllers\Controller;
use App\Mail\AdminMessage;
use App\Models\AdminMail;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Admins write a mail, preview it in the mail layout and send it to a group of users (FEAT-017).
 * Write → Preview (with "Send test to me") → Confirm "Send to N users?" → queued, one mail per user.
 */
class MailController extends Controller
{
    public const GROUPS = [
        'all' => 'All verified users',
        'admins' => 'Admins only',
        'user' => 'A single user',
    ];

    public function index(): View
    {
        return view('admin.mails.index', [
            'groups' => self::GROUPS,
            'counts' => [
                'all' => $this->recipients('all')->count(),
                'admins' => $this->recipients('admins')->count(),
                'user' => 1,
            ],
            'users' => User::whereNotNull('email_verified_at')->orderBy('name')->get(['name', 'email']),
            'sent' => AdminMail::with('user')->latest()->take(20)->get(),
            'failedJobs' => DB::table('failed_jobs')->count(),
        ]);
    }

    /**
     * Preview, and "Send test to me" from the preview page.
     */
    public function preview(Request $request): View|RedirectResponse
    {
        if ($request->input('action') === 'edit') {
            return redirect()->route('admin.mails.index')->withInput();
        }

        $data = $this->validated($request);
        $mail = new AdminMessage($data['subject'], $data['body']);

        if ($request->input('action') === 'test') {
            Mail::to($request->user())->send($mail);
            session()->now('notif.success', "Test mail sent to {$request->user()->email}.");
        }

        return view('admin.mails.preview', [
            'data' => $data,
            'groupLabel' => self::GROUPS[$data['group']],
            'count' => $this->recipients($data['group'], $data['recipient'] ?? null)->count(),
            'html' => $mail->render(),
            // a new token per preview, sending the same confirm page twice sends only once
            'sendToken' => (string) Str::uuid(),
        ]);
    }

    public function send(Request $request, QueueMails $queueMails): RedirectResponse
    {
        $data = $this->validated($request);
        $request->validate(['send_token' => 'required|uuid']);

        if (AdminMail::where('send_token', $request->input('send_token'))->exists()) {
            session()->flash('notif.success', 'This mail was already sent.');
            return redirect()->route('admin.mails.index');
        }

        $recipients = $this->recipients($data['group'], $data['recipient'] ?? null)->get();
        $log = AdminMail::create([
            'user_id' => $request->user()->id,
            'subject' => $data['subject'],
            'body' => $data['body'],
            'group' => $data['group'],
            'recipient_email' => $data['recipient'] ?? null,
            'recipient_count' => $recipients->count(),
            'send_token' => $request->input('send_token'),
        ]);

        $queueMails->handle($recipients, fn () => new AdminMessage($log->subject, $log->body));

        session()->flash('notif.success', "Mail queued for {$log->recipient_count} " . Str::plural('user', $log->recipient_count) . '.');
        return redirect()->route('admin.mails.index');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'subject' => 'required|string|max:150',
            'body' => 'required|string|max:10000',
            'group' => ['required', Rule::in(array_keys(self::GROUPS))],
            'recipient' => [
                'nullable', 'required_if:group,user', 'email',
                Rule::exists('users', 'email')->whereNotNull('email_verified_at'),
            ],
        ], [
            'recipient.exists' => 'There is no verified user with this email.',
        ]);
    }

    /**
     * Only verified users get mails. The notification switches of the profile are ignored on purpose.
     */
    private function recipients(string $group, ?string $email = null): Builder
    {
        $query = User::whereNotNull('email_verified_at')->orderBy('id');

        return match ($group) {
            'admins' => $query->where('usertype', 'admin'),
            'user' => $query->where('email', $email),
            default => $query,
        };
    }
}
