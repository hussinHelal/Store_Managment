@extends('layouts.app')

@section('content')

    <span class="text-center border border-1 rounded text-bold">تعديل صنف</span>
    <form action="{{ route('categories.update', $category) }}" method="POST">
      @csrf
      @method('PUT')

      <div class="mb-3">
        <label for="name" class="form-label">الاسم</label>
        <input type="text" class="form-control" id="name" name="name" value="{{ $category->name }}">
      </div>

      @include('components.form-actions', ['submitLabel' => 'تحديث', 'backUrl' => route('categories.index')])
    </form>

@endsection
