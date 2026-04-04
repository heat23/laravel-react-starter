<?php

namespace App\Support;

class MailSanitizer
{
    /**
     * Strip CRLF and null bytes — safe for SMTP header fields.
     */
    public static function sanitizeForHeader(string $value): string
    {
        return preg_replace('/[\r\n\0]/', '', $value) ?? '';
    }

    /**
     * Make a value safe for embedding in Markdown mail body lines.
     * Strips CRLF/null bytes, HTML tags, escapes backslashes, and escapes
     * CommonMark link-syntax characters so renderers cannot interpret them.
     */
    public static function sanitizeForMarkdown(string $value): string
    {
        $stripped = preg_replace('/[\r\n\0]/', '', $value) ?? '';
        $stripped = strip_tags($stripped);
        $stripped = str_replace('\\', '\\\\', $stripped);

        return str_replace(['[', ']', '(', ')'], ['\[', '\]', '\(', '\)'], $stripped);
    }
}
