@extends('layouts.app')

@section('title', '- الملف الشخصي')

@section('content')
@php
    $photoUrl = null;
    if ($profile->photo) {
        $photoUrl = str_contains($profile->photo, '/')
            ? asset('storage/'.$profile->photo)
            : asset('uploads/users/'.$profile->photo);
    }
@endphp
<div class="container py-4" style="max-width: 60rem">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">الملف الشخصي</h1>
            <p class="text-body-secondary mb-0">معلومات حسابك الشخصي.</p>
        </div>
        <a href="{{ route('profile.edit', $profile) }}" class="btn btn-primary">
            <i class="fa-solid fa-pen me-1" aria-hidden="true"></i> تعديل الملف
        </a>
    </div>

    <section class="border rounded-3 overflow-hidden bg-body shadow-sm" aria-label="بيانات الملف الشخصي">
        <div class="p-4 p-md-5 bg-body-tertiary border-bottom">
            <div class="d-flex flex-column flex-sm-row align-items-center gap-4 text-center text-sm-start">
                <div class="flex-shrink-0">
            @if($photoUrl)
                        <img src="{{ $photoUrl }}" alt="الصورة الشخصية" class="rounded-circle border border-3 border-white shadow-sm" width="112" height="112" style="object-fit: cover">
            @else
                        <div class="rounded-circle bg-primary-subtle text-primary border border-3 border-white shadow-sm d-flex align-items-center justify-content-center fw-semibold" style="width: 112px; height: 112px; font-size: 2.5rem" aria-hidden="true">
                    @if($profile->name)
                        {{ mb_strtoupper(mb_substr($profile->name, 0, 1)) }}
                    @else
                        <i class="fa-solid fa-user" aria-hidden="true"></i>
                    @endif
                </div>
            @endif
                </div>
                <div class="min-w-0">
                    <h2 class="h4 mb-2">{{ $profile->name ?: $profile->username }}</h2>
                    <p class="text-body-secondary mb-3" dir="ltr">{{ '@' . $profile->username }}</p>
                    <span class="badge rounded-pill text-bg-primary px-3 py-2">
                        <i class="fa-solid fa-shield-halved me-1" aria-hidden="true"></i>
                        {{ $profile->getRoleNames()->join(', ') ?: ucfirst($profile->role) }}
                    </span>
                </div>
            </div>
        </div>

        <div class="p-4 p-md-5">
            <h3 class="h6 text-body-secondary mb-3">تفاصيل الحساب</h3>
            <dl class="row g-0 mb-0">
                <div class="col-12 col-md-6 border-top py-3 px-md-3">
                    <dt class="small fw-normal text-body-secondary mb-1"><i class="fa-solid fa-user me-2" aria-hidden="true"></i>الاسم</dt>
                    <dd class="mb-0 fw-semibold">{{ $profile->name ?: '—' }}</dd>
                </div>
                <div class="col-12 col-md-6 border-top py-3 px-md-3">
                    <dt class="small fw-normal text-body-secondary mb-1"><i class="fa-solid fa-at me-2" aria-hidden="true"></i>اسم المستخدم</dt>
                    <dd class="mb-0 fw-semibold" dir="ltr">{{ $profile->username }}</dd>
                </div>
                <div class="col-12 col-md-6 border-top py-3 px-md-3">
                    <dt class="small fw-normal text-body-secondary mb-1"><i class="fa-solid fa-user-tag me-2" aria-hidden="true"></i>الدور</dt>
                    <dd class="mb-0 fw-semibold">{{ $profile->getRoleNames()->join(', ') ?: ucfirst($profile->role) }}</dd>
                </div>
                <div class="col-12 col-md-6 border-top py-3 px-md-3">
                    <dt class="small fw-normal text-body-secondary mb-1"><i class="fa-regular fa-calendar me-2" aria-hidden="true"></i>تاريخ إنشاء الحساب</dt>
                    <dd class="mb-0 fw-semibold" dir="ltr">{{ $profile->created_at?->format('Y-m-d') ?: '—' }}</dd>
                </div>
            </dl>
        </div>
    </section>
</div>
@endsection
