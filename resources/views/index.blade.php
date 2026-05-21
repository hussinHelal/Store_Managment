@extends('layouts.app')

@section('main')
<div class="container-fluid py-4">
    <h1 class="h3 fw-bold text-body mb-1">الرئيسية</h1>
    <p class="text-muted small mb-4">نظرة عامة</p>

    <div class="row g-3">
        {{-- @php
        $cards = [
            ['label' => 'إجمالي المبيعات', 'value' => '245,890', 'badge' => '+12.5% هذا الشهر', 'icon' => 'fa-money-bill-wave', 'trend' => 'up', 'color' => '#10b981'],
            ['label' => 'التصنيفات',        'value' => '48',        'badge' => 'تصنيف نشط',        'icon' => 'fa-list',            'trend' => null,  'color' => '#8b5cf6'],
            ['label' => 'العملاء',          'value' => '1,284',     'badge' => '+28 هذا الشهر',    'icon' => 'fa-users',           'trend' => 'up',  'color' => '#3b82f6'],
            ['label' => 'إجمالي الديون',    'value' => '87,450',    'badge' => '-10.2% هذا الشهر', 'icon' => 'fa-credit-card',     'trend' => 'down','color' => '#ef4444'],
            ['label' => 'فواتير اليوم',     'value' => '37',        'badge' => 'فاتورة اليوم',     'icon' => 'fa-receipt',         'trend' => null,  'color' => '#f59e0b'],
            ['label' => 'المنتجات',     'value' => '100',        'badge' => '100 منتج',     'icon' => 'fa-receipt',         'trend' => null,  'color' => '#3b82f6'],
        ];
        @endphp --}}

        @foreach($cards as $card)
        <div class="col-12 col-md-6 col-xl-4 col-xxl-3 ">
            <div class="card h-100 border-0 shadow-sm position-relative overflow-hidden " style="border-top: 4px solid {{ $card['color'] }} !important;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-3 ">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" 
                             style="width: 40px; height: 40px; background-color: {{ $card['color'] }}20;">
                            <i class="fa-solid {{ $card['icon'] }}" style="color: {{ $card['color'] }}"></i>
                        </div>
                        
                        <span class="badge rounded-pill fw-medium py-2 px-2 " 
                              style="background-color: {{ $card['color'] }}15; color: {{ $card['color'] }}; font-size: 0.7rem;">
                            @if($card['trend'] === 'up')
                                <i class="fa-solid fa-arrow-trend-up me-1"></i>
                            @elseif($card['trend'] === 'down')
                                <i class="fa-solid fa-arrow-trend-down me-1"></i>
                            @else
                                <i class="fa-solid fa-check me-1"></i>
                            @endif
                            {{ $card['badge'] }}
                        </span>
                    </div>

                    <div>
                        <p class="text-muted small mb-1">{{ $card['label'] }}</p>
                        <h3 class="fw-bold mb-0 text-body">{{ $card['value'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<style>
    /* Smooth hover effect */
    .card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
    }
</style>

@endsection