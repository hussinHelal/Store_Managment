@extends('layouts.app')

@section('content')

    <button class="btn btn-primary"><a href="{{ route('category.create') }}" class="text-white"><i class="fas fa-plus"></i> صنف جديد</a></button>
    <table class="table table-borderless">
      <thead>
          <tr colspan="4">الاصناف</tr>
        <tr>
          <th scope="col">#</th>
          <th scope="col">الاسم</th>
          <th scope="col">الوصف</th>
          <th scope="col">الإجراءات</th>
        </tr>
      </thead>
      <tbody>
          @foreach($categories as $category)
        <tr>
          <th scope="row">{{ $category->id }}</th>
          <td>{{ $category->name }}</td>
          <td>{{ $category->description }}</td>
          <td>
              <a href="{{ route('category.edit', $category->id) }}" class="btn btn-sm btn-primary">تعديل</a>
              <form action="{{ route('category.destroy', $category->id) }}" method="POST" style="display: inline;">
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