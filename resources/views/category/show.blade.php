@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width: 48rem">
    <div class="mb-4">
        <a href="{{ route('categories.index') }}" class="link-secondary text-decoration-none"><i class="fa-solid fa-arrow-right me-1" aria-hidden="true"></i> التصنيفات</a>
        <h1 class="h3 mt-2">{{ $category->name }}</h1>
    </div>
    <p class="text-body-secondary">عدد المنتجات: {{ $category->products_count }}</p>
    <a href="{{ route('categories.edit', $category) }}" class="btn btn-outline-primary"><i class="fa-solid fa-pen me-1" aria-hidden="true"></i> تعديل</a>
</div>
@endsection