@extends('layouts.app')

@section('content')

    <table class="table table-borderless">
      <thead>
          <tr colspan="4">اعدادات الملف الشخصي</tr>
        <tr>
          <th scope="col">#</th>
          <th scope="col">الاسم</th>
          <th scope="col">البريد الإلكتروني</th>
          <th scope="col">الإجراءات</th>
        </tr>
      </thead>
      <tbody>
          @foreach($users as $user)
        <tr>
          <th scope="row">{{ $user->id }}</th>
          <td>{{ $user->name }}</td>
          <td>{{ $user->email }}</td>
          <td>
              <a href="{{ route('user.edit', $user->id) }}" class="btn btn-sm btn-primary">تعديل</a>
              <form action="{{ route('user.destroy', $user->id) }}" method="POST" style="display: inline;">
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