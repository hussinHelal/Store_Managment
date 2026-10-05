@extends(auth()->check() ? 'layouts.app' : 'layouts.error-guest')

@section('content')
    @include('errors.page')
@endsection