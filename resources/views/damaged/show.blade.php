@extends('layouts.app')

@section('main')
    @php
        $money = fn ($value) => number_format((float) $value, 2);
        $full = app(\App\Services\PictureStorageService::class)->url($item->image_path, 'damaged', false);
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h3 class="mb-0 d-flex align-items-center gap-2">
            <span>تفاصيل الهالِك #{{ $item->id }}</span>
            @if ($item->isVoided())
                <span class="badge text-bg-secondary fs-6">ملغي</span>
            @else
                <span class="badge text-bg-success fs-6">مسجَّل</span>
            @endif
        </h3>
        <div class="d-flex gap-2 d-print-none">
            <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                <i class="fa-solid fa-print" aria-hidden="true"></i> طباعة
            </button>
            <a href="{{ route('damaged.index') }}" class="btn btn-outline-secondary">رجوع</a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    @if ($item->isVoided())
        <div class="alert alert-secondary">
            <strong>تم إلغاء هذا السجل</strong> بتاريخ {{ $item->voided_at?->format('Y-m-d H:i') }}.
            السبب: {{ $item->void_reason }}.
            @unless ($item->product_id)
                <div class="small mt-1">المنتج محذوف من النظام، لذلك لم تُعَد الكمية إلى المخزون.</div>
            @endunless
        </div>
    @endif

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">المنتج</dt>
                        <dd class="col-sm-8 fw-semibold">
                            {{ $item->product_name }}
                            @if ($item->product)
                                @can('page.products.view')
                                    <a href="{{ route('products.show', $item->product_id) }}" class="small d-print-none">(عرض المنتج)</a>
                                @endcan
                            @else
                                <span class="badge text-bg-light border text-dark">محذوف</span>
                            @endif
                        </dd>

                        @if ($item->product_barcode)
                            <dt class="col-sm-4">الباركود</dt>
                            <dd class="col-sm-8" dir="ltr">{{ $item->product_barcode }}</dd>
                        @endif

                        <dt class="col-sm-4">الكمية الهالكة</dt>
                        <dd class="col-sm-8">{{ $item->quantity }} قطعة</dd>

                        <dt class="col-sm-4">السبب</dt>
                        <dd class="col-sm-8">{{ $item->reasonLabel() }}</dd>

                        <dt class="col-sm-4">قيمة الوحدة</dt>
                        <dd class="col-sm-8">{{ $money($item->unit_value) }} ج.م</dd>

                        <dt class="col-sm-4">إجمالي القيمة</dt>
                        <dd class="col-sm-8 fw-bold text-danger">{{ $money($item->total_value) }} ج.م</dd>

                        <dt class="col-sm-4">المخزون قبل / بعد</dt>
                        <dd class="col-sm-8">{{ $item->stock_before }} ← {{ $item->stock_after }}</dd>

                        <dt class="col-sm-4">تاريخ الهالِك</dt>
                        <dd class="col-sm-8">{{ $item->damaged_on->format('Y-m-d') }}</dd>

                        <dt class="col-sm-4">سُجِّل بواسطة</dt>
                        <dd class="col-sm-8">{{ $item->created_by_name ?? '-' }} &middot; {{ $item->created_at->format('Y-m-d H:i') }}</dd>

                        @if ($item->notes)
                            <dt class="col-sm-4">ملاحظات</dt>
                            <dd class="col-sm-8">{{ $item->notes }}</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            @if ($full)
                <div class="card shadow-sm mb-3">
                    <a href="{{ $full }}" target="_blank" rel="noopener">
                        <img src="{{ $full }}" alt="صورة الهالِك" class="card-img-top" onerror="this.closest('.card').remove()">
                    </a>
                </div>
            @endif

            @can('page.damaged.manage')
                @unless ($item->isVoided())
                    <div class="card shadow-sm border-danger d-print-none">
                        <div class="card-body">
                            <h5 class="card-title">إلغاء السجل</h5>
                            <p class="small text-body-secondary">
                                استخدمه فقط لو سجّلت الهالِك بالخطأ. تعود الكمية إلى المخزون ويبقى السجل ظاهراً كملغي.
                            </p>
                            <button type="button" class="btn btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#voidModal">
                                إلغاء وإرجاع الكمية
                            </button>
                        </div>
                    </div>
                @endunless
            @endcan
        </div>
    </div>

    @can('page.damaged.manage')
        @unless ($item->isVoided())
            <div class="modal fade" id="voidModal" tabindex="-1" aria-labelledby="voidTitle" aria-hidden="true">
                <div class="modal-dialog">
                    <form method="POST" action="{{ route('damaged.void', $item) }}" class="modal-content">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="voidTitle">إلغاء سجل الهالِك</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                        </div>
                        <div class="modal-body">
                            <p>سيتم إرجاع <strong>{{ $item->quantity }}</strong> قطعة من «{{ $item->product_name }}» إلى المخزون.</p>
                            <label for="voidReason" class="form-label">سبب الإلغاء</label>
                            <input type="text" id="voidReason" name="reason" class="form-control" minlength="3" maxlength="255" required>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">تراجع</button>
                            <button type="submit" class="btn btn-danger">تأكيد الإلغاء</button>
                        </div>
                    </form>
                </div>
            </div>
        @endunless
    @endcan
@endsection
