@extends('layouts.app')

@section('content')

    <span class="text-center border border-1 rounded text-bold">انشاء صنف</span>
    <form action="{{ route('categories.store') }}" method="POST">
      @csrf
      @if ($errors->any())
          <div class="alert alert-danger">
              <ul class="mb-0" style="list-style-type: none; padding: 0;">
                  @foreach ($errors->all() as $error)
                      <li>{{ $error }}</li>
                  @endforeach
              </ul>
          </div>
      @endif
      <div class="mb-3">
        <label for="name" class="form-label">الاسم</label>
        <input type="text" class="form-control" id="name" name="name">
      </div>
      
      <button type="submit" class="btn btn-primary">انشاء</button>
    </form>
    
     <div class="mt-3">
        <a href="{{ route('categories.index') }}" class="btn btn-danger">رجوع</a>
    </div>
    
@endsection