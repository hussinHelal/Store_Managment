<?php

namespace App\Http\Controllers;

use App\Models\invoice;
use App\Models\products;
use App\Models\category;
use App\Models\customers;
use App\Models\installments;
use App\Models\Maintenance;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class HomeController extends Controller
{
    public function index()
    {
        // Make the controller resilient when running in environments
        // where migrations haven't been run (tests, CI, fresh clones).
        $totalSales = 0;
        $categoriesCount = 0;
        $customersCount = 0;
        $totalInstallments = 0;
        $todayInvoices = 0;
        $productsCount = 0;
        $invoicesCount = 0;
        $maintenanceCount = 0;
        $soldQuantity = 0;

        if (Schema::hasTable((new invoice)->getTable())) {
            $totalSales = invoice::where('status', '!=', 'refunded')->sum('total_amount');
            $todayInvoices = invoice::where('status', '!=', 'refunded')
                ->whereDate('invoice_date', now())
                ->count();
            $invoicesCount = invoice::where('status', '!=', 'refunded')->count();
            $soldQuantity = invoice::where('status', '!=', 'refunded')->sum('quantity');
        }

        if (Schema::hasTable((new category)->getTable())) {
            $categoriesCount = category::count();
        }

        if (Schema::hasTable((new customers)->getTable())) {
            $customersCount = customers::count();
        }

        if (Schema::hasTable((new installments)->getTable())) {
            $totalInstallments = installments::sum('paid_amount');
        }

        if (Schema::hasTable((new products)->getTable())) {
            $productsCount = products::count();
        }

        if (Schema::hasTable((new Maintenance)->getTable())) {
            $maintenanceCount = Maintenance::count();
        }

        $cards = [
            [
                'label' => 'إجمالي المبيعات',
                'value' => number_format($totalSales, 2),
                'badge' => 'بعد خصم المرتجعات',
                'icon' => 'fa-money-bill-wave',
                'trend' => 'up',
                'color' => '#10b981',
            ],
            [
                'label' => 'الفواتير',
                'value' => number_format($invoicesCount),
                'badge' => 'إجمالي الفواتير',
                'icon' => 'fa-file-invoice',
                'trend' => null,
                'color' => '#0ea5e9',
            ],
            [
                'label' => 'المنتجات',
                'value' => number_format($productsCount),
                'badge' => 'منتج متاح',
                'icon' => 'fa-box',
                'trend' => null,
                'color' => '#3b82f6',
            ],
            [
                'label' => 'العملاء',
                'value' => number_format($customersCount),
                'badge' => 'عملاء مسجلين',
                'icon' => 'fa-users',
                'trend' => 'up',
                'color' => '#8b5e3e',
            ],
            [
                'label' => 'الديون',
                'value' => number_format($totalInstallments, 2),
                'badge' => 'رصيد الدين المتبقي',
                'icon' => 'fa-credit-card',
                'trend' => 'down',
                'color' => '#ef4444',
            ],
            [
                'label' => 'الصيانة',
                'value' => number_format($maintenanceCount),
                'badge' => 'طلبات الصيانة',
                'icon' => 'fa-wrench',
                'trend' => null,
                'color' => '#f97316',
            ],
            [
                'label' => 'المبيعات',
                'value' => number_format($soldQuantity),
                'badge' => 'قطع مباعة',
                'icon' => 'fa-chart-line',
                'trend' => 'up',
                'color' => '#14b8a6',
            ],
            [
                'label' => 'فواتير اليوم',
                'value' => number_format($todayInvoices),
                'badge' => 'فواتير اليوم',
                'icon' => 'fa-receipt',
                'trend' => null,
                'color' => '#f59e0b',
            ],
        ];

        $lastSevenDays = collect();
        for ($i = 6; $i >= 0; $i--) {
            $lastSevenDays->push(now()->subDays($i)->format('Y-m-d'));
        }

        if (Schema::hasTable((new invoice)->getTable())) {
            $dailySales = invoice::where('status', '!=', 'refunded')
                ->whereBetween('invoice_date', [now()->subDays(6)->startOfDay(), now()->endOfDay()])
                ->selectRaw('DATE(invoice_date) as date, SUM(total_amount) as total')
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('total', 'date');
        } else {
            $dailySales = collect();
        }

        $chartLabels = $lastSevenDays->map(fn($date) => Carbon::parse($date)->format('d M'))->all();
        $chartData = $lastSevenDays->map(fn($date) => $dailySales->get($date, 0))->all();

        return view('index', compact('cards', 'chartLabels', 'chartData'));
    }
}
