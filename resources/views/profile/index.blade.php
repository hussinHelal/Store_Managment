@extends('layouts.app')
@section('content')

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-person-gear me-2"></i> إعدادات الملف الشخصي
                    </h5>
                </div>
                <div class="card-body text-center py-4">
                    <div class="avatar-circle bg-success text-white mx-auto mb-3">
                        {{ strtoupper(substr($profile->name, 0, 1)) }}
                    </div>
                    <h5 class="mb-1">{{ $profile->name ?? 'لا يوجد اسم' }}</h5>
                    <p class="text-muted mb-0">{{ $profile->email ?? 'لا يوجد بريد' }}</p>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2">
                    <a href="{{ route('profile.edit', $profile->id) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-pencil-square me-1"></i> تعديل
                    </a>
                    <form action="{{ route('profile.destroy', $profile->id) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                onclick="return confirm('هل أنت متأكد من الحذف؟')">
                            <i class="bi bi-trash me-1"></i> حذف
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .avatar-circle {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: 700;
    }
</style>

@endsection