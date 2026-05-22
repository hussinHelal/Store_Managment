@extends('layouts.app')

@section('content')

    <span class="text-center border border-1 rounded text-bold">تغيير الحالة</span>
    <form action="{{ route('maintenance.repaired', $maintenance) }}" method="POST">
      @csrf
      @method('PUT')
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
        <label for="status" class="form-label">الحالة</label>
        <select class="form-select" id="status" name="status">
          <option value="pending" >قيد الانتظار</option>
          <option value="completed" >مكتمل</option>
        </select>
      </div>
      
      <button type="submit" class="btn btn-primary">مكتمل</button>
    </form>
    
    
    <div class="mt-3">
      <a href="{{ route('maintenance.index') }}" class="btn btn-secondary">العودة</a>
    </div>
@endsection