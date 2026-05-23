@extends('layouts.app')

@section('content')

    <span class="text-center border border-1 rounded text-bold">تعديل العميل</span>
    <form action="{{ route('customers.update', $customers->id) }}" method="POST">
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
        <label for="name" class="form-label">الاسم</label>
        <input type="text" class="form-control" id="name" name="name" value="{{ $customers->name }}">
      </div>
      
      <div class="mb-3">
        <label for="phone" class="form-label">التليفون</label>
        <input type="text" class="form-control" id="phone" name="phone" value="{{ $customers->phone }}">
      </div>
      
      <div class="mb-3">
        <label for="address" class="form-label">العنوان</label>
        <input type="text" class="form-control" id="address" name="address" value="{{ $customers->address }}">
      </div>
      
      <button type="submit" class="btn btn-primary">تحديث</button>
    </form>
       <div class="mt-3">
        <a href="{{ route('customers.index') }}" class="btn btn-danger">رجوع</a>
    </div>
    
@endsection