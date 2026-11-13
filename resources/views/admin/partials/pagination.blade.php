@if ($paginator->hasPages())
    <nav class="pager-a" aria-label="Pagination">
        <span class="pager-a__info">
            {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}
        </span>
        <span class="pager-a__links">
            @if ($paginator->onFirstPage())
                <span class="btn btn--ghost is-disabled">Previous</span>
            @else
                <a class="btn btn--ghost" href="{{ $paginator->previousPageUrl() }}" rel="prev">Previous</a>
            @endif
            @if ($paginator->hasMorePages())
                <a class="btn btn--ghost" href="{{ $paginator->nextPageUrl() }}" rel="next">Next</a>
            @else
                <span class="btn btn--ghost is-disabled">Next</span>
            @endif
        </span>
    </nav>
@endif
