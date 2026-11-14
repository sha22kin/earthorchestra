<?php

namespace App\Support;

final class Prose
{
    public static function plain(string $text): string
    {
        return trim(preg_replace('/\s*\[\d{1,2}\]/', '', $text));
    }
}
