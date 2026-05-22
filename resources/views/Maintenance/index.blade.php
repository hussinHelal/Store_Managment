@extends('layouts.app')

@section('content')
    
<button class="btn btn-primary"><a href="{{ route('maintenance.create') }}" class="text-white nav-link"><i class="fas fa-plus"></i>جهاز جديد</a></button>

<div class="row justify-content-center">
    <span class="fw-bold text-body text-center">الصيانة</span>
</div>
<table class="table  table-hover table-striped ">
      <thead class="table-dark">
          <tr>
            <th scope="col">#</th>
            <th scope="col">اسم الجهاز</th>
            <th scope="col">الوصف</th>
            <th scope="col">المالك</th>
            <th scope="col">عنوان المالك</th>
            <th scope="col">تليفون</th>
            <th scope="col">الحالة</th>
            <th scope="col">تاريخ الدخول</th>
            <th scope="col">تاريخ الخروج</th>
            <th scope="col">الإجراءات</th>
        </tr>
      </thead>
      <tbody>
          @foreach($maintenances as $maintenance)
        <tr>
          <th scope="row">{{ $maintenance->id }}</th>
          <td>{{ $maintenance->name }}</td>
          <td>{{ $maintenance->description }}</td>
          <td>{{ $maintenance->owner }}</td>
          <td>{{ $maintenance->address }}</td>
          <td>{{ $maintenance->phone }}</td>
          <td>{{ $maintenance->status }}</td>
          <td>{{\Carbon\Carbon::parse($maintenance->requested_date)->format('Y-m-d') }}</td>
          <td>{{\Carbon\Carbon::parse($maintenance->completed_date)->format('Y-m-d') }}</td>
          <td>
              <a href="{{ route('maintenance.edit', $maintenance->id) }}" class="btn btn-sm btn-primary">تعديل</a>
              <a href="{{ route('maintenance.showRepaired', $maintenance->id) }}" class="btn btn-sm btn-primary">تغيير الحالة</a>
              <form action="{{ route('maintenance.destroy', $maintenance->id) }}" method="POST" style="display: inline;">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من حذف هذا الجهاز    ؟')">حذف</button>
              </form>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    
@endsection