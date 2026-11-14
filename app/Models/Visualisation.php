<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class Visualisation extends Model
{
    public const CACHE_KEY = 'visualisations.ordered';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'body' => 'array',
            'legend' => 'array',
            'position' => 'integer',
            'svs_id' => 'integer',
            'data_starts_on' => 'date',
            'data_ends_on' => 'date',
        ];
    }

    protected static function booted(): void
    {
        $flush = fn () => Cache::forget(self::CACHE_KEY);

        static::saved($flush);
        static::deleted($flush);
    }

    public static function ordered(): Collection
    {
        return Cache::rememberForever(
            self::CACHE_KEY,
            fn () => static::query()->with('citations')->orderBy('position')->get()
        );
    }

    public function citations(): HasMany
    {
        return $this->hasMany(Citation::class)->orderBy('position');
    }

    public function scopeInOrder(Builder $query): Builder
    {
        return $query->orderBy('position');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected function numeral(): Attribute
    {
        return Attribute::get(fn () => (string) $this->position);
    }

    protected function svsUrl(): Attribute
    {
        return Attribute::get(fn () => "https://svs.gsfc.nasa.gov/{$this->svs_id}/");
    }

    protected function dateRange(): Attribute
    {
        return Attribute::get(function () {
            $start = $this->data_starts_on;
            $end = $this->data_ends_on;

            return $start->isSameYear($end)
                ? $start->format('j M').' - '.$end->format('j M Y')
                : $start->format('j M Y').' - '.$end->format('j M Y');
        });
    }

    protected function readingMinutes(): Attribute
    {
        return Attribute::get(function () {
            $words = collect($this->body)->sum(fn ($block) => str_word_count($block['text'] ?? ''));

            return max(1, (int) round($words / 200));
        });
    }

    public function legendTicks(): array
    {
        $stops = array_values($this->legend['stops']);
        $last = max(1, count($stops) - 1);

        return collect($this->legend['ticks'])->map(function ($tick) use ($stops, $last) {
            if (! is_numeric($tick)) {
                foreach ($stops as $i => $stop) {
                    if (str_starts_with($stop['label'] ?? '', (string) $tick)) {
                        return ['label' => (string) $tick, 'at' => $i / $last];
                    }
                }

                return null;
            }

            foreach ($stops as $i => $stop) {
                $next = $stops[$i + 1] ?? null;

                if ($tick <= $stop['value'] || $next === null) {
                    return ['label' => (string) $tick, 'at' => $i / $last];
                }

                if ($tick < $next['value']) {
                    $f = ($tick - $stop['value']) / ($next['value'] - $stop['value']);

                    return ['label' => (string) $tick, 'at' => ($i + $f) / $last];
                }
            }

            return null;
        })->filter()->values()->all();
    }

    public function toPlayerConfig(): array
    {
        return [
            'slug' => $this->slug,
            'title' => $this->short_title,
            'interaction' => $this->interaction,
            'accent' => $this->accent,
            'audio' => asset($this->audio_path),
            'legend' => $this->legend,
        ];
    }
}
