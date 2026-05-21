@extends('layouts.app')

@section('content')

    <button class="btn btn-primary"><a href="{{ route('categories.create') }}" class="text-white nav-link"><i class="fas fa-plus"></i> صنف جديد</a></button> <br>
    <div class="row justify-content-center">
        <h3 class="fw-bold text-body text-center">الأصناف</h3>
    </div>
    <table class="table table-hover table-striped ">
      <thead class="table-dark">
          
        <tr>
          <th scope="col">#</th>
          <th scope="col">الاسم</th>
          <th scope="col">الإجراءات</th>
        </tr>
      </thead>
      <tbody>
          @foreach($categories as $category)
        <tr>
          <th scope="row">{{ $category->id }}</th>
          <td>{{ $category->name }}</td>
          <td>
              <a href="{{ route('categories.edit', $category->id) }}" class="btn btn-sm btn-primary">تعديل</a>
              <form action="{{ route('categories.destroy', $category->id) }}" method="POST" style="display: inline;">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-danger">حذف</button>
              </form>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    
@endsection