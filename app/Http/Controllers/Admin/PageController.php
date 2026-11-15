<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public const PARAGRAPH_FIELDS = [
        'home' => ['intro', 'how', 'cursor', 'slider'],
        'about' => ['paragraphs'],
        'credits' => [],
    ];

    public function index(): View
    {
        return view('admin.pages.index', ['pages' => Page::orderBy('id')->get()]);
    }

    public function edit(Page $page): View
    {
        abort_unless(array_key_exists($page->slug, self::PARAGRAPH_FIELDS), 404);

        return view("admin.pages.edit-{$page->slug}", ['page' => $page]);
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        abort_unless(array_key_exists($page->slug, self::PARAGRAPH_FIELDS), 404);

        $base = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'lede' => ['nullable', 'string', 'max:255'],
        ]);

        $content = match ($page->slug) {
            'home' => $this->homeContent($request),
            'about' => $this->aboutContent($request),
            'credits' => $this->creditsContent($request),
        };

        $page->update($base + ['content' => $content]);

        return back()->with('status', "{$page->title} saved.");
    }

    private function homeContent(Request $request): array
    {
        $v = $request->validate([
            'intro' => ['required', 'string', 'max:5000'],
            'how' => ['required', 'string', 'max:5000'],
            'cursor' => ['required', 'string', 'max:5000'],
            'slider' => ['required', 'string', 'max:5000'],
            'orchestra' => ['required', 'string', 'max:500'],
        ]);

        return [
            'intro' => self::paragraphs($v['intro']),
            'how' => self::paragraphs($v['how']),
            'cursor' => self::paragraphs($v['cursor']),
            'slider' => self::paragraphs($v['slider']),
            'orchestra' => trim($v['orchestra']),
        ];
    }

    private function aboutContent(Request $request): array
    {
        $v = $request->validate([
            'paragraphs' => ['required', 'string', 'max:15000'],
            'pullquote' => ['nullable', 'string', 'max:300'],
        ]);

        return ['paragraphs' => self::paragraphs($v['paragraphs']), 'pullquote' => trim((string) $v['pullquote'])];
    }

    private function creditsContent(Request $request): array
    {
        $request->merge([
            'team' => collect($request->input('team', []))->filter(fn ($r) => trim($r['role'] ?? '') !== '')->values()->all(),
            'programs' => collect($request->input('programs', []))->filter(fn ($r) => trim($r['label'] ?? '') !== '')->values()->all(),
        ]);

        $v = $request->validate([
            'team' => ['array', 'max:30'],
            'team.*.role' => ['required', 'string', 'max:80'],
            'team.*.names' => ['required', 'string', 'max:200'],
            'programs' => ['array', 'max:20'],
            'programs.*.label' => ['required', 'string', 'max:80'],
            'programs.*.value' => ['required', 'string', 'max:120'],
            'ai_note' => ['nullable', 'string', 'max:2000'],
            'citations_note' => ['nullable', 'string', 'max:500'],
            'eyebrow' => ['nullable', 'string', 'max:120'],
            'programs_title' => ['nullable', 'string', 'max:120'],
            'sources_title' => ['nullable', 'string', 'max:120'],
            'ai_title' => ['nullable', 'string', 'max:120'],
            'footer_left' => ['nullable', 'string', 'max:200'],
            'footer_right' => ['nullable', 'string', 'max:200'],
        ]);

        $labels = collect(array_keys(Page::CREDIT_TEXT))
            ->mapWithKeys(fn ($key) => [$key => trim((string) ($v[$key] ?? ''))])
            ->all();

        return $labels + [
            'team' => collect($v['team'] ?? [])->map(fn ($r) => [
                'role' => trim($r['role']),
                'names' => collect(preg_split('/\s*(,|&)\s*/', $r['names']))->filter()->values()->all(),
            ])->all(),
            'programs' => collect($v['programs'] ?? [])->map(fn ($r) => ['label' => trim($r['label']), 'value' => trim($r['value'])])->all(),
            'ai_note' => trim((string) ($v['ai_note'] ?? '')),
            'citations_note' => trim((string) ($v['citations_note'] ?? '')),
        ];
    }

    public static function paragraphs(string $text): array
    {
        return collect(preg_split('/\R\s*\R/', trim($text)))
            ->map(fn ($p) => trim(preg_replace('/\s*\R\s*/', ' ', $p)))
            ->filter()
            ->values()
            ->all();
    }
}
