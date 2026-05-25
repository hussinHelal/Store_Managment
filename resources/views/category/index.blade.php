@extends('layouts.app')
@section('title', ' - الأصناف')
@section('content')

<div class="page-header">
    <h1>الأصناف</h1>
    <a href="{{ route('categories.create') }}" class="btn btn-primary btn-round">
        <i class="fas fa-plus me-1"></i> صنف جديد
    </a>
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
