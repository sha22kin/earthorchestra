<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Arrangement extends Model
{
    private const ALPHABET = 'abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    protected $guarded = ['id'];

    protected $hidden = ['id', 'creator_hash'];

    protected function casts(): array
    {
        return [
            'levels' => 'array',
            'listens' => 'integer',
        ];
    }

    public function plays(): HasMany
    {
        return $this->hasMany(ArrangementPlay::class);
    }

    public function recordListen(): void
    {
        $this->increment('listens');
        $this->plays()->create();
    }

    public function getRouteKeyName(): string
    {
        return 'code';
    }

    public static function freshCode(int $length = 8): string
    {
        do {
            $code = '';
            for ($i = 0; $i < $length; $i++) {
                $code .= self::ALPHABET[random_int(0, strlen(self::ALPHABET) - 1)];
            }
        } while (static::where('code', $code)->exists());

        return $code;
    }

    public function shareUrl(): string
    {
        return route('arrangements.show', $this);
    }

    public function displayTitle(): string
    {
        return $this->title ?: 'Untitled arrangement';
    }

    public function excerpt(): string
    {
        return Str::limit($this->displayTitle(), 40);
    }
}
