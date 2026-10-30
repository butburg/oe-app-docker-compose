<?php

namespace App\Mail;

use Illuminate\Mail\Events\MessageSending;

/**
 * Mails load nothing from the internet (FEAT-016): the font is part of the HTML, the logo is attached inline
 * and referenced as cid:. Browser previews cannot show cid: images, they get the logo URL instead.
 */
class MailAssets
{
    public const LOGO_CID = 'cid:weedy-logo.png';

    private static ?string $fontCss = null;

    /**
     * @font-face with the Figtree file as data URI (20 KB file, 27 KB in the mail). Clients that ignore
     * web fonts in mails (Gmail, Outlook) fall back to Arial.
     */
    public static function fontCss(): string
    {
        return self::$fontCss ??= "@font-face { font-family: 'Figtree'; font-style: normal; font-weight: 300 900; src: url(data:font/woff2;base64,"
            . base64_encode(file_get_contents(resource_path('fonts/figtree-latin-wght-normal.woff2')))
            . ") format('woff2'); }";
    }

    public static function logoPath(): string
    {
        return public_path('images/mail/logo.png');
    }

    /**
     * Listener for every sent mail: attaches the logo when the HTML uses it.
     */
    public static function embedLogo(MessageSending $event): void
    {
        if (str_contains((string) $event->message->getHtmlBody(), self::LOGO_CID)) {
            $event->message->embedFromPath(self::logoPath(), 'weedy-logo.png', 'image/png');
        }
    }

    /**
     * HTML of a mail for the browser (admin preview, /dev/mail).
     */
    public static function forBrowser(string $html): string
    {
        return str_replace(self::LOGO_CID, asset('images/mail/logo.png'), $html);
    }
}
