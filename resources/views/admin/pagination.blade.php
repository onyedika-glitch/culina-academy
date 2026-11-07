@if ($paginator->hasPages())
<nav class="admin-pagination" aria-label="Pagination">
    @if ($paginator->total() > 0)
    <p class="admin-pagination-info">
        Showing <strong>{{ $paginator->firstItem() }}</strong> to <strong>{{ $paginator->lastItem() }}</strong> of <strong>{{ $paginator->total() }}</strong> results
    </p>
    @endif

    <div class="admin-pagination-links">
        @if ($paginator->onFirstPage())
            <span class="admin-btn admin-btn-secondary admin-btn-sm is-disabled" aria-disabled="true">Previous</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="admin-btn admin-btn-secondary admin-btn-sm" rel="prev">Previous</a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="admin-pagination-ellipsis">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="admin-btn admin-btn-primary admin-btn-sm is-active" aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="admin-btn admin-btn-secondary admin-btn-sm">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="admin-btn admin-btn-secondary admin-btn-sm" rel="next">Next</a>
        @else
            <span class="admin-btn admin-btn-secondary admin-btn-sm is-disabled" aria-disabled="true">Next</span>
        @endif
    </div>
</nav>
@endif