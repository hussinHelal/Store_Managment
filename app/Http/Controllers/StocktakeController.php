<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateStocktakeRequest;
use App\Models\Branch;
use App\Models\category;
use App\Models\Stocktake;
use App\Services\StocktakeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StocktakeController extends Controller
{
    public function index(): View
    {
        $stocktakes = Stocktake::query()->with(['branch', 'category'])
            ->withCount('items')->orderByDesc('id')->paginate(20)->withQueryString();
        $branches = Branch::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $categories = category::query()->orderBy('name')->get(['id', 'name']);

        return view('stocktake.index', compact('stocktakes', 'branches', 'categories'));
    }

    public function show(Stocktake $stocktake): View
    {
        $stocktake->load(['branch', 'items.product.category']);

        return view('stocktake.show', compact('stocktake'));
    }

    public function store(CreateStocktakeRequest $request, StocktakeService $service): RedirectResponse
    {
        $data = $request->validated();
        $take = $service->start($data, $request->user(), $data['idempotency_key'] ?? (string) Str::uuid());

        return redirect()->route('stocktake.show', $take)->with('success', 'بدأ الجرد وتم حفظ أرصدة النظام وقت البدء.');
    }

    public function count(Request $request, Stocktake $stocktake, int $item, StocktakeService $service): RedirectResponse
    {
        $data = $request->validate([
            'counted_qty' => ['required', 'integer', 'between:0,1000000'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);
        $service->count($stocktake, $item, (int) $data['counted_qty'], $data['note'] ?? null);

        return back()->with('success', 'تم حفظ العد.');
    }

    public function review(Stocktake $stocktake, StocktakeService $service): RedirectResponse
    {
        $service->review($stocktake);

        return back()->with('success', 'اكتمل العد وأصبح الجرد جاهزاً للمراجعة.');
    }

    public function approve(Request $request, Stocktake $stocktake, StocktakeService $service): RedirectResponse
    {
        abort_unless($request->user()->can('page.stocktake.approve'), 403);
        $service->approve($stocktake, $request->user());

        return back()->with('success', 'تم اعتماد الجرد وتسجيل فروق المخزون.');
    }

    public function cancel(Request $request, Stocktake $stocktake, StocktakeService $service): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:255']]);
        $service->cancel($stocktake, $data['reason']);

        return back()->with('success', 'تم إلغاء الجرد مع الاحتفاظ بسجله.');
    }
}
