<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Arrangement;
use App\Models\ArrangementPlay;
use App\Models\Citation;
use App\Models\Page;
use App\Models\User;
use App\Models\Visualisation;
use App\Support\SystemStatus;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $mediaBytes = collect(glob(public_path('media/*')) ?: [])->sum(fn ($f) => filesize($f));

        $stats = [
            ['Movements', Visualisation::count(), 'music', 'blue', route('admin.visualisations.index')],
            ['Saved arrangements', Arrangement::count(), 'sliders', 'green', route('admin.arrangements.index')],
            ['Shared-link listens', (int) Arrangement::sum('listens'), 'headphones', 'orange', route('admin.arrangements.index')],
            ['Citations', Citation::count(), 'quote', 'teal', route('admin.visualisations.index')],
            ['Pages', Page::count(), 'file', 'indigo', route('admin.pages.index')],
            ['Saved this week', Arrangement::where('created_at', '>=', now()->subDays(7))->count(), 'trend', 'green', route('admin.arrangements.index')],
            ['Admins & editors', User::count(), 'users', 'teal', request()->user()->can('manage-users') ? route('admin.users.index') : route('admin.profile.edit')],
            ['Media on disk', $this->humanBytes($mediaBytes), 'film', 'orange', route('admin.visualisations.index')],
        ];

        return view('admin.dashboard', [
            'stats' => $stats,
            'chart' => $this->lastSevenDays(),
            'status' => SystemStatus::checks(),
            'recent' => Arrangement::latest()->take(6)->get(),
            'popular' => Arrangement::orderByDesc('listens')->where('listens', '>', 0)->take(6)->get(),
            'visualisations' => Visualisation::ordered(),
        ]);
    }

    /**
     * Two series for the chart: arrangements saved per day, and shared-link opens per day.
     */
    private function lastSevenDays(): array
    {
        $start = Carbon::today()->subDays(6);
        $days = collect(range(0, 6))->map(fn ($i) => $start->copy()->addDays($i));

        $saved = Arrangement::where('created_at', '>=', $start)
            ->select(DB::raw('DATE(created_at) as d'), DB::raw('COUNT(*) as n'))
            ->groupBy('d')->pluck('n', 'd');

        $plays = ArrangementPlay::where('created_at', '>=', $start)
            ->select(DB::raw('DATE(created_at) as d'), DB::raw('COUNT(*) as n'))
            ->groupBy('d')->pluck('n', 'd');

        return [
            'labels' => $days->map->format('D')->all(),
            'dates' => $days->map->format('j M')->all(),
            'series' => [
                ['name' => 'Arrangements saved', 'color' => '#2563eb', 'values' => $days->map(fn ($d) => (int) ($saved[$d->toDateString()] ?? 0))->all()],
                ['name' => 'Shared-link listens', 'color' => '#059669', 'values' => $days->map(fn ($d) => (int) ($plays[$d->toDateString()] ?? 0))->all()],
            ],
        ];
    }

    private function humanBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = $bytes > 0 ? min(3, (int) floor(log($bytes, 1024))) : 0;

        return round($bytes / (1024 ** $i), $i > 1 ? 1 : 0).' '.$units[$i];
    }
}
