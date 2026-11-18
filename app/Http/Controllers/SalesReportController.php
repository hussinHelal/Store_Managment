<?php

namespace App\Http\Controllers;

use App\Http\Requests\SalesReportRequest;
use App\Models\Branch;
use App\Models\category;
use App\Models\products;
use App\Models\User;
use App\Services\SalesReportService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SalesReportController extends Controller
{
    public function index(SalesReportRequest $request, SalesReportService $reports): View
    {
        $filters = $request->validated();
        $report = $reports->build($filters);

        return view('sales_reports.index', [
            'report' => $report,
            'branches' => Branch::query()->where('is_active', true)->orderBy('name')->limit(500)->get(['id', 'name']),
            'cashiers' => User::query()->where('is_active', true)->orderBy('name')->limit(500)->get(['id', 'name']),
            'categories' => category::query()->orderBy('name')->limit(500)->get(['id', 'name']),
            'products' => products::query()->orderBy('name')->limit(500)->get(['id', 'name']),
        ]);
    }

    public function pdf(SalesReportRequest $request, SalesReportService $reports): Response
    {
        abort_unless(class_exists(Mpdf::class), 501, 'PDF generation is unavailable.');
        $report = $reports->build($request->validated());
        $html = view('sales_reports.pdf', compact('report'))->render();
        $temporaryDirectory = storage_path('app/mpdf-temp');
        File::ensureDirectoryExists($temporaryDirectory);
        $pdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L',
            'tempDir' => $temporaryDirectory,
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
            'default_font' => 'dejavusans',
        ]);
        $pdf->WriteHTML($html);
        $contents = $pdf->Output('', Destination::STRING_RETURN);

        return response($contents, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="sales-report-'.$report['filters']['from'].'-'.$report['filters']['to'].'.pdf"',
            'Content-Length' => (string) strlen($contents),
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function export(SalesReportRequest $request, SalesReportService $reports): StreamedResponse
    {
        $filters = $request->validated();

        return response()->streamDownload(function () use ($reports, $filters): void {
            $output = fopen('php://output', 'wb');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['تاريخ الفاتورة', 'رقم الفاتورة', 'العميل', 'حالة الفاتورة', 'المنتج', 'الكمية', 'سعر الوحدة', 'تكلفة الوحدة', 'التكلفة تقديرية', 'الإجمالي'], ',', '"', '');
            foreach ($reports->exportRows($filters) as $row) {
                fputcsv($output, array_map($this->safeCsvCell(...), [
                    $row->invoice_date,
                    $row->invoice_number,
                    $row->customer,
                    $row->status,
                    $row->product_name,
                    $row->quantity,
                    $row->unit_price,
                    $row->unit_cost,
                    $row->cost_estimated ? 'نعم' : 'لا',
                    $row->line_total,
                ]), ',', '"', '');
            }
            fclose($output);
        }, 'sales-report-'.$filters['from'].'-'.$filters['to'].'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    private function safeCsvCell(mixed $value): string
    {
        $cell = (string) ($value ?? '');

        return preg_match('/^[=+\-@]/u', $cell) === 1 ? "'".$cell : $cell;
    }
}
