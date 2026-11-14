<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

final class Prose
{
    public static function withCitations(string $text, string $anchorPrefix = 'ref-'): HtmlString
    {
        $html = preg_replace_callback(
            '/\[(\d{1,2})\]/',
            fn (array $m) => sprintf(
                '<sup class="cite"><a href="#%s%d" aria-label="Source %2$d">%2$d</a></sup>',
                $anchorPrefix,
                (int) $m[1]
            ),
            e($text)
        );

        return new HtmlString($html);
    }

    public static function plain(string $text): string
    {
        return trim(preg_replace('/\s*\[\d{1,2}\]/', '', $text));
    }
}
