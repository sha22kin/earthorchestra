<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Page extends Model
{
    public const CREDITS_CACHE_KEY = 'page.credits';

    /** Editable labels on the credits page and footer, with their defaults. */
    public const CREDIT_TEXT = [
        'eyebrow' => 'The programme notes',
        'programs_title' => 'Instruments',
        'sources_title' => 'Visualisations',
        'ai_title' => 'A note on AI usage',
        'footer_left' => '© {year} The Earth Orchestra team',
        'footer_right' => 'Illustrated by hand · Scored in Draw Audio',
    ];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['content' => 'array'];
    }

    protected static function booted(): void
    {
        $flush = fn () => Cache::forget(self::CREDITS_CACHE_KEY);

        static::saved($flush);
        static::deleted($flush);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** A credits label, falling back to its default when left empty. */
    public function text(string $key): string
    {
        $value = $this->content[$key] ?? null;

        return str_replace('{year}', date('Y'), filled($value) ? $value : (self::CREDIT_TEXT[$key] ?? ''));
    }

    /** Credits label for places outside the credits page, such as the footer. */
    public static function creditText(string $key): string
    {
        $page = Cache::rememberForever(self::CREDITS_CACHE_KEY, fn () => static::where('slug', 'credits')->first());

        return $page ? $page->text($key) : str_replace('{year}', date('Y'), self::CREDIT_TEXT[$key] ?? '');
    }
}
