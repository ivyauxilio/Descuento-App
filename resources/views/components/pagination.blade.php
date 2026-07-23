@props(['paginator'])

@if ($paginator->hasPages())
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-3 pt-3 border-top">
        <!-- Results Info -->
        <div class="text-muted small">
            <i class="fas fa-list-ul me-1"></i>
            Showing
            <strong>{{ $paginator->firstItem() ?? 0 }}</strong>
            to
            <strong>{{ $paginator->lastItem() ?? 0 }}</strong>
            of
            <strong>{{ $paginator->total() }}</strong>
            results
        </div>

        <!-- Pagination Links -->
        <nav aria-label="Page navigation">
            <ul class="pagination pagination-sm mb-0">
                {{-- Previous --}}
                <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
                    @if ($paginator->onFirstPage())
                        <span class="page-link"><i class="fas fa-chevron-left"></i></span>
                    @else
                        <a class="page-link" href="{{ $paginator->previousPageUrl() }}">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    @endif
                </li>

                {{-- Page Numbers --}}
                @php
                    $currentPage = $paginator->currentPage();
                    $lastPage = $paginator->lastPage();
                    $start = max(1, $currentPage - 2);
                    $end = min($lastPage, $currentPage + 2);
                @endphp

                {{-- First page + dots --}}
                @if ($start > 1)
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->url(1) }}">1</a>
                    </li>
                    @if ($start > 2)
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    @endif
                @endif

                {{-- Page range --}}
                @for ($page = $start; $page <= $end; $page++)
                    @if ($page == $currentPage)
                        <li class="page-item active">
                            <span class="page-link">{{ $page }}</span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $paginator->url($page) }}">{{ $page }}</a>
                        </li>
                    @endif
                @endfor

                {{-- Dots + last page --}}
                @if ($end < $lastPage)
                    @if ($end < $lastPage - 1)
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    @endif
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->url($lastPage) }}">{{ $lastPage }}</a>
                    </li>
                @endif

                {{-- Next --}}
                <li class="page-item {{ !$paginator->hasMorePages() ? 'disabled' : '' }}">
                    @if ($paginator->hasMorePages())
                        <a class="page-link" href="{{ $paginator->nextPageUrl() }}">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    @else
                        <span class="page-link"><i class="fas fa-chevron-right"></i></span>
                    @endif
                </li>
            </ul>
        </nav>
    </div>
@endif
