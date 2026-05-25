@extends('layouts.app')
@section('title', '- الرئيسية')
@section('main')
<div class="container-fluid py-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4 gap-3">
        <div>
            <h1 class="h3 fw-bold text-body mb-1">لوحة التحكم</h1>
            <p class="text-muted small mb-0">نظرة عامة على أداء المتجر والروابط الأكثر استخدامًا.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('backup.export') }}" class="btn btn-success btn-round" title="تحميل نسخة احتياطية من البيانات">
                <i class="fas fa-download me-2"></i> تحميل نسخة احتياطية
            </a>
            <a href="{{ route('invoices.index') }}" class="btn btn-outline-primary btn-round">
                <i class="fas fa-file-invoice me-2"></i> عرض الفواتير
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        @foreach($cards as $card)
            <div class="col-12 col-sm-6 col-xl-3 shadow">
                <div class="card dashboard-card h-100 border-0 shadow-sm position-relative overflow-hidden" style="border-top: 4px solid {{ $card['color'] }};">
                    <div class="card-body py-4 px-3">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background-color: {{ $card['color'] }}20;">
                                <i class="fa-solid {{ $card['icon'] }} fs-5" style="color: {{ $card['color'] }}"></i>
                            </div>
                            <span class="badge rounded-pill fw-medium py-2 px-2" style="background-color: {{ $card['color'] }}15; color: {{ $card['color'] }}; font-size: 0.75rem;">
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
                        <p class="text-muted small mb-1">{{ $card['label'] }}</p>
                        <h3 class="fw-bold mb-0 text-body">{{ $card['value'] }}</h3>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-3">
        <div class="col-12 col-xl-8">
            <div class="card chart-card h-100 border-0 shadow-sm">
                <div class="card-header bg-white border-0 d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="mb-1">مخطط المبيعات الأسبوعية</h5>
                        <p class="text-muted small mb-0">إجمالي المبيعات لكل يوم خلال الأسبوع الماضي.</p>
                    </div>
                    <span class="badge bg-primary text-white">{{ number_format(array_sum($chartData), 2) }} جم إجمالي</span>
                </div>
                <div class="card-body p-4">
                    <div class="chart-container" style="min-height: 320px;">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-white border-0 pb-3">
                    <h5 class="mb-0">الوصول السريع</h5>
                </div>
                <div class="card-body p-4">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('categories.index') }}" class="list-group-item list-group-item-action rounded-3 mb-2">التصنيفات</a>
                        <a href="{{ route('products.index') }}" class="list-group-item list-group-item-action rounded-3 mb-2">المنتجات</a>
                        <a href="{{ route('maintenance.index') }}" class="list-group-item list-group-item-action rounded-3 mb-2">الصيانة</a>
                        <a href="{{ route('invoices.index') }}" class="list-group-item list-group-item-action rounded-3 mb-2">الفواتير</a>
                        <a href="{{ route('customers.index') }}" class="list-group-item list-group-item-action rounded-3 mb-2">العملاء</a>
                        <a href="{{ route('installments.index') }}" class="list-group-item list-group-item-action rounded-3">الديون</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('salesChart');
        if (!ctx) return;

        const salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($chartLabels),
                datasets: [{
                    label: 'المبيعات',
                    data: @json($chartData),
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.18)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 4,
                    pointBackgroundColor: '#0d6efd',
                    pointBorderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#6c757d' }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(108, 117, 125, 0.15)' },
                        ticks: { color: '#6c757d' }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return context.dataset.label + ': ' + context.formattedValue + ' ج.م';
                            }
                        }
                    }
                }
            }
        });
    });
</script>

<style>
    .dashboard-card {
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        border-radius: 1rem;
        background: #ffffff;
        box-shadow: 0 14px 38px rgba(15, 23, 42, 0.06);
    }
    .dashboard-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 22px 48px rgba(15, 23, 42, 0.12) !important;
    }
    .chart-card {
        border-radius: 1rem;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        box-shadow: 0 24px 55px rgba(15, 23, 42, 0.08);
    }
    .chart-card .card-header {
        background: transparent;
    }
    .chart-container {
        min-height: 320px;
    }
    .btn-round {
        border-radius: 0.75rem;
    }
</style>

@endsection
