<?php

namespace App\Http\Controllers\Dev;

use App\Http\Controllers\Controller;
use App\Mail\AccountAlreadyExists;
use App\Mail\AdminMessage;
use App\Mail\ConfirmNewEmail;
use App\Mail\ConfirmRegistration;
use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Str;

/**
 * Renders every mail with sample data in the browser (FEAT-016). Only registered in the local environment.
 */
class MailPreviewController extends Controller
{
    /**
     * @return array<string, callable(): Mailable>
     */
    private function mails(): array
    {
        return [
            'confirm-registration' => fn () => new ConfirmRegistration('Anna', url('/register/confirm/' . Str::random(40))),
            'confirm-new-email' => fn () => new ConfirmNewEmail('Anna', url('/profile/email/confirm/1/' . sha1('x')), User::EMAIL_CHANGE_VALID_MINUTES),
            'account-already-exists' => fn () => new AccountAlreadyExists(),
            'admin-message' => fn () => new AdminMessage('Maintenance on Sunday', "# Short maintenance on Sunday\n\nThe gallery is **offline from 8 to 9 am** while we update the server.\n\n- Your images and comments stay as they are.\n- Please log in again afterwards.\n\nQuestions? Just answer this mail."),
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
