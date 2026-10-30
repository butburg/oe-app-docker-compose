<?php

namespace App\Mail\Concerns;

trait EscapesMarkdown
{
    /**
     * Characters that Markdown or HTML would read as formatting, a link or a tag.
     */
    private static array $specialChars = [
        '\\', '`', '*', '_', '{', '}', '[', ']', '(', ')', '#', '+', '-', '.', '!', '|', '~', '<', '>', '&', '"', "'",
    ];

    /**
     * Text from users (comments, titles) shown as plain text in a Markdown mail, so nobody can put links or
     * formatting into a mail to someone else.
     */
    protected static function plainText(string $text): string
    {
        // One line only: a blank line would end the quote and the rest would be read as Markdown again
        $text = preg_replace('/\s+/', ' ', trim($text));

        // Each special character becomes an entity, e.g. "*" → "&#42;". Markdown shows an entity as the plain
        // character, and the text part of the mail decodes it, so both parts show the text as it was written
        $entities = [];
        foreach (self::$specialChars as $char) {
            $entities[$char] = '&#' . ord($char) . ';';
        }

        return strtr($text, $entities);
    }
}
