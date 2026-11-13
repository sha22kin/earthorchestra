@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'hint' => null,
    'rows' => 4,
    'options' => [],
    'required' => false,
])
@php
    $id = 'f-'.str_replace(['[', ']', '.'], '-', $name);
    $dot = str_replace(['[', ']'], ['.', ''], $name);
    $current = old($dot, $value);
    $invalid = $errors->has($dot);
@endphp
<div {{ $attributes->only('class')->merge(['class' => 'field'.($invalid ? ' is-invalid' : '')]) }}>
    <label for="{{ $id }}">{{ $label }} @if ($required)<span class="req" aria-hidden="true">*</span>@endif</label>

    @if ($type === 'textarea')
        <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}" @required($required) {{ $attributes->only(['data-autosize', 'spellcheck', 'data-legend-input']) }}>{{ $current }}</textarea>
    @elseif ($type === 'select')
        <select id="{{ $id }}" name="{{ $name }}" @required($required)>
            @foreach ($options as $optValue => $optLabel)
                <option value="{{ $optValue }}" @selected((string) $current === (string) $optValue)>{{ $optLabel }}</option>
            @endforeach
        </select>
    @elseif ($type === 'color')
        <div class="color-input">
            <input type="color" value="{{ $current }}" data-color-picker="{{ $id }}" aria-label="{{ $label }} picker">
            <input id="{{ $id }}" type="text" name="{{ $name }}" value="{{ $current }}" maxlength="7" @required($required) pattern="#[0-9a-fA-F]{6}">
        </div>
    @else
        <input id="{{ $id }}" type="{{ $type }}" name="{{ $name }}" value="{{ $type === 'password' ? '' : $current }}" @required($required)
               {{ $attributes->only(['autocomplete', 'accept', 'maxlength', 'min', 'max', 'placeholder']) }}>
    @endif

    @if ($invalid)
        <p class="field__error">{{ $errors->first($dot) }}</p>
    @elseif ($hint)
        <p class="field__hint">{{ $hint }}</p>
    @endif
</div>
