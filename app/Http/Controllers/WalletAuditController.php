<?php

namespace App\Http\Controllers;

use App\Models\WalletAuditLog;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Who did what, when, from where. Superadmin only. */
class WalletAuditController extends Controller
{
    private const ACTION_FILTERS = [
        'transaction.' => 'معاملات المحافظ',
        'wallet.' => 'إدارة المحافظ',
        'expense.' => 'المصروفات',
        'cash.' => 'حركات النقدية',
        'damaged.' => 'سجلات الهالك',
    ];

    private const ACTION_LABELS = [
        'transaction.created' => 'تسجيل معاملة',
        'transaction.reversed' => 'عكس معاملة',
        'transaction.settled' => 'تحصيل مبلغ آجل',
        'wallet.created' => 'إنشاء محفظة',
        'wallet.updated' => 'تعديل بيانات محفظة',
        'wallet.activated' => 'تفعيل محفظة',
        'wallet.deactivated' => 'إيقاف محفظة',
        'wallet.adjusted' => 'تسوية رصيد محفظة',
        'expense.created' => 'تسجيل مصروف',
        'expense.voided' => 'إلغاء مصروف',
        'cash.adjusted' => 'تعديل درج النقدية',
        'damaged.recorded' => 'تسجيل صنف تالف',
        'damaged.voided' => 'إلغاء سجل تالف',
    ];

    private const SUBJECT_LABELS = [
        'wallet_transaction' => 'معاملة محفظة',
        'wallet' => 'محفظة',
        'wallet_expense' => 'مصروف',
        'wallet_cash_adjustment' => 'حركة درج نقدية',
        'damaged_item' => 'سجل هالك',
    ];

    private const META_LABELS = [
        'wallet_id' => 'رقم المحفظة',
        'type' => 'نوع العملية',
        'amount' => 'المبلغ',
        'commission' => 'العمولة',
        'fee' => 'رسوم المزود',
        'method' => 'طريقة الدفع',
        'payment_method' => 'طريقة الدفع',
        'reversal_id' => 'رقم قيد العكس',
        'reason' => 'السبب',
        'settlement_id' => 'رقم التحصيل',
        'paid' => 'المبلغ المحصل',
        'remaining' => 'المتبقي',
        'delta' => 'فرق الرصيد',
        'provider' => 'مزود الخدمة',
        'opening_balance' => 'الرصيد الافتتاحي',
        'changed' => 'الحقول المعدلة',
        'category' => 'البند',
        'quantity' => 'الكمية',
        'stock_restored' => 'حالة إعادة المخزون',
        'product_id' => 'رقم المنتج',
        'total_value' => 'إجمالي القيمة',
        'kind' => 'نوع الحركة',
    ];

    private const TYPE_LABELS = [
        'send' => 'تحويل من المحفظة',
        'receive' => 'استلام على المحفظة',
        'reversal' => 'عكس',
        'settlement' => 'تحصيل آجل',
        'adjustment' => 'تسوية',
    ];

    private const KIND_LABELS = [
        'opening' => 'رصيد افتتاحي للدرج',
        'owner_deposit' => 'إيداع من المالك',
        'owner_withdraw' => 'سحب للمالك',
        'correction' => 'تسوية نقدية',
    ];

    public function index(Request $request)
    {
        abort_unless($request->user()->isSuperAdmin(), 403);

        $request->validate(['action' => ['nullable', Rule::in(array_keys(self::ACTION_FILTERS))]]);

        $logs = WalletAuditLog::query()
            ->when($request->filled('action'), function ($query) use ($request) {
                $query->where('action', 'like', addcslashes(trim((string) $request->query('action')), '%_\\').'%');
            })
            ->latest('id')
            ->paginate(50)
            ->withQueryString();

        $logs->getCollection()->transform(function (WalletAuditLog $log): WalletAuditLog {
            $log->action_label = self::ACTION_LABELS[$log->action] ?? 'حدث آخر';
            $log->subject_label = self::SUBJECT_LABELS[$log->subject_type] ?? 'عنصر';
            $log->meta_details = collect($log->meta ?? [])->map(fn ($value, string $key): array => [
                'label' => self::META_LABELS[$key] ?? 'تفصيل إضافي',
                'value' => $this->formatMetaValue($key, $value),
            ])->values();

            return $log;
        });

        return view('wallets.audit', [
            'logs' => $logs,
            'action' => (string) $request->query('action', ''),
            'actionFilters' => self::ACTION_FILTERS,
        ]);
    }

    private function formatMetaValue(string $key, mixed $value): string
    {
        if ($key === 'type') {
            return self::TYPE_LABELS[$value] ?? (string) $value;
        }

        if ($key === 'method' || $key === 'payment_method') {
            return ['cash' => 'نقدي', 'deferred' => 'آجل'][$value] ?? (string) $value;
        }

        if ($key === 'provider') {
            return Wallet::PROVIDERS[$value] ?? (string) $value;
        }

        if ($key === 'kind') {
            return self::KIND_LABELS[$value] ?? (string) $value;
        }

        if ($key === 'changed' && is_array($value)) {
            $fieldLabels = [
                'name' => 'الاسم',
                'provider' => 'مزود الخدمة',
                'identifier' => 'المعرّف',
                'holder_name' => 'اسم صاحب المحفظة',
                'notes' => 'ملاحظات',
            ];

            return collect($value)->map(fn ($field) => $fieldLabels[$field] ?? 'حقل آخر')->implode('، ');
        }

        if ($key === 'stock_restored') {
            return $value ? 'تمت إعادة المخزون' : 'لم تتم إعادة المخزون';
        }

        if (is_bool($value)) {
            return $value ? 'نعم' : 'لا';
        }

        if (in_array($key, ['amount', 'commission', 'fee', 'paid', 'remaining', 'delta', 'opening_balance', 'total_value'], true)
            && is_numeric($value)) {
            return number_format((float) $value, 2).' ج.م';
        }

        if (in_array($key, ['wallet_id', 'reversal_id', 'settlement_id', 'product_id'], true)) {
            return '#'.(string) $value;
        }

        return is_scalar($value) ? (string) $value : '—';
    }
}
