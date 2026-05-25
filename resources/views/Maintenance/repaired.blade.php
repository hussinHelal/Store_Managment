@extends('layouts.app')

@section('content')

    <span class="text-center border border-1 rounded text-bold">تغيير الحالة</span>
@if($maintenance->status == 'قيد الانتظار')
    <form action="{{ route('maintenance.repaired', $maintenance) }}" method="POST">
      @csrf
      @method('PUT')

      <div class="mb-3">
        <label for="status" class="form-label">الحالة</label>
        <select class="form-select" id="status" name="status">
          <option value="قيد الانتظار" selected>قيد الانتظار</option>
          <option value="مكتمل">مكتمل</option>
        </select>
      </div>

      <button type="submit" class="btn btn-primary">مكتمل</button>
    </form>
@else
    <div class="alert alert-info">الحالة حالياً: {{ $maintenance->status }}</div>
@endif


    <div class="mt-3">
      <a href="{{ route('maintenance.index') }}" class="btn btn-secondary">العودة</a>
    </div>
@endsection
