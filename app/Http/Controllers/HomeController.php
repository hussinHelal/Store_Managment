<?php

namespace App\Http\Controllers;

use App\Models\invoice;
use App\Models\products;
use App\Models\category;
use App\Models\customers;
use App\Models\installments;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $totalSales = invoice::where('status', '!=', 'refunded')->sum('total_amount');
        $categoriesCount = category::count();
        $customersCount = customers::count();
        $totalInstallments = Installments::sum('paid_amount');
        $todayInvoices = invoice::where('status', '!=', 'refunded')
            ->whereDate('invoice_date', now())
            ->count();
        $productsCount = products::count();

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
                'label' => 'التصنيفات',
                'value' => number_format($categoriesCount),
                'badge' => 'عدد التصنيفات',
                'icon' => 'fa-list',
                'trend' => null,
                'color' => '#8b5cf6',
            ],
            [
                'label' => 'العملاء',
                'value' => number_format($customersCount),
                'badge' => 'عملاء مسجلين',
                'icon' => 'fa-users',
                'trend' => 'up',
                'color' => '#3b82f6',
            ],
            [
                'label' => 'إجمالي الديون',
                'value' => number_format($totalInstallments, 2),
                'badge' => 'رصيد الدين المتبقي',
                'icon' => 'fa-credit-card',
                'trend' => 'down',
                'color' => '#ef4444',
            ],
            [
                'label' => 'فواتير اليوم',
                'value' => number_format($todayInvoices),
                'badge' => 'فاتورة اليوم',
                'icon' => 'fa-receipt',
                'trend' => null,
                'color' => '#f59e0b',
            ],
            [
                'label' => 'المنتجات',
                'value' => number_format($productsCount),
                'badge' => 'منتج متاح',
                'icon' => 'fa-box',
                'trend' => null,
                'color' => '#3b82f6',
            ],
        ];

        return view('index', compact('cards'));
    }
}
