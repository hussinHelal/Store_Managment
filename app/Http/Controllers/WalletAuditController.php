<?php

namespace App\Http\Controllers;

use App\Models\WalletAuditLog;
use Illuminate\Http\Request;

/** Who did what, when, from where. Superadmin only. */
class WalletAuditController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->isSuperAdmin(), 403);

        $request->validate(['action' => ['nullable', 'string', 'max:60']]);

        $logs = WalletAuditLog::query()
            ->when($request->filled('action'), function ($query) use ($request) {
                $query->where('action', 'like', addcslashes(trim((string) $request->query('action')), '%_\\').'%');
            })
            ->latest('id')
            ->paginate(50)
            ->withQueryString();

        return view('wallets.audit', ['logs' => $logs, 'action' => (string) $request->query('action', '')]);
    }
}
