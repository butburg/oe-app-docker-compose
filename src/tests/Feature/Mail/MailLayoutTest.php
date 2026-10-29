<?php

namespace Tests\Feature\Mail;

use App\Mail\AccountAlreadyExists;
use App\Mail\ConfirmNewEmail;
use App\Mail\ConfirmRegistration;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MailLayoutTest extends TestCase
{
    public static function mails(): array
    {
        return [
            'confirm registration' => [fn () => new ConfirmRegistration('Anna', 'https://example.test/confirm')],
            'confirm new email' => [fn () => new ConfirmNewEmail('Anna', 'https://example.test/confirm', 60)],
            'account already exists' => [fn () => new AccountAlreadyExists()],
        ];
    }

    /**
     * @dataProvider mails
     */
    public function test_mail_uses_the_weedy_layout(callable $make): void
    {
        /** @var Mailable $mail */
        $mail = $make();
        $html = $mail->render();

        $this->assertStringContainsString(asset('images/mail/logo.png'), $html);
        $this->assertStringContainsString('width="48" height="48"', $html);
        // two-part wordmark as text, first word in the accent colour
        $this->assertMatchesRegularExpression('/<span class="accent" style="[^"]*color: #A5E04D;[^"]*">Weedy<\/span> Gallery/', $html);
        $this->assertStringContainsString('font-family: Figtree, sans-serif', $html);
        $this->assertStringContainsString('bgcolor="#071D20"', $html);
        $this->assertStringContainsString('You get this mail because this address was used on', $html);

        $this->assertStringNotContainsString('laravel.com/img/notification-logo.png', $html);
        $this->assertStringNotContainsString('All rights reserved', $html);
        $this->assertStringNotContainsString('-apple-system', $html);
        $this->assertStringNotContainsString('@import', $html);
    }

    /**
     * @dataProvider mails
     */
    public function test_plain_text_part_has_brand_and_footer(callable $make): void
    {
        Mail::to('anna@example.test')->send($make());
        $text = Mail::mailer()->getSymfonyTransport()->messages()->last()->getOriginalMessage()->getTextBody();

        $this->assertStringContainsString('Weedy Gallery: ' . config('app.url'), $text);
        $this->assertStringContainsString('You get this mail because this address was used on Weedy Gallery', $text);
        $this->assertStringNotContainsString('<span', $text);
    }

    public function test_button_uses_the_primary_colour_with_dark_text(): void
    {
        $html = (new ConfirmRegistration('Anna', 'https://example.test/confirm'))->render();

        $this->assertMatchesRegularExpression('/<a href="https:\/\/example.test\/confirm" class="button"[^>]*style="[^"]*color: #071D20;/', $html);
        $this->assertStringContainsString('bgcolor="#00D9FF"', $html);
    }

    public function test_mail_is_much_smaller_than_with_the_default_theme(): void
    {
        $html = (new ConfirmRegistration('Anna', 'https://example.test/confirm'))->render();

        // the default Laravel theme gave 9,374 bytes for this mail
        $this->assertLessThan(5500, strlen($html));
    }
}
