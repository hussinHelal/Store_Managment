<!doctype html>
<html lang="ar" dir="rtl">
<head><meta charset="utf-8"><style>body{font-family:dejavusans,sans-serif;font-size:10pt}h1{font-size:18pt}table{width:100%;border-collapse:collapse;margin:12px 0}th,td{border:1px solid #bbb;padding:5px;text-align:right}.summary td{width:25%}.muted{color:#666}</style></head>
<body>
    <h1>تقرير المبيعات</h1>
    <p>من {{ $report['filters']['from'] }} إلى {{ $report['filters']['to'] }}</p>
    <table class="summary"><tbody>
        <tr><th>إجمالي المبيعات</th><td>{{ $report['summary']['gross_sales'] }} ج.م</td><th>المرتجعات</th><td>{{ $report['summary']['refunds'] }} ج.م</td></tr>
        <tr><th>صافي المبيعات</th><td>{{ $report['summary']['net_sales'] }} ج.م</td><th>تكلفة البضاعة</th><td>{{ $report['summary']['cost_of_goods'] }} ج.م</td></tr>
        <tr><th>مجمل الربح</th><td>{{ $report['summary']['gross_profit'] }} ج.م</td><th>عدد الفواتير</th><td>{{ $report['summary']['invoice_count'] }}</td></tr>
    </tbody></table>
    @if($report['summary']['missing_cost_quantity'] > 0)<p class="muted">تنبيه: التكلفة غير محددة أو صفر لعدد {{ $report['summary']['missing_cost_quantity'] }} قطعة؛ الربح غير مكتمل.</p>@endif
    @if($report['summary']['estimated_cost_quantity'] > 0)<p class="muted">تكلفة {{ $report['summary']['estimated_cost_quantity'] }} قطعة تقديرية من التكلفة الحالية وقت الترحيل.</p>@endif
    <h2>الأداء حسب الفترة</h2>
    <table><thead><tr><th>الفترة</th><th>صافي المبيعات</th><th>الفواتير</th></tr></thead><tbody>@foreach($report['trend'] as $row)<tr><td>{{ $row['period'] }}</td><td>{{ $row['net_sales'] }} ج.م</td><td>{{ $row['invoice_count'] }}</td></tr>@endforeach</tbody></table>
    <h2>المبيعات حسب التصنيف</h2>
    <table><thead><tr><th>التصنيف</th><th>الكمية</th><th>صافي المبيعات</th></tr></thead><tbody>@foreach($report['categories'] as $row)<tr><td>{{ $row['name'] }}</td><td>{{ $row['quantity'] }}</td><td>{{ $row['net_sales'] }} ج.م</td></tr>@endforeach</tbody></table>
    <h2>الأكثر مبيعاً</h2>
    <table><thead><tr><th>المنتج</th><th>الكمية</th><th>صافي المبيعات</th></tr></thead><tbody>@foreach($report['products']['top'] as $row)<tr><td>{{ $row['name'] }}</td><td>{{ $row['quantity'] }}</td><td>{{ $row['net_sales'] }} ج.م</td></tr>@endforeach</tbody></table>
    <h2>الأقل مبيعاً</h2>
    <table><thead><tr><th>المنتج</th><th>الكمية</th><th>صافي المبيعات</th></tr></thead><tbody>@foreach($report['products']['bottom'] as $row)<tr><td>{{ $row['name'] }}</td><td>{{ $row['quantity'] }}</td><td>{{ $row['net_sales'] }} ج.م</td></tr>@endforeach</tbody></table>
    <h2>المبيعات حسب الكاشير</h2>
    <table><thead><tr><th>الكاشير</th><th>الفواتير</th><th>صافي المبيعات</th></tr></thead><tbody>@foreach($report['cashiers'] as $row)<tr><td>{{ $row['name'] }}</td><td>{{ $row['invoice_count'] }}</td><td>{{ $row['net_sales'] }} ج.م</td></tr>@endforeach</tbody></table>
    <p class="muted">الخصومات ووسائل الدفع لا يتم تسجيلها حالياً بصورة مستقلة؛ حالة السداد معروضة في شاشة التقرير.</p>
</body></html>
