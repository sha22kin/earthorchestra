<li class="repeater__row">
    <span class="repeater__num"></span>
    <div class="repeater__fields">
        <textarea name="citations[{{ $i }}][reference]" rows="2" placeholder="Author. (Year). Title. Publisher." aria-label="Reference">{{ $c['reference'] ?? '' }}</textarea>
        <input type="url" name="citations[{{ $i }}][url]" value="{{ $c['url'] ?? '' }}" placeholder="https://…" aria-label="Link">
    </div>
    <button type="button" class="icon-btn icon-btn--danger" data-repeater-remove aria-label="Remove source"><x-admin.icon name="trash" /></button>
</li>
