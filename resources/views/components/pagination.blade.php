@if(isset($collection) && method_exists($collection, 'links'))
    <div class="d-flex justify-content-end mt-3">
        {{ $collection->withQueryString()->links() }}
    </div>
@endif
