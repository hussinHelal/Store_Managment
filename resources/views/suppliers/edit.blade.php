@extends('layouts.app')
@section('title', ' - تعديل المورد')

@section('content')
<div class="container py-3" style="max-width: 52rem">
    <div class="mb-4">
        <a href="{{ route('suppliers.show', $supplier) }}" class="link-secondary text-decoration-none">{{ $supplier->name }}</a>
        <h1 class="h3 mt-2">تعديل بيانات المورد</h1>
    </div>
    <form method="POST" action="{{ route('suppliers.update', $supplier) }}">
        @csrf
        @method('PUT')
        @include('suppliers._form', ['supplier' => $supplier])
        <div class="form-actions d-flex flex-wrap align-items-center gap-2 mt-3">
            <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
            <a href="{{ route('suppliers.show', $supplier) }}" class="btn btn-outline-secondary">إلغاء</a>
        </div>
    </form>
</div>
@endsection
