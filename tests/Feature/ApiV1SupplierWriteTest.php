<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\products;
use App\Models\Supplier;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ApiV1SupplierWriteTest extends TestCase
{
    use RefreshDatabase;

    public function test_supplier_create_is_idempotent_and_initializes_opening_balance(): void
    {
        $token = $this->tokenWithSupplierManagePermission();
        $key = (string) Str::uuid();
        $payload = [
            'name' => 'North Supply',
            'phone' => '01012345678',
            'opening_balance' => '125.25',
            'notes' => 'Private note',
        ];

        $this->withToken($token)
            ->postJson('/api/v1/suppliers', ['name' => 'Missing key'])
            ->assertUnprocessable()
            ->assertJsonPath('error_code', 'VALIDATION_FAILED');

        $created = $this->withToken($token)
            ->withHeader('Idempotency-Key', $key)
            ->postJson('/api/v1/suppliers', $payload)
            ->assertCreated()
            ->assertJsonPath('data.name', 'North Supply')
            ->assertJsonPath('data.opening_balance', '125.25')
            ->assertJsonPath('data.balance', '125.25')
            ->assertJsonPath('data.notes', 'Private note');
        $supplierId = $created->json('data.id');

        $this->withToken($token)
            ->withHeader('Idempotency-Key', $key)
            ->postJson('/api/v1/suppliers', $payload)
            ->assertCreated()
            ->assertJsonPath('data.id', $supplierId);

        $this->assertSame(1, Supplier::query()->count());
    }

    public function test_supplier_receipt_records_line_cost_and_increases_only_selected_branch_stock(): void
    {
        $token = $this->tokenWithSupplierManagePermission();
        $supplier = Supplier::query()->create(['name' => 'Receipt supplier', 'opening_balance' => '0.00', 'balance' => '0.00']);
        $product = products::query()->create([
            'name' => 'Receipt phone', 'price' => '100.00', 'cost_price' => '80.00', 'description' => 'Phone', 'stock' => 0,
        ]);
        $branch = Branch::query()->where('is_main', true)->firstOrFail();
        $key = (string) Str::uuid();
        $payload = [
            'branch_id' => $branch->id,
            'purchase_date' => now()->toDateString(),
            'payment_type' => 'credit',
            'paid_amount' => '5.00',
            'items' => [['product_id' => $product->id, 'quantity' => 2, 'unit_cost' => '12.50']],
        ];

        $created = $this->withToken($token)->withHeader('Idempotency-Key', $key)
            ->postJson('/api/v1/suppliers/'.$supplier->id.'/receipts', $payload)
            ->assertCreated()
            ->assertJsonPath('data.total_amount', '25.00')
            ->assertJsonPath('data.items.0.unit_cost', '12.50')
            ->assertJsonPath('data.items.0.cost_warning', true);

        $this->withToken($token)->withHeader('Idempotency-Key', $key)
            ->postJson('/api/v1/suppliers/'.$supplier->id.'/receipts', $payload)
            ->assertCreated()->assertJsonPath('data.id', $created->json('data.id'));

        $this->assertSame('80.00', $product->fresh()->cost_price);
        $this->assertSame(2, (int) $product->fresh()->stock);
        $this->assertSame('20.00', $supplier->fresh()->balance);
        $this->assertDatabaseCount('purchases', 1);
        $this->assertDatabaseCount('purchase_items', 1);
        $this->assertDatabaseCount('supplier_payments', 1);
        $this->assertSame(0, app(StockService::class)->verify()['branch_stock_mismatches']);
    }

    public function test_supplier_create_requires_manage_permission_and_idempotency_key(): void
    {
        $permission = Permission::query()->create([
            'name' => 'page.suppliers.view',
            'guard_name' => 'web',
        ]);
        $role = Role::query()->create(['name' => 'Supplier viewer', 'guard_name' => 'web']);
        $role->givePermissionTo($permission);
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole($role);
        $token = $user->createToken('supplier-viewer', ['api:me', 'page.suppliers.view']);

        $this->withToken($token->plainTextToken)
            ->withHeader('Idempotency-Key', (string) Str::uuid())
            ->postJson('/api/v1/suppliers', ['name' => 'Forbidden'])
            ->assertForbidden();

        $this->assertSame(0, Supplier::query()->count());
    }

    public function test_supplier_update_without_notes_preserves_the_existing_private_note(): void
    {
        $token = $this->tokenWithSupplierManagePermission();
        $supplier = Supplier::query()->create([
            'name' => 'North Supply',
            'opening_balance' => '10.00',
            'balance' => '10.00',
            'notes' => 'Keep this note',
        ]);

        $this->withToken($token)
            ->withHeader('Idempotency-Key', (string) Str::uuid())
            ->putJson('/api/v1/suppliers/'.$supplier->id, [
                'name' => 'North Supply Updated',
                'opening_balance' => '10.00',
            ])
            ->assertOk();

        $this->assertSame('Keep this note', $supplier->fresh()->notes);
    }

    public function test_supplier_delete_cannot_cascade_financial_history(): void
    {
        $token = $this->tokenWithSupplierManagePermission();
        $supplier = Supplier::query()->create([
            'name' => 'North Supply',
            'opening_balance' => '10.00',
            'balance' => '10.00',
        ]);
        $purchaseId = DB::table('purchases')->insertGetId([
            'supplier_id' => $supplier->id,
            'invoice_number' => 'SUP-KEEP-001',
            'purchase_date' => '2026-11-17',
            'payment_type' => 'credit',
            'total_amount' => '50.00',
            'paid_amount' => '0.00',
            'remaining' => '50.00',
            'status' => 'unpaid',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('supplier_payments')->insert([
            'supplier_id' => $supplier->id,
            'purchase_id' => $purchaseId,
            'receipt_number' => 'PAY-KEEP-001',
            'amount' => '5.00',
            'payment_date' => '2026-11-17',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withToken($token)
            ->withHeader('Idempotency-Key', (string) Str::uuid())
            ->deleteJson('/api/v1/suppliers/'.$supplier->id)
            ->assertUnprocessable();

        $this->assertDatabaseHas('suppliers', ['id' => $supplier->id]);
        $this->assertDatabaseHas('purchases', ['id' => $purchaseId]);
        $this->assertDatabaseHas('supplier_payments', ['receipt_number' => 'PAY-KEEP-001']);
    }

    private function tokenWithSupplierManagePermission(): string
    {
        $permission = Permission::query()->create([
            'name' => 'page.suppliers.manage',
            'guard_name' => 'web',
        ]);
        $role = Role::query()->create(['name' => 'Supplier manager', 'guard_name' => 'web']);
        $role->givePermissionTo($permission);
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole($role);

        return $user->createToken('supplier-manager', [
            'api:me',
            'page.suppliers.view',
            'page.suppliers.manage',
        ])->plainTextToken;
    }
}
