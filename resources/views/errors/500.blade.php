@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="text-center">
        <h1 class="display-4">500</h1>
        <p class="lead">حدث خطأ في الخادم. نأسف للخطأ، سنعمل على إصلاحه قريباً.</p>
        <a href="{{ route('home') }}" class="btn btn-primary">الرجوع إلى الرئيسية</a>
    </div>
</div>
@endsection
