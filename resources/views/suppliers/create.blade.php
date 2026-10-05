@extends('layouts.app')
@section('title', ' - إضافة مورد')

@section('content')
<div class="container py-3" style="max-width: 52rem">
    <div class="mb-4">
        <a href="{{ route('suppliers.index') }}" class="link-secondary text-decoration-none">الموردون</a>
        <h1 class="h3 mt-2">إضافة مورد</h1>
    </div>
    <form method="POST" action="{{ route('suppliers.store') }}">
        @csrf
        @include('suppliers._form', ['supplier' => null])
        <div class="form-actions d-flex flex-wrap align-items-center gap-2 mt-3">
            <button type="submit" class="btn btn-primary">حفظ المورد</button>
            <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary">إلغاء</a>
        </div>
    </form>
</div>
@endsection
