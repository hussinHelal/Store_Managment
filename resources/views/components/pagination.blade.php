@if(isset($collection) && method_exists($collection, 'links'))
    @if($collection->hasPages())
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 mt-3">
            <p class="small text-body-secondary mb-0" aria-live="polite">
                عرض {{ $collection->firstItem() ?? 0 }} إلى {{ $collection->lastItem() ?? 0 }} من {{ $collection->total() }} {{ $paginationLabel ?? 'سجل' }}
            </p>
            <div class="pagination-links" dir="rtl">
                {{ $collection->onEachSide(1)->withQueryString()->links() }}
            </div>
        </div>
    @endif
@endif
