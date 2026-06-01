<?php

namespace App\Http\Controllers;

use App\Models\customers;
use App\Models\invoice;
use App\Models\installments;
use App\Models\Maintenance;
use App\Models\products;
use Illuminate\Http\Request;
use ZipArchive;

class BackupController extends Controller
{
    public function export(Request $request)
    {
        // Allow both admin and superadmin users
        $isAuthorized = $request->user() && ($request->user()->isSuperAdmin() || $request->user()->isAdmin());

        if (!$isAuthorized) {
            if ($request->expectsJson() || $request->ajax() || str_contains($request->header('Accept', ''), 'application/json')) {
                return response()->json(['error' => 'ليس لديك صلاحية تنزيل النسخة الاحتياطية.'], 403);
            }

            return redirect()->route('home')->with('error', 'ليس لديك صلاحية تنزيل النسخة الاحتياطية.');
        }

        try {
            $productsArr = products::with('category')->get()->map(fn($product) => [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'description' => $product->description,
                'stock' => $product->stock,
                'total_sold' => $product->total_sold,
                'category' => $product->category?->name,
                'image' => $product->image,
                'created_at' => $product->created_at,
                'updated_at' => $product->updated_at,
            ])->toArray();

            $invoicesArr = invoice::with('product')->get()->map(fn($invoice) => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'customer' => $invoice->customer,
                'product_id' => $invoice->product_id,
                'product_name' => $invoice->product?->name,
                'quantity' => $invoice->quantity,
                'product_price' => $invoice->product_price,
                'total_amount' => $invoice->total_amount,
                'paid_amount' => $invoice->paid_amount,
                'status' => $invoice->status,
                'invoice_date' => $invoice->invoice_date,
                'created_at' => $invoice->created_at,
                'updated_at' => $invoice->updated_at,
            ])->toArray();

            $installmentsArr = installments::with('product')->get()->map(fn($installment) => [
                'id' => $installment->id,
                'customer' => $installment->customer,
                'product_id' => $installment->product_id,
                'product_name' => $installment->product_name,
                'product_price' => $installment->product_price,
                'quantity' => $installment->quantity,
                'paid_amount' => $installment->paid_amount,
                'remaining' => $installment->remaining,
                'status' => $installment->status,
                'payment_date' => $installment->payment_date,
                'next_payment_date' => $installment->next_payment_date,
                'created_at' => $installment->created_at,
                'updated_at' => $installment->updated_at,
            ])->toArray();

            $customersArr = customers::all()->map(fn($customer) => [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'address' => $customer->address,
                'created_at' => $customer->created_at,
                'updated_at' => $customer->updated_at,
            ])->toArray();

            $maintenanceArr = Maintenance::all()->map(fn($maintenance) => [
                'id' => $maintenance->id,
                'name' => $maintenance->name,
                'owner' => $maintenance->owner,
                'phone' => $maintenance->phone,
                'address' => $maintenance->address,
                'description' => $maintenance->description,
                'status' => $maintenance->status,
                'requested_date' => $maintenance->requested_date,
                'completed_date' => $maintenance->completed_date,
                'created_at' => $maintenance->created_at,
                'updated_at' => $maintenance->updated_at,
            ])->toArray();

            $files = [
                'products.csv' => $this->buildCsv($productsArr),
                'invoices.csv' => $this->buildCsv($invoicesArr),
                'installments.csv' => $this->buildCsv($installmentsArr),
                'customers.csv' => $this->buildCsv($customersArr),
                'maintenance.csv' => $this->buildCsv($maintenanceArr),
            ];
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['error' => 'فشل جمع البيانات: ' . $e->getMessage()], 500);
            }

            abort(500, 'فشل جمع البيانات: ' . $e->getMessage());
        }

        $format = $request->query('format', 'zip');
        $filenameBase = 'backup-' . now()->format('YmdHis');

        try {
            if ($format === 'pdf') {
                if (!class_exists(\Dompdf\Dompdf::class)) {
                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json(['error' => 'توليد PDF يتطلب تثبيت الحزمة dompdf/dompdf.'], 501);
                    }
                    return redirect()->back()->with('error', 'توليد PDF يتطلب تثبيت الحزمة dompdf/dompdf.');
                }

                // Build a simple HTML representation of datasets
                $html = '<h1>Backup export - ' . e(now()->toDateTimeString()) . '</h1>';
                $datasets = ['Products' => $productsArr, 'Invoices' => $invoicesArr, 'Installments' => $installmentsArr, 'Customers' => $customersArr, 'Maintenance' => $maintenanceArr];
                foreach ($datasets as $title => $rows) {
                    $html .= "<h2>{$title}</h2>";
                    if (empty($rows)) { $html .= '<p>(لا توجد سجلات)</p>'; continue; }
                    $html .= '<table border="1" cellpadding="4" cellspacing="0" style="border-collapse:collapse; width:100%">';
                    $html .= '<thead><tr>';
                    foreach (array_keys($rows[0]) as $col) { $html .= '<th>' . e($col) . '</th>'; }
                    $html .= '</tr></thead><tbody>';
                    foreach ($rows as $row) {
                        $html .= '<tr>';
                        foreach ($row as $cell) { $html .= '<td>' . e(is_array($cell) ? json_encode($cell, JSON_UNESCAPED_UNICODE) : (string)$cell) . '</td>'; }
                        $html .= '</tr>';
                    }
                    $html .= '</tbody></table><br/>';
                }

                $dompdf = new \Dompdf\Dompdf();
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'landscape');
                $dompdf->render();
                $pdfContent = $dompdf->output();
                $tempFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filenameBase . '.pdf';
                file_put_contents($tempFile, $pdfContent);
                return response()->download($tempFile, basename($tempFile))->deleteFileAfterSend(true);
            }

            if ($format === 'xlsx' || $format === 'excel') {
                if (class_exists('PhpOffice\\PhpSpreadsheet\\Spreadsheet')) {
                    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
                    $sheetIndex = 0;
                    $sets = ['Products' => $productsArr, 'Invoices' => $invoicesArr, 'Installments' => $installmentsArr, 'Customers' => $customersArr, 'Maintenance' => $maintenanceArr];
                    foreach ($sets as $title => $rows) {
                        if ($sheetIndex > 0) {
                            $spreadsheet->createSheet();
                        }
                        $sheet = $spreadsheet->setActiveSheetIndex($sheetIndex);
                        $sheet->setTitle(substr($title, 0, 31));
                        if (!empty($rows)) {
                            $cols = array_keys($rows[0]);
                            $colIndex = 1;
                            foreach ($cols as $c) {
                                $sheet->setCellValueByColumnAndRow($colIndex++, 1, $c);
                            }
                            $rowIndex = 2;
                            foreach ($rows as $row) {
                                $colIndex = 1;
                                foreach ($row as $cell) {
                                    $sheet->setCellValueByColumnAndRow($colIndex++, $rowIndex, is_array($cell) ? json_encode($cell, JSON_UNESCAPED_UNICODE) : $cell);
                                }
                                $rowIndex++;
                            }
                        }
                        $sheetIndex++;
                    }

                    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                    $tempFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filenameBase . '.xlsx';
                    $writer->save($tempFile);
                    return response()->download($tempFile, basename($tempFile))->deleteFileAfterSend(true);
                }

                // fallback to zip of CSVs
                $filenameWithoutExt = $filenameBase . '-csv';
                $tempFile = $this->createArchive($files, $filenameWithoutExt);
                return response()->download($tempFile, basename($tempFile))->deleteFileAfterSend(true);
            }

            // default: zip of CSVs
            $filenameWithoutExt = $filenameBase;
            $tempFile = $this->createArchive($files, $filenameWithoutExt);
            return response()->download($tempFile, basename($tempFile))->deleteFileAfterSend(true);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $he) {
            throw $he;
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['error' => 'فشل أثناء إنشاء النسخة الاحتياطية: ' . $e->getMessage()], 500);
            }
            abort(500, 'فشل أثناء إنشاء النسخة الاحتياطية: ' . $e->getMessage());
        }
    }

    private function createArchive(array $files, string $filename): string
    {
        if (class_exists('ZipArchive')) {
            $archiveFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename . '.zip';
            $zip = new ZipArchive();
            if ($zip->open($archiveFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                abort(500, 'Unable to create backup file.');
            }
            foreach ($files as $name => $content) {
                $zip->addFromString($name, $content);
            }
            $zip->close();
            return $archiveFile;
        }

        if (class_exists('PharData')) {
            $archiveFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename . '.tar';
            try {
                @unlink($archiveFile);
                $tar = new \PharData($archiveFile);
                foreach ($files as $name => $content) {
                    $tar->addFromString($name, $content);
                }
            } catch (\Exception $e) {
                abort(500, 'Unable to create backup archive: ' . $e->getMessage());
            }
            return $archiveFile;
        }

        abort(500, 'Backup requires the ZipArchive or PharData PHP extension. Please enable ext-zip or ext-phar.');
    }

    private function buildCsv(array $rows): string
    {
        if (empty($rows)) {
            return '';
        }

        $fp = fopen('php://temp', 'r+');
        fputcsv($fp, array_keys($rows[0]));

        foreach ($rows as $row) {
            fputcsv($fp, array_map(fn($value) => is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value, $row));
        }

        rewind($fp);
        $csv = stream_get_contents($fp);
        fclose($fp);

        return $csv;
    }
}
