@extends('admin.pages._shell')

@php
    $c = $page->content;
    $team = old('team', collect($c['team'] ?? [])->map(fn ($r) => ['role' => $r['role'], 'names' => implode(', ', $r['names'])])->all());
    $programs = old('programs', $c['programs'] ?? []);
    $defaults = \App\Models\Page::CREDIT_TEXT;
@endphp

@section('fields')
    <article class="card">
        <header class="card__head"><h2>Headings &amp; footer</h2></header>
        <div class="card__body stack">
            <div class="fields-2">
                <x-admin.field name="eyebrow" label="Small line above the title" :value="$c['eyebrow'] ?? ''" :placeholder="$defaults['eyebrow']" />
                <x-admin.field name="programs_title" label="Tools heading" :value="$c['programs_title'] ?? ''" :placeholder="$defaults['programs_title']" />
                <x-admin.field name="sources_title" label="Visualisations heading" :value="$c['sources_title'] ?? ''" :placeholder="$defaults['sources_title']" />
                <x-admin.field name="ai_title" label="AI note heading" :value="$c['ai_title'] ?? ''" :placeholder="$defaults['ai_title']" />
            </div>
            <div class="fields-2">
                <x-admin.field name="footer_left" label="Footer, left" :value="$c['footer_left'] ?? ''" :placeholder="$defaults['footer_left']" hint="{year} becomes the current year." />
                <x-admin.field name="footer_right" label="Footer, right" :value="$c['footer_right'] ?? ''" :placeholder="$defaults['footer_right']" />
            </div>
        </div>
    </article>

    <article class="card">
        <header class="card__head">
            <h2>The company</h2>
            <button type="button" class="btn btn--ghost btn--sm btn--icon" data-repeater-add="team" title="Add role" aria-label="Add role"><x-admin.icon name="plus" /></button>
        </header>
        <div class="card__body">
            <ol class="repeater" data-repeater="team">
                @foreach ($team as $i => $row)
                    @include('admin.pages.pair-row', ['group' => 'team', 'i' => $i, 'a' => ['role', 'Role', $row['role'] ?? ''], 'b' => ['names', 'Names, comma separated', $row['names'] ?? '']])
                @endforeach
            </ol>
            <template data-repeater-template="team">
                @include('admin.pages.pair-row', ['group' => 'team', 'i' => '__i__', 'a' => ['role', 'Role', ''], 'b' => ['names', 'Names, comma separated', '']])
            </template>
        </div>
    </article>

    <article class="card">
        <header class="card__head">
            <h2>Instruments (tools)</h2>
            <button type="button" class="btn btn--ghost btn--sm btn--icon" data-repeater-add="programs" title="Add tool" aria-label="Add tool"><x-admin.icon name="plus" /></button>
        </header>
        <div class="card__body">
            <ol class="repeater" data-repeater="programs">
                @foreach ($programs as $i => $row)
                    @include('admin.pages.pair-row', ['group' => 'programs', 'i' => $i, 'a' => ['label', 'Used for', $row['label'] ?? ''], 'b' => ['value', 'Tool', $row['value'] ?? '']])
                @endforeach
            </ol>
            <template data-repeater-template="programs">
                @include('admin.pages.pair-row', ['group' => 'programs', 'i' => '__i__', 'a' => ['label', 'Used for', ''], 'b' => ['value', 'Tool', '']])
            </template>
        </div>
    </article>

    <article class="card">
        <header class="card__head"><h2>Notes</h2></header>
        <div class="card__body stack">
            <x-admin.field name="citations_note" label="Note under the visualisation list" type="textarea" rows="2" :value="$c['citations_note'] ?? ''" />
            <x-admin.field name="ai_note" label="A note on AI usage" type="textarea" rows="4" :value="$c['ai_note'] ?? ''" hint="Leave empty to hide this section." />
        </div>
    </article>
@endsection
