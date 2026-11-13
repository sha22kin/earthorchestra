<li class="repeater__row">
    <span class="repeater__num"></span>
    <div class="repeater__fields repeater__fields--pair">
        <input type="text" name="{{ $group }}[{{ $i }}][{{ $a[0] }}]" value="{{ $a[2] }}" placeholder="{{ $a[1] }}" aria-label="{{ $a[1] }}">
        <input type="text" name="{{ $group }}[{{ $i }}][{{ $b[0] }}]" value="{{ $b[2] }}" placeholder="{{ $b[1] }}" aria-label="{{ $b[1] }}">
    </div>
    <button type="button" class="icon-btn icon-btn--danger" data-repeater-remove aria-label="Remove row"><x-admin.icon name="trash" /></button>
</li>
