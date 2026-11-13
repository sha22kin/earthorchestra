<?php

namespace App\Support;

use App\Models\Visualisation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Lightweight health checks shown on the admin dashboard.
 * Each row: [label, icon, state (ok|warn|off), badge text].
 */
final class SystemStatus
{
    public static function checks(): array
    {
        return [
            self::database(),
            self::cache(),
            self::media(),
            self::storage(),
            self::debug(),
        ];
    }

    private static function database(): array
    {
        try {
            DB::select('select 1');

            return ['Database', 'database', 'ok', 'Online'];
        } catch (Throwable) {
            return ['Database', 'database', 'off', 'Offline'];
        }
    }

    private static function cache(): array
    {
        try {
            Cache::put('admin.health', 1, 10);

            return ['Cache ('.config('cache.default').')', 'bolt', Cache::get('admin.health') ? 'ok' : 'warn', 'Active'];
        } catch (Throwable) {
            return ['Cache', 'bolt', 'off', 'Failing'];
        }
    }

    private static function media(): array
    {
        $all = Visualisation::all();
        $expected = $all->count() * 3;
        $present = $all->sum(fn ($v) => collect([$v->video_path, $v->audio_path, $v->poster_path])
            ->filter(fn ($p) => is_file(public_path($p)))
            ->count());

        return ['Media files', 'film', $present === $expected ? 'ok' : 'warn', "{$present} / {$expected}"];
    }

    private static function storage(): array
    {
        $writable = is_writable(public_path('media')) && is_writable(storage_path('framework'));

        return ['Uploads folder', 'folder', $writable ? 'ok' : 'warn', $writable ? 'Writable' : 'Read-only'];
    }

    private static function debug(): array
    {
        return config('app.debug')
            ? ['Debug mode', 'bug', 'warn', 'On']
            : ['Debug mode', 'bug', 'ok', 'Off'];
    }
}
