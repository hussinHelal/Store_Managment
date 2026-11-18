<?php

namespace Tests\Feature;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ApiV1SupplierReadTest extends TestCase
{
    use RefreshDatabase;

    public function test_supplier_list_is_searchable_paginated_and_excludes_private_notes(): void
    {
        $token = $this->tokenWithSupplierViewPermission();
        Supplier::query()->create([
            'name' => 'North Star Supply',
            'phone' => '01012345678',
            'email' => 'accounts@example.test',
            'address' => 'Cairo',
            'opening_balance' => '20.00',
            'balance' => '125.25',
            'notes' => 'Private supplier note',
        ]);
        Supplier::query()->create([
            'name' => 'South Supply',
            'opening_balance' => '0.00',
            'balance' => '0.00',
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/suppliers?q=North&per_page=1')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'North Star Supply')
            ->assertJsonPath('data.0.balance', '125.25')
            ->assertJsonPath('data.0.purchases_count', 0)
            ->assertJsonMissingPath('data.0.notes')
            ->assertJsonStructure(['data', 'links', 'meta']);
    }

    public function test_supplier_statement_returns_aging_and_paginated_financial_history(): void
    {
        $supplier = $this->supplier();
        $purchase = $supplier->purchases()->create([
            'invoice_number' => 'SUP-STATEMENT-1',
            'purchase_date' => now()->subDays(40)->toDateString(),
            'payment_type' => 'credit',
            'total_amount' => '75.00',
            'paid_amount' => '0.00',
            'remaining' => '75.00',
            'status' => 'unpaid',
        ]);
        $supplier->update(['balance' => '115.00']);
        $supplier->payments()->create([
            'purchase_id' => $purchase->id,
            'receipt_number' => 'SUP-STATEMENT-PAY',
            'amount' => '5.00',
            'payment_date' => now()->toDateString(),
        ]);
        $user = $this->userWithSupplierViewPermission();
        $token = $user->createToken('supplier-statement-reader', ['api:me', 'page.suppliers.view']);

        $this->withToken($token->plainTextToken)->getJson('/api/v1/suppliers/'.$supplier->id.'/statement?status=open')
            ->assertOk()
            ->assertJsonPath('data.summary.recorded_balance', '115.00')
            ->assertJsonPath('data.summary.aging.days_31_60', '75.00')
            ->assertJsonPath('data.purchases.data.0.invoice_number', 'SUP-STATEMENT-1')
            ->assertJsonPath('data.payments.data.0.receipt_number', 'SUP-STATEMENT-PAY');
    }

    public function test_supplier_detail_requires_current_page_permission(): void
    {
        $supplier = $this->supplier();
        $userWithoutPermission = User::factory()->create(['is_active' => true]);
        $overbroadToken = $userWithoutPermission->createToken('supplier-reader', [
            'api:me',
            'page.suppliers.view',
        ]);

        $this->withToken($overbroadToken->plainTextToken)
            ->getJson('/api/v1/suppliers/'.$supplier->id)
            ->assertForbidden();
    }

    public function test_supplier_detail_requires_token_ability(): void
    {
        $supplier = $this->supplier();
        $user = $this->userWithSupplierViewPermission();
        $wrongScope = $user->createToken('wrong-supplier-scope', ['api:me']);

        $this->withToken($wrongScope->plainTextToken)
            ->getJson('/api/v1/suppliers/'.$supplier->id)
            ->assertForbidden();
    }

    public function test_supplier_detail_returns_an_allowlisted_authorized_resource(): void
    {
        $supplier = $this->supplier();
        $user = $this->userWithSupplierViewPermission();
        $token = $user->createToken('supplier-reader', ['api:me', 'page.suppliers.view']);
        $this->assertTrue($user->can('page.suppliers.view'));

        $this->withToken($token->plainTextToken)
            ->getJson('/api/v1/suppliers/'.$supplier->id)
            ->assertOk()
            ->assertJsonPath('data.name', 'North Star Supply')
            ->assertJsonPath('data.balance', '40.00')
            ->assertJsonMissingPath('data.notes');
    }

    private function supplier(): Supplier
    {
        return Supplier::query()->create([
            'name' => 'North Star Supply',
            'opening_balance' => '0.00',
            'balance' => '40.00',
        ]);
    }

    private function tokenWithSupplierViewPermission(): string
    {
        $user = $this->userWithSupplierViewPermission();
        $token = $user->createToken('supplier-reader', ['api:me', 'page.suppliers.view']);

        return $token->plainTextToken;
    }

    private function userWithSupplierViewPermission(): User
    {
        $permission = Permission::query()->create([
            'name' => 'page.suppliers.view',
            'guard_name' => 'web',
        ]);
        $role = Role::query()->create(['name' => 'Supplier reader', 'guard_name' => 'web']);
        $role->givePermissionTo($permission);
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole($role);

        return $user;
    }
}
