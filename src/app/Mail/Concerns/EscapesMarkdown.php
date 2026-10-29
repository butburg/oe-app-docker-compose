<?php

namespace App\Mail\Concerns;

trait EscapesMarkdown
{
    /**
     * Text from users (comments, titles) shown as plain text in a Markdown mail: HTML is escaped and Markdown
     * characters get a backslash, so nobody can put links or formatting into a mail to someone else.
     */
    protected static function plainText(string $text): string
    {
        return preg_replace('/([\\\\`*_{}\[\]()#+\-.!|~])/', '\\\\$1', e($text));
    }
}
