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
          <option value="مرفوض">مرفوض</option>
        </select>
      </div>

      @include('components.form-actions', ['submitLabel' => 'حفظ الحالة', 'backUrl' => route('maintenance.index'), 'backLabel' => 'العودة'])
    </form>
@else
    <div class="alert alert-info">الحالة حالياً: {{ $maintenance->status }}</div>
    <div class="form-actions d-flex flex-wrap align-items-center gap-2 mt-3">
      <a href="{{ route('maintenance.index') }}" class="btn btn-outline-secondary">العودة</a>
    </div>
@endif
@endsection
