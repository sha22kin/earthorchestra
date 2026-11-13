@props(['name'])
@php
    $paths = [
        'play'     => '<path d="M8 5.5v13l10.5-6.5z" fill="currentColor" stroke-linejoin="round"/>',
        'pause'    => '<path d="M8.5 5.5v13M15.5 5.5v13" stroke-width="3"/>',
        'speaker'  => '<path d="M4.5 9.5h3l4.5-4v13l-4.5-4h-3z" stroke-linejoin="round"/><path d="M15.5 9a4 4 0 0 1 0 6M18 6.5a7.5 7.5 0 0 1 0 11"/>',
        'stop'     => '<rect x="6.5" y="6.5" width="11" height="11" rx="2" fill="currentColor"/>',
        'arrow-r'  => '<path d="M4.5 12h14M13.5 6.5 19 12l-5.5 5.5"/>',
        'arrow-l'  => '<path d="M19.5 12h-14M10.5 6.5 5 12l5.5 5.5"/>',
        'arrow-d'  => '<path d="M12 4.5v14M6.5 13.5 12 19l5.5-5.5"/>',
        'external' => '<path d="M10 5.5H6a1 1 0 0 0-1 1V18a1 1 0 0 0 1 1h11.5a1 1 0 0 0 1-1v-4"/><path d="M13.5 4.5h6v6M19.5 4.5 11 13"/>',
        'copy'     => '<rect x="8.5" y="8.5" width="11" height="11" rx="2"/><path d="M15.5 8.5v-3a1 1 0 0 0-1-1h-9a1 1 0 0 0-1 1v9a1 1 0 0 0 1 1h3"/>',
        'check'    => '<path d="m5 12.5 4.5 4.5L19 7.5"/>',
        'restart'  => '<path d="M5 12a7 7 0 1 0 2.1-5"/><path d="M5 4.5v4h4"/>',
        'cursor'   => '<path d="M6 4.5 18 11l-5.5 1.5L10 18z" stroke-linejoin="round"/>',
        'slider'   => '<path d="M12 3.5v17"/><circle cx="12" cy="12" r="3" fill="currentColor"/><path d="M6 9 3.5 12 6 15M18 9l2.5 3-2.5 3"/>',
        'lock'     => '<rect x="5.5" y="10.5" width="13" height="9" rx="2"/><path d="M8.5 10.5V8a3.5 3.5 0 0 1 7 0v2.5"/>',
        'save'     => '<path d="M6 4.5h9.5l3 3V19a.5.5 0 0 1-.5.5H6a.5.5 0 0 1-.5-.5V5a.5.5 0 0 1 .5-.5z"/><path d="M8.5 4.5v4h6v-4M8.5 19.5v-6h7v6"/>',
        'note'     => '<path d="M9 18V6.5l10-2V16"/><circle cx="6.5" cy="18" r="2.5"/><circle cx="16.5" cy="16" r="2.5"/>',
    ];
@endphp
<svg {{ $attributes->merge(['class' => 'icon icon--'.$name]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" aria-hidden="true" focusable="false">{!! $paths[$name] ?? '' !!}</svg>
