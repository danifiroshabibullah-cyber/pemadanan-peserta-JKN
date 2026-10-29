@if ($paginator->hasPages())
    <nav class="pager" role="navigation" aria-label="Navigasi halaman">
        <p class="pager-info">
            Menampilkan {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}
            dari {{ number_format($paginator->total(), 0, ',', '.') }} data
        </p>

        <ul class="pager-list">
            @if ($paginator->onFirstPage())
                <li><span class="off" aria-disabled="true">Sebelumnya</span></li>
            @else
                <li><a href="{{ $paginator->previousPageUrl() }}" rel="prev">Sebelumnya</a></li>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li><span class="off">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li><span class="on" aria-current="page">{{ $page }}</span></li>
                        @else
                            <li><a href="{{ $url }}" aria-label="Halaman {{ $page }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li><a href="{{ $paginator->nextPageUrl() }}" rel="next">Berikutnya</a></li>
            @else
                <li><span class="off" aria-disabled="true">Berikutnya</span></li>
            @endif
        </ul>
    </nav>
@endif
