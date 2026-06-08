@extends('layouts.app')
@section('title', ' - الأصناف')
@section('content')

<div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
    <div>
        <h1>الأصناف</h1>
        @include('components.search-bar', ['placeholder' => 'ابحث باسم الصنف'])
    </div>
    <a href="{{ route('categories.create') }}" class="btn btn-primary btn-round">
        <i class="fas fa-plus me-1"></i> صنف جديد
    </a>
</div>

<div class="row gx-3 gy-3 mb-4">
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm bg-body p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <p class="text-muted small mb-0">إجمالي الأصناف</p>
                <span class="badge bg-primary bg-opacity-10 text-primary">الصفحة الحالية</span>
            </div>
            <h2 class="fw-bold mb-1">{{ $categories->total() }}</h2>
            <p class="text-secondary mb-0">عرض {{ $categories->count() }} من أصل {{ $categories->total() }} صنف.</p>
        </div>
    </div>
</div>

<div class="table-wrapper table-responsive">
    <table class="table table-hover table-striped align-middle mb-0">
      <thead class="table-dark">
        <tr>
          <th scope="col">#</th>
          <th scope="col">الاسم</th>
          <th scope="col">الإجراءات</th>
        </tr>
      </thead>
      <tbody>
          @forelse($categories as $category)
        <tr>
          <th scope="row">{{ $category->id }}</th>
          <td>{{ $category->name }}</td>
          <td>
              <a href="{{ route('categories.edit', $category->id) }}" class="btn btn-sm btn-primary rounded">تعديل</a>
              <form action="{{ route('categories.destroy', $category->id) }}" method="POST" style="display: inline;">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-danger rounded">حذف</button>
              </form>
          </td>
        </tr>
        @empty
        <tr>
            <td colspan="3" class="text-center">لا توجد أصناف.</td>
        </tr>
        @endforelse
      </tbody>
    </table>

    @include('components.pagination', ['collection' => $categories])
</div>

@endsection
