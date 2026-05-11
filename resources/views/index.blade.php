@extends('layouts.app')

@section('main')
<div>
    <h1 class="text-2xl font-medium text-gray-800 dark:text-white mb-1">الرئيسية</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">نظرة عامة</p>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-3 rounded m-2" >

        @php
        $cards = [
            ['label' => 'إجمالي المبيعات', 'value' => '245,890', 'badge' => '+12.5% هذا الشهر', 'icon' => 'fa-money-bill-wave', 'trend' => 'up', 'color' => 'emerald'],
            ['label' => 'التصنيفات',        'value' => '48',       'badge' => 'تصنيف نشط',        'icon' => 'fa-list',           'trend' => null,  'color' => 'violet'],
            ['label' => 'العملاء',          'value' => '1,284',    'badge' => '+28 هذا الشهر',    'icon' => 'fa-users',          'trend' => 'up',  'color' => 'blue'],
            ['label' => 'إجمالي الديون',    'value' => '87,450',   'badge' => '-10.2% هذا الشهر', 'icon' => 'fa-credit-card',    'trend' => 'down','color' => 'red'],
            ['label' => 'فواتير اليوم',     'value' => '37',       'badge' => 'فاتورة اليوم',     'icon' => 'fa-receipt',        'trend' => null,  'color' => 'amber'],
        ];

        $colors = [
            'emerald' => ['bg' => 'bg-emerald-50',  'icon' => 'text-emerald-600',  'badge' => 'bg-emerald-50 text-emerald-700'],
            'violet'  => ['bg' => 'bg-violet-50',   'icon' => 'text-violet-600',   'badge' => 'bg-violet-50 text-violet-700'],
            'blue'    => ['bg' => 'bg-blue-50',      'icon' => 'text-blue-600',     'badge' => 'bg-blue-50 text-blue-700'],
            'red'     => ['bg' => 'bg-red-50',       'icon' => 'text-red-600',      'badge' => 'bg-red-50 text-red-700'],
            'amber'   => ['bg' => 'bg-amber-50',     'icon' => 'text-amber-600',    'badge' => 'bg-amber-50 text-amber-700'],
        ];
        @endphp

        @foreach($cards as $card)
        @php $c = $colors[$card['color']]; @endphp
        <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-xl p-5 flex flex-col gap-3 hover:shadow-sm transition-shadow m-2 rounded " >
            
            <div class="w-10 h-10 {{ $c['bg'] }} rounded-lg flex items-center justify-center">
                <i class="fa-solid {{ $card['icon'] }} {{ $c['icon'] }}"></i>
            </div>

            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ $card['label'] }}</p>
                <p class="text-2xl font-medium text-gray-800 dark:text-white">{{ $card['value'] }}</p>
            </div>

            <div class="flex items-center gap-1 px-2 py-1 {{ $c['badge'] }} rounded-md w-fit text-xs font-medium">
                @if($card['trend'] === 'up')
                    <i class="fa-solid fa-arrow-trend-up text-xs"></i>
                @elseif($card['trend'] === 'down')
                    <i class="fa-solid fa-arrow-trend-down text-xs"></i>
                @else
                    <i class="fa-solid fa-check text-xs"></i>
                @endif
                {{ $card['badge'] }}
            </div>

        </div>
        @endforeach

    </div>
</div>
@endsection