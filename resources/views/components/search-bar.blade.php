@php
    $search = request('search');
@endphp
<form method="GET" class="mt-3 mb-4 d-flex align-items-center gap-2 " role="search" style="min-width:300px">
    <input type="search" name="search" class="form-control flex-grow-1" placeholder="{{ $placeholder ?? 'ابحث هنا ...' }}" value="{{ $search }}" autocomplete="off">
    <button type="submit" class="btn btn-outline-secondary">بحث</button>
    @if($search)
        <a href="{{ url()->current() }}" class="btn btn-outline-danger">مسح</a>
    @endif
</form>
