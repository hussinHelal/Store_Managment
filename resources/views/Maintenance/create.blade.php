@extends('layouts.app')

@section('content')

    <span class="text-center border border-1 rounded text-bold">اضافة جهاز للصيانة</span>
    <form action="{{ route('maintenance.store') }}" method="POST">
      @csrf
      @if ($errors->any())
          <div class="alert alert-danger">
              <ul class="mb-0">
                  @foreach ($errors->all() as $error)
                      <li>{{ $error }}</li>
                  @endforeach
              </ul>
          </div>
      @endif
      <div class="mb-3">
        <label for="name" class="form-label">اسم الجهاز</label>
        <input type="text" class="form-control" id="name" name="name" >
      </div>
      
      <div class="mb-3">
        <label for="owner" class="form-label">اسم المالك</label>
        <input type="text" class="form-control" id="owner" name="owner" >
      </div>
      
      <div class="mb-3">
        <label for="address" class="form-label">العنوان</label>
        <input type="text" class="form-control" id="address" name="address" >
      </div>
      
      <div class="mb-3">
        <label for="phone" class="form-label">التليفون</label>
        <input type="text" class="form-control" id="phone" name="phone" >
      </div>
      <div class="mb-3">
        <label for="requested_date" class="form-label">تاريخ دخول الصيانة</label>
        <input type="date" class="form-control" id="requested_date" name="requested_date" >
      </div>

      <div class="mb-3">
        <label for="status" class="form-label">الحالة</label>
        <select class="form-select" id="status" name="status">
          <option value="pending" >قيد الانتظار</option>
          <option value="completed" >مكتمل</option>
        </select>
      </div>

      <div class="mb-3">
        <label for="description" class="form-label">الوصف</label>
        <textarea class="form-control" id="description" name="description"> </textarea>
      </div>
      
      <button type="submit" class="btn btn-primary">اضافة</button>
    </form>
    
    <div class="mt-3">
      <a href="{{ route('maintenance.index') }}" class="btn btn-secondary">العودة</a>
    </div>
@endsection