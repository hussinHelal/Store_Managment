<?php

namespace Tests\Feature;

use App\Models\products;
use App\Models\User;
use App\Services\InvoiceCreationService;
use App\Services\SalesReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SalesReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_report_uses_sale_time_cost_snapshots_and_filters(): void
    {
        [$user, $token] = $this->reportViewer();
        $costed = products::query()->create(['name' => 'Costed device', 'price' => '100.00', 'cost_price' => '60.00', 'stock' => 10]);
        $uncosted = products::query()->create(['name' => 'Uncosted device', 'price' => '50.00', 'stock' => 10]);

        $service = app(InvoiceCreationService::class);
        $create = fn (products $product, int $quantity, string $paid) => $service->create([
            'customer' => 'Report buyer',
            'product_ids' => [$product->id],
            'quantities' => [$quantity],
            'invoice_date' => '2026-11-15',
            'paid_amount' => $paid,
        ], $user->id, static function (): void {});

        $paidInvoice = $create($costed, 2, '200.00');
        $partialInvoice = $create($uncosted, 1, '10.00');
        $costed->update(['cost_price' => '90.00']);

        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $paidInvoice->id,
            'product_id' => $costed->id,
            'unit_cost' => '60.00',
            'cost_estimated' => false,
        ]);
        $this->assertDatabaseHas('invoices', ['id' => $paidInvoice->id, 'cashier_id' => $user->id]);

        $this->withToken($token)->getJson('/api/v1/sales-reports?from=2026-11-01&to=2026-11-30&group_by=week')
            ->assertOk()
            ->assertJsonPath('data.summary.gross_sales', '250.00')
            ->assertJsonPath('data.summary.discounts', null)
            ->assertJsonPath('data.summary.refunds', '0.00')
            ->assertJsonPath('data.summary.net_sales', '250.00')
            ->assertJsonPath('data.summary.invoice_count', 2)
            ->assertJsonPath('data.summary.average_ticket', '125.00')
            ->assertJsonPath('data.summary.cost_of_goods', '120.00')
            ->assertJsonPath('data.summary.gross_profit', '130.00')
            ->assertJsonPath('data.summary.missing_cost_quantity', 1)
            ->assertJsonPath('data.summary.estimated_cost_quantity', 0)
            ->assertJsonPath('data.payment_statuses.0.status', 'paid')
            ->assertJsonPath('data.payment_statuses.1.status', 'partial');

        $this->withToken($token)->getJson('/api/v1/sales-reports?from=2026-11-01&to=2026-11-30&product_id='.$costed->id)
            ->assertOk()->assertJsonPath('data.summary.gross_sales', '200.00')
            ->assertJsonPath('data.summary.cost_of_goods', '120.00');

        $this->assertSame('60.00', $paidInvoice->lines()->firstOrFail()->unit_cost);
        $this->assertSame('2026-11-15', $partialInvoice->invoice_date->toDateString());
    }

    public function test_sales_report_permissions_and_date_validation_are_enforced(): void
    {
        $this->reportViewer();
        $other = User::factory()->create(['is_active' => true]);
        $otherToken = $other->createToken('no-sales-report', ['api:me']);

        $this->withToken($otherToken->plainTextToken)->getJson('/api/v1/sales-reports')->assertForbidden();
    }

    public function test_sales_report_rejects_date_ranges_longer_than_one_year(): void
    {
        [, $token] = $this->reportViewer();
        $this->withToken($token)->getJson('/api/v1/sales-reports?from=2025-01-01&to=2026-11-01')->assertUnprocessable();
    }

    public function test_web_sales_report_renders_arabic_filters_and_cost_warning(): void
    {
        [$user] = $this->reportViewer();
        $product = products::query()->create(['name' => 'Missing cost item', 'price' => '25.00', 'stock' => 4]);
        app(InvoiceCreationService::class)->create([
            'customer' => '=HYPERLINK("https://example.invalid")',
            'product_ids' => [$product->id],
            'quantities' => [1],
            'invoice_date' => now('Africa/Cairo')->toDateString(),
            'paid_amount' => '25.00',
        ], $user->id, static function (): void {});

        $this->assertDatabaseHas('invoice_items', ['product_id' => $product->id, 'unit_cost' => null]);
        $summary = app(SalesReportService::class)->build([
            'from' => now('Africa/Cairo')->startOfMonth()->toDateString(),
            'to' => now('Africa/Cairo')->toDateString(),
            'group_by' => 'day',
        ])['summary'];
        $this->assertSame(1, $summary['missing_cost_quantity']);

        $this->actingAs($user)->get(route('sales_reports.index'))
            ->assertOk()
            ->assertSee('تقارير المبيعات')
            ->assertSee('تكلفة المنتج غير محددة')
            ->assertSee('مبيعات');

        $filters = ['from' => $today = now('Africa/Cairo')->startOfMonth()->toDateString(), 'to' => now('Africa/Cairo')->toDateString()];
        $csv = $this->actingAs($user)->get(route('sales_reports.export', $filters));
        $csv->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
        ob_start();
        $csv->baseResponse->getCallback()();
        $csvContent = ob_get_clean();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csvContent);
        $this->assertStringContainsString("'=HYPERLINK", $csvContent);

        $this->actingAs($user)->get(route('sales_reports.pdf', $filters))
            ->assertOk()->assertHeader('content-type', 'application/pdf');
    }

    private function reportViewer(): array
    {
        $permission = Permission::query()->firstOrCreate(['name' => 'page.sales_reports.view', 'guard_name' => 'web']);
        $role = Role::query()->create(['name' => 'Sales report viewer '.Str::random(8), 'guard_name' => 'web']);
        $role->givePermissionTo($permission);
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole($role);
        $token = $user->createToken('sales-report-viewer', ['api:me', 'page.sales_reports.view']);

        return [$user, $token->plainTextToken];
    }
}
