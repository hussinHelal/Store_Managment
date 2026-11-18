@extends('layouts.app')

@section('content')
<div class="container-fluid py-3" dir="rtl">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div><h1 class="h3 mb-1">تقارير المبيعات</h1><p class="text-body-secondary mb-0">ملخص المبيعات والمرتجعات وتكلفة البضاعة حسب الفترة والفلاتر.</p></div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-primary" href="{{ route('sales_reports.export', request()->query()) }}">تصدير CSV</a>
            <a class="btn btn-outline-primary" href="{{ route('sales_reports.pdf', request()->query()) }}">تنزيل PDF</a>
        </div>
    </div>

    @if($report['summary']['missing_cost_quantity'] > 0)
        <div class="alert alert-warning" role="alert">تكلفة المنتج غير محددة أو تساوي صفراً لعدد {{ $report['summary']['missing_cost_quantity'] }} قطعة مباعة؛ إجمالي الربح المعروض غير مكتمل. أدخل تكلفة المنتجات من صفحة المنتجات.</div>
    @endif
    @if($report['summary']['estimated_cost_quantity'] > 0)
        <div class="alert alert-info" role="status">تكلفة {{ $report['summary']['estimated_cost_quantity'] }} قطعة من الفواتير القديمة تقديرية، وتستخدم التكلفة الحالية وقت ترحيل البيانات.</div>
    @endif

    <form method="get" action="{{ route('sales_reports.index') }}" class="card card-body mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-sm-6 col-lg-2"><label class="form-label" for="from">من تاريخ</label><input class="form-control" type="date" id="from" name="from" value="{{ $report['filters']['from'] }}" required></div>
            <div class="col-sm-6 col-lg-2"><label class="form-label" for="to">إلى تاريخ</label><input class="form-control" type="date" id="to" name="to" value="{{ $report['filters']['to'] }}" required></div>
            <div class="col-sm-6 col-lg-2"><label class="form-label" for="branch_id">الفرع</label><select class="form-select" id="branch_id" name="branch_id"><option value="">كل الفروع</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected(($report['filters']['branch_id'] ?? '') == $branch->id)>{{ $branch->name }}</option>@endforeach</select></div>
            <div class="col-sm-6 col-lg-2"><label class="form-label" for="cashier_id">الكاشير</label><select class="form-select" id="cashier_id" name="cashier_id"><option value="">كل المستخدمين</option>@foreach($cashiers as $cashier)<option value="{{ $cashier->id }}" @selected(($report['filters']['cashier_id'] ?? '') == $cashier->id)>{{ $cashier->name }}</option>@endforeach</select></div>
            <div class="col-sm-6 col-lg-2"><label class="form-label" for="category_id">التصنيف</label><select class="form-select" id="category_id" name="category_id"><option value="">كل التصنيفات</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(($report['filters']['category_id'] ?? '') == $category->id)>{{ $category->name }}</option>@endforeach</select></div>
            <div class="col-sm-6 col-lg-2"><label class="form-label" for="product_id">المنتج</label><select class="form-select" id="product_id" name="product_id"><option value="">كل المنتجات</option>@foreach($products as $product)<option value="{{ $product->id }}" @selected(($report['filters']['product_id'] ?? '') == $product->id)>{{ $product->name }}</option>@endforeach</select></div>
            <div class="col-sm-6 col-lg-2"><label class="form-label" for="payment_status">حالة السداد</label><select class="form-select" id="payment_status" name="payment_status"><option value="">الكل</option><option value="paid" @selected(($report['filters']['payment_status'] ?? '') === 'paid')>مدفوعة</option><option value="partial" @selected(($report['filters']['payment_status'] ?? '') === 'partial')>مدفوعة جزئياً</option><option value="unpaid" @selected(($report['filters']['payment_status'] ?? '') === 'unpaid')>غير مدفوعة</option></select></div>
            <div class="col-sm-6 col-lg-2"><label class="form-label" for="group_by">التجميع</label><select class="form-select" id="group_by" name="group_by"><option value="day" @selected($report['filters']['group_by'] === 'day')>يومي</option><option value="week" @selected($report['filters']['group_by'] === 'week')>أسبوعي</option><option value="month" @selected($report['filters']['group_by'] === 'month')>شهري</option></select></div>
            <div class="col-sm-6 col-lg-2"><button class="btn btn-primary w-100">تطبيق الفلاتر</button></div>
        </div>
    </form>

    @php($summary = $report['summary'])
    <div class="row g-3 mb-3">
        @foreach([
            'إجمالي المبيعات' => $summary['gross_sales'], 'الخصومات غير المسجلة' => $summary['discounts'], 'المرتجعات' => $summary['refunds'],
            'صافي المبيعات' => $summary['net_sales'], 'تكلفة البضاعة' => $summary['cost_of_goods'], 'مجمل الربح' => $summary['gross_profit'],
        ] as $label => $value)
            <div class="col-6 col-md-4 col-xl-2"><div class="card h-100"><div class="card-body"><div class="small text-body-secondary">{{ $label }}</div><strong>{{ $value === null ? 'غير مسجلة في النظام' : $value.' ج.م' }}</strong></div></div></div>
        @endforeach
        <div class="col-6 col-md-4 col-xl-2"><div class="card h-100"><div class="card-body"><div class="small text-body-secondary">عدد الفواتير</div><strong>{{ $summary['invoice_count'] }}</strong></div></div></div>
        <div class="col-6 col-md-4 col-xl-2"><div class="card h-100"><div class="card-body"><div class="small text-body-secondary">متوسط الفاتورة</div><strong>{{ $summary['average_ticket'] }} ج.م</strong></div></div></div>
    </div>

    <div class="row g-3">
        <div class="col-lg-7"><section class="card h-100"><div class="card-header fw-semibold">الأداء حسب الفترة</div><div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>الفترة</th><th>صافي المبيعات</th><th>الفواتير</th></tr></thead><tbody>@forelse($report['trend'] as $row)<tr><td>{{ $row['period'] }}</td><td>{{ $row['net_sales'] }} ج.م</td><td>{{ $row['invoice_count'] }}</td></tr>@empty<tr><td colspan="3" class="text-center text-body-secondary py-3">لا توجد مبيعات في الفترة المحددة.</td></tr>@endforelse</tbody></table></div></section></div>
        <div class="col-lg-5"><section class="card h-100"><div class="card-header fw-semibold">المبيعات حسب التصنيف</div><div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>التصنيف</th><th>الكمية</th><th>الصافي</th></tr></thead><tbody>@forelse($report['categories'] as $row)<tr><td>{{ $row['name'] }}</td><td>{{ $row['quantity'] }}</td><td>{{ $row['net_sales'] }} ج.م</td></tr>@empty<tr><td colspan="3" class="text-center text-body-secondary py-3">لا توجد بيانات.</td></tr>@endforelse</tbody></table></div></section></div>
        @foreach(['top' => 'الأكثر مبيعاً', 'bottom' => 'الأقل مبيعاً'] as $key => $title)
            <div class="col-lg-6"><section class="card"><div class="card-header fw-semibold">{{ $title }}</div><div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>المنتج</th><th>صافي الكمية</th><th>صافي المبيعات</th></tr></thead><tbody>@forelse($report['products'][$key] as $row)<tr><td>{{ $row['name'] }}</td><td>{{ $row['quantity'] }}</td><td>{{ $row['net_sales'] }} ج.م</td></tr>@empty<tr><td colspan="3" class="text-center text-body-secondary py-3">لا توجد بيانات.</td></tr>@endforelse</tbody></table></div></section></div>
        @endforeach
        <div class="col-lg-6"><section class="card"><div class="card-header fw-semibold">المبيعات حسب الكاشير</div><div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>الكاشير</th><th>الفواتير</th><th>صافي المبيعات</th></tr></thead><tbody>@forelse($report['cashiers'] as $row)<tr><td>{{ $row['name'] }}</td><td>{{ $row['invoice_count'] }}</td><td>{{ $row['net_sales'] }} ج.م</td></tr>@empty<tr><td colspan="3" class="text-center text-body-secondary py-3">لا توجد بيانات.</td></tr>@endforelse</tbody></table></div></section></div>
        <div class="col-lg-6"><section class="card"><div class="card-header fw-semibold">الفواتير حسب حالة السداد</div><div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>الحالة</th><th>عدد الفواتير</th></tr></thead><tbody>@forelse($report['payment_statuses'] as $row)<tr><td>{{ ['paid' => 'مدفوعة', 'partial' => 'مدفوعة جزئياً', 'unpaid' => 'غير مدفوعة'][$row['status']] ?? $row['status'] }}</td><td>{{ $row['invoice_count'] }}</td></tr>@empty<tr><td colspan="2" class="text-center text-body-secondary py-3">لا توجد بيانات.</td></tr>@endforelse</tbody></table></div></section></div>
    </div>
    <p class="small text-body-secondary mt-3 mb-0">الخصومات ووسائل الدفع غير مسجلة حالياً على سطور الفواتير؛ يوضح التقرير حالة السداد بدلاً من تخمين وسيلة الدفع. تكلفة الفواتير القديمة تقديرية بناءً على التكلفة الحالية المتاحة وقت ترحيل البيانات.</p>
</div>
@endsection
