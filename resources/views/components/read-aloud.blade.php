@props(['target', 'label' => 'Read aloud'])
<button type="button" {{ $attributes->merge(['class' => 'read-aloud']) }} data-tts="{{ $target }}" hidden>
    <x-icon name="speaker" />
    <span data-tts-label>{{ $label }}</span>
</button>
