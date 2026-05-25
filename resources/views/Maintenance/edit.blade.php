@extends('layouts.app')

@section('content')

    <span class="text-center border border-1 rounded text-bold">تعديل بيانات الجهاز</span>
    <form action="{{ route('maintenance.update', $maintenance->id) }}" method="POST">
      @csrf
      @method('PUT')

      <div class="mb-3">
        <label for="name" class="form-label">اسم الجهاز</label>
        <input type="text" class="form-control" id="name" name="name" value="{{ $maintenance->name }}">
      </div>

      <div class="mb-3">
        <label for="owner" class="form-label">اسم المالك</label>
        <input type="text" class="form-control" id="owner" name="owner" value="{{ $maintenance->owner }}">
      </div>

      <div class="mb-3">
        <label for="address" class="form-label">العنوان</label>
        <input type="text" class="form-control" id="address" name="address" value="{{ $maintenance->address }}">
      </div>

      <div class="mb-3">
        <label for="phone" class="form-label">التليفون</label>
        <input type="text" class="form-control" id="phone" name="phone" value="{{ $maintenance->phone }}">
      </div>

      <div class="mb-3">
        <label for="status" class="form-label">الحالة</label>
        <select class="form-select" id="status" name="status">
          <option value="قيد الانتظار" {{ $maintenance->status == 'قيد الانتظار' ? 'selected' : '' }}>قيد الانتظار</option>
          <option value="مكتمل" {{ $maintenance->status == 'مكتمل' ? 'selected' : '' }}>مكتمل</option>
        </select>
      </div>

      <div class="mb-3">
        <label for="description" class="form-label">الوصف</label>
        <textarea class="form-control" id="description" name="description">{{ $maintenance->description }}</textarea>
      </div>

      <button type="submit" class="btn btn-primary">تحديث</button>
    </form>
     <div class="mt-3">
        <a href="{{ route('maintenance.index') }}" class="btn btn-danger">رجوع</a>
    </div>

@endsection
