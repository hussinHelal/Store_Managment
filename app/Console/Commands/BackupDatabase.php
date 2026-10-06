<?php

namespace App\Console\Commands;

use App\Models\customers;
use App\Models\invoice;
use App\Models\installments;
use App\Models\Maintenance;
use App\Models\products;
use App\Models\BackupLog;
use Illuminate\Console\Command;
use ZipArchive;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database {--format=zip : Export format (zip, pdf, xlsx)}';
    protected $description = 'Create an automatic backup of database and save to storage';

    public function handle()
    {
        try {
            $this->info('Starting database backup...');

            // Collect data
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

            $this->info('Data collected successfully. Creating backup file...');

            // Create CSV files
            $files = [
                'products.csv' => $this->buildCsv($productsArr),
                'invoices.csv' => $this->buildCsv($invoicesArr),
                'installments.csv' => $this->buildCsv($installmentsArr),
                'customers.csv' => $this->buildCsv($customersArr),
                'maintenance.csv' => $this->buildCsv($maintenanceArr),
            ];

            $format = $this->option('format');
            $filenameBase = 'backup-' . now()->format('YmdHis');
            $storagePath = storage_path('app/backups');

            // Ensure backup directory exists
            if (!is_dir($storagePath)) {
                mkdir($storagePath, 0755, true);
            }

            if ($format === 'pdf' && class_exists(\Dompdf\Dompdf::class)) {
                $html = '<h1>Backup - ' . e(now()->toDateTimeString()) . '</h1>';
                $datasets = ['Products' => $productsArr, 'Invoices' => $invoicesArr, 'Installments' => $installmentsArr, 'Customers' => $customersArr, 'Maintenance' => $maintenanceArr];
                foreach ($datasets as $title => $rows) {
                    $html .= "<h2>{$title}</h2>";
                    if (empty($rows)) { $html .= '<p>(No records)</p>'; continue; }
                    $html .= '<table border="1" cellpadding="4" cellspacing="0" style="border-collapse:collapse; width:100%; font-size:10px;">';
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
                $backupFile = $storagePath . DIRECTORY_SEPARATOR . $filenameBase . '.pdf';
                file_put_contents($backupFile, $pdfContent);
                $this->info('Backup saved to: ' . $backupFile);
            } elseif ($format === 'xlsx' && class_exists('PhpOffice\\PhpSpreadsheet\\Spreadsheet')) {
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
                $backupFile = $storagePath . DIRECTORY_SEPARATOR . $filenameBase . '.xlsx';
                $writer->save($backupFile);
                $this->info('Backup saved to: ' . $backupFile);
            } else {
                // Default: ZIP
                $filename = $filenameBase . '.zip';
                $backupFile = $storagePath . DIRECTORY_SEPARATOR . $filename;

                $zip = new ZipArchive();
                if ($zip->open($backupFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                    throw new \Exception('Unable to create backup ZIP file.');
                }
                foreach ($files as $name => $content) {
                    $zip->addFromString($name, $content);
                }
                $zip->close();
                $this->info('Backup saved to: ' . $backupFile);
            }

            // Log the backup
            BackupLog::create([
                'filename' => basename($backupFile),
                'path' => $backupFile,
                'format' => $format,
                'size' => filesize($backupFile),
                'status' => 'success',
            ]);

            $this->info('Backup completed successfully!');
            return 0;

        } catch (\Exception $e) {
            $this->error('Backup failed: ' . $e->getMessage());

            // Log the failure
            BackupLog::create([
                'filename' => 'backup-' . now()->format('YmdHis'),
                'path' => null,
                'format' => $this->option('format'),
                'size' => 0,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            return 1;
        }
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
