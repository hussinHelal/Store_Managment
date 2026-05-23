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
        if (!$request->user() || !$request->user()->isSuperAdmin()) {
            abort(403);
        }

        $files = [
            'products.csv' => $this->buildCsv(products::with('category')->get()->map(fn($product) => [
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
            ])->toArray()),
            'invoices.csv' => $this->buildCsv(invoice::with('product')->get()->map(fn($invoice) => [
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
            ])->toArray()),
            'installments.csv' => $this->buildCsv(installments::with('product')->get()->map(fn($installment) => [
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
            ])->toArray()),
            'customers.csv' => $this->buildCsv(customers::all()->map(fn($customer) => [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'address' => $customer->address,
                'created_at' => $customer->created_at,
                'updated_at' => $customer->updated_at,
            ])->toArray()),
            'maintenance.csv' => $this->buildCsv(Maintenance::all()->map(fn($maintenance) => [
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
            ])->toArray()),
        ];

        $filename = 'backup-' . now()->format('YmdHis') . '.zip';
        $tempFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename;

        $zip = new ZipArchive();
        if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Unable to create backup file.');
        }

        foreach ($files as $name => $content) {
            $zip->addFromString($name, $content);
        }

        $zip->close();

        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
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
