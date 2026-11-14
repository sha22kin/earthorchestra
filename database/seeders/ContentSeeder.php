<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\Visualisation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ContentSeeder extends Seeder
{
    private function overwrite(): bool
    {
        return filter_var(env('CONTENT_SEED_OVERWRITE', false), FILTER_VALIDATE_BOOL);
    }

    public function run(): void
    {
        DB::transaction(function () {
            $this->seedVisualisations();
            $this->seedPages();
        });

        Cache::forget(Visualisation::CACHE_KEY);
    }

    private function seedVisualisations(): void
    {
        foreach ($this->read('visualisations.json') as $row) {
            if (! $this->overwrite() && Visualisation::where('slug', $row['slug'])->exists()) {
                continue;
            }

            $visualisation = Visualisation::updateOrCreate(
                ['slug' => $row['slug']],
                Arr::except($row, ['slug', 'citations'])
            );

            $visualisation->citations()->delete();

            foreach (array_values($row['citations']) as $i => $citation) {
                $visualisation->citations()->create([
                    'position' => $i + 1,
                    'reference' => $citation['text'],
                    'url' => $citation['url'],
                ]);
            }
        }
    }

    private function seedPages(): void
    {
        foreach ($this->read('pages.json') as $row) {
            if (! $this->overwrite() && Page::where('slug', $row['slug'])->exists()) {
                continue;
            }

            Page::updateOrCreate(['slug' => $row['slug']], Arr::except($row, ['slug']));
        }
    }

    private function read(string $file): array
    {
        return json_decode(
            File::get(database_path("seeders/content/{$file}")),
            associative: true,
            flags: JSON_THROW_ON_ERROR
        );
    }
}
