<?php

namespace App\Support;

class MailSanitizer
{
    /**
     * Strip CRLF, null bytes, dangerous tag content, and HTML tags — safe for SMTP header fields.
     *
     * Script/style tag contents are removed entirely (not just the tags), so that
     * values like <script>alert(1)</script> produce an empty string rather than "alert(1)".
     */
    public static function sanitizeForHeader(string $value): string
    {
        $clean = preg_replace('/[\r\n\0]/', '', $value) ?? '';
        // Remove contents of dangerous tags entirely before stripping all tags
        $clean = preg_replace('/<(script|style|iframe|object|embed|form)[^>]*>.*?<\/\1>/is', '', $clean) ?? $clean;

        return strip_tags($clean);
    }

    /**
     * Make a value safe for embedding in Markdown mail body lines.
     * Strips CRLF/null bytes, dangerous tag contents (script/style/etc.), remaining
     * HTML tags, escapes backslashes, and escapes CommonMark link-syntax characters.
     */
    public static function sanitizeForMarkdown(string $value): string
    {
        $stripped = preg_replace('/[\r\n\0]/', '', $value) ?? '';
        // Remove contents of dangerous tags entirely before stripping all tags
        $stripped = preg_replace('/<(script|style|iframe|object|embed|form)[^>]*>.*?<\/\1>/is', '', $stripped) ?? $stripped;
        $stripped = strip_tags($stripped);
        $stripped = str_replace('\\', '\\\\', $stripped);

        return str_replace(['[', ']', '(', ')'], ['\[', '\]', '\(', '\)'], $stripped);
    }
}
