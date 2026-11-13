<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateVisualisationRequest;
use App\Models\Visualisation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VisualisationController extends Controller
{
    public function index(): View
    {
        return view('admin.visualisations.index', [
            'visualisations' => Visualisation::withCount('citations')->orderBy('position')->get(),
        ]);
    }

    public function edit(Visualisation $visualisation): View
    {
        $visualisation->load('citations');

        return view('admin.visualisations.edit', [
            'v' => $visualisation,
            'bodyText' => self::blocksToText($visualisation->body),
        ]);
    }

    public function update(UpdateVisualisationRequest $request, Visualisation $visualisation): RedirectResponse
    {
        DB::transaction(function () use ($request, $visualisation) {
            $data = $request->safe()->except(['body', 'legend', 'citations', 'video', 'audio', 'poster']);
            $data['body'] = self::textToBlocks($request->validated('body'));
            $data['legend'] = json_decode($request->validated('legend'), true)
                + ['aliases' => [], 'neutral' => [], 'ticks' => [], 'decimals' => 0];

            foreach (['video' => 'mp4', 'audio' => null, 'poster' => null] as $field => $ext) {
                if ($file = $request->file($field)) {
                    $data["{$field}_path"] = $this->storeMedia($file, $visualisation, $field, $ext);
                }
            }

            $visualisation->update($data);

            $visualisation->citations()->delete();
            foreach (array_values($request->validated('citations') ?? []) as $i => $row) {
                $visualisation->citations()->create([
                    'position' => $i + 1,
                    'reference' => $row['reference'],
                    'url' => $row['url'] ?? null,
                ]);
            }
        });

        // Citations changed without touching the parent, so flush explicitly.
        cache()->forget(Visualisation::CACHE_KEY);

        return redirect()
            ->route('admin.visualisations.edit', $visualisation)
            ->with('status', "Movement {$visualisation->numeral} saved.");
    }

    private function storeMedia(UploadedFile $file, Visualisation $v, string $kind, ?string $ext): string
    {
        $ext ??= strtolower($file->getClientOriginalExtension() ?: $file->extension());
        $name = "movement-{$v->position}-{$kind}-".now()->format('YmdHis').".{$ext}";
        $file->move(public_path('media'), $name);

        return "media/{$name}";
    }

    /**
     * The article body is stored as blocks. In the editor it's plain text:
     * paragraphs separated by a blank line, headings start with "## ".
     */
    public static function blocksToText(array $blocks): string
    {
        return collect($blocks)
            ->map(fn ($b) => ($b['type'] === 'h' ? '## ' : '').$b['text'])
            ->implode("\n\n");
    }

    public static function textToBlocks(string $text): array
    {
        return collect(preg_split('/\R\s*\R/', trim(str_replace("\r\n", "\n", $text))))
            ->map(fn ($chunk) => trim(preg_replace('/\s*\n\s*/', ' ', $chunk)))
            ->filter()
            ->map(fn ($chunk) => str_starts_with($chunk, '## ')
                ? ['type' => 'h', 'text' => trim(substr($chunk, 3))]
                : ['type' => 'p', 'text' => $chunk])
            ->values()
            ->all();
    }
}
