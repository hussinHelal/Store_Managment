@php($viewErrors = $errors ?? collect())
@if(session('success') || session('error') || $viewErrors->isNotEmpty())
    <div class="mb-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($viewErrors->isNotEmpty())
            <div class="alert alert-warning alert-dismissible fade show rounded-3 shadow-sm" role="alert">
                <strong>حدثت الأخطاء التالية:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($viewErrors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    </div>
@endif

{{-- container for dynamic alerts injected by JS --}}
<div id="dynamic-alerts" class="mb-4"></div>
