@if ($paginator->hasPages())
    <nav class="pager-a" aria-label="Pagination">
        <span class="pager-a__info">
            {{ $paginator->firstItem() }}-{{ $paginator->lastItem() }} of {{ $paginator->total() }}
        </span>
        <span class="pager-a__links">
            @if ($paginator->onFirstPage())
                <span class="btn btn--ghost btn--icon is-disabled" aria-hidden="true"><x-admin.icon name="chevron" class="flip" /></span>
            @else
                <a class="btn btn--ghost btn--icon" href="{{ $paginator->previousPageUrl() }}" rel="prev" title="Previous page" aria-label="Previous page"><x-admin.icon name="chevron" class="flip" /></a>
            @endif
            @if ($paginator->hasMorePages())
                <a class="btn btn--ghost btn--icon" href="{{ $paginator->nextPageUrl() }}" rel="next" title="Next page" aria-label="Next page"><x-admin.icon name="chevron" /></a>
            @else
                <span class="btn btn--ghost btn--icon is-disabled" aria-hidden="true"><x-admin.icon name="chevron" /></span>
            @endif
        </span>
    </nav>
@endif
