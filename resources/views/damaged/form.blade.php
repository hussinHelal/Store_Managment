@extends('layouts.app')

@section('main')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h3 class="mb-0">تسجيل هالِك</h3>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('damaged.store') }}" enctype="multipart/form-data" id="damagedForm" autocomplete="off" novalidate>
        @csrf
        <input type="hidden" name="product_id" id="productId" value="{{ old('product_id', $preselected?->id) }}">
        <input type="hidden" name="idempotency_key" id="idemKey" value="{{ old('idempotency_key') }}">

        <div class="row g-3">
            <div class="col-lg-7">
                <div class="card shadow-sm mb-3">
                    <div class="card-header fw-semibold">1. المنتج</div>
                    <div class="card-body">
                        <label for="productSearch" class="form-label">ابحث بالاسم أو امسح الباركود</label>
                        <div class="input-group mb-2">
                            <span class="input-group-text"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></span>
                            <input type="text" id="productSearch" class="form-control form-control-lg" maxlength="100" placeholder="اسم المنتج أو الباركود">
                        </div>
                        <div id="productResults" class="list-group mb-3" style="max-height:260px;overflow:auto"></div>

                        <div id="selectedProduct" class="alert alert-secondary d-none mb-0" role="status"></div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header fw-semibold">2. تفاصيل الهالِك</div>
                    <div class="card-body row g-3">
                        <div class="col-md-4">
                            <label for="quantity" class="form-label">الكمية</label>
                            <input type="text" inputmode="numeric" id="quantity" name="quantity" dir="ltr"
                                   class="form-control @error('quantity') is-invalid @enderror" value="{{ old('quantity', 1) }}" required>
                            <div class="form-text" id="stockHint"></div>
                            @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label for="reason" class="form-label">السبب</label>
                            <select id="reason" name="reason" class="form-select @error('reason') is-invalid @enderror" required>
                                <option value="">اختر السبب</option>
                                @foreach ($reasons as $key => $label)
                                    <option value="{{ $key }}" @selected(old('reason') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label for="damaged_on" class="form-label">تاريخ الهالِك</label>
                            <input type="date" id="damaged_on" name="damaged_on" max="{{ $today }}"
                                   class="form-control @error('damaged_on') is-invalid @enderror" value="{{ old('damaged_on', $today) }}" required>
                            @error('damaged_on')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="unit_value" class="form-label">قيمة الوحدة (ج.م)</label>
                            <input type="text" inputmode="decimal" id="unit_value" name="unit_value" dir="ltr"
                                   class="form-control @error('unit_value') is-invalid @enderror" value="{{ old('unit_value') }}">
                            <div class="form-text">تُملأ بسعر البيع تلقائياً. اكتب سعر التكلفة لو تريد حساب الخسارة الحقيقية.</div>
                            @error('unit_value')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="image" class="form-label">صورة (اختياري)</label>
                            <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp"
                                   class="form-control @error('image') is-invalid @enderror">
                            @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <img id="imagePreview" src="" alt="معاينة الصورة" class="img-thumbnail mt-2 d-none" style="max-height:120px">
                        </div>
                        <div class="col-12">
                            <label for="notes" class="form-label">ملاحظات</label>
                            <textarea id="notes" name="notes" rows="2" maxlength="500" class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
                            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card shadow-sm position-sticky" style="top:1rem" aria-live="polite">
                    <div class="card-header fw-semibold">ملخص قبل الحفظ</div>
                    <div class="card-body" id="summary"></div>
                    <div class="card-footer">
                        <div class="form-actions d-flex flex-wrap align-items-center gap-2">
                        <button type="submit" class="btn btn-danger btn-lg flex-grow-1" id="submitBtn" disabled>
                            <i class="fa-solid fa-check" aria-hidden="true"></i>
                            <span>تسجيل الهالِك</span>
                        </button>
                        <a href="{{ route('damaged.index') }}" class="btn btn-outline-secondary">رجوع</a>
                        </div>
                        <div class="form-text text-center mt-2">سيُخصم من المخزون فوراً ويمكن إلغاء السجل لاحقاً لإرجاع الكمية.</div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
(() => {
    'use strict';

    const SEARCH_URL = @json(route('damaged.products.search'));
    const PRESELECTED = @json($preselected ? [
        'id' => $preselected->id, 'name' => $preselected->name, 'barcode' => $preselected->barcode,
        'stock' => (int) $preselected->stock, 'price' => number_format((float) $preselected->price, 2, '.', ''),
    ] : null);

    const $ = (id) => document.getElementById(id);
    const form = $('damagedForm'), productId = $('productId'), keyInput = $('idemKey');
    const search = $('productSearch'), results = $('productResults'), selectedBox = $('selectedProduct');
    const qty = $('quantity'), unit = $('unit_value'), submitBtn = $('submitBtn'), summary = $('summary');
    const reason = $('reason'), stockHint = $('stockHint');
    let selected = null, timer, controller, seq = 0;

    const ascii = (s) => String(s ?? '').replace(/[٠-٩]/g, (d) => '٠١٢٣٤٥٦٧٨٩'.indexOf(d)).replace(/٫/g, '.').replace(/٬/g, '');
    const cents = (s) => { const v = ascii(s).trim(); return (v === '' || v === '.' || !/^\d*\.?\d{0,2}$/.test(v)) ? null : Math.round(parseFloat(v) * 100); };
    const fmt = (c) => (c / 100).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    function uuid() {
        if (window.crypto && typeof crypto.randomUUID === 'function') return crypto.randomUUID();
        const bytes = new Uint8Array(16);
        if (window.crypto && crypto.getRandomValues) crypto.getRandomValues(bytes);
        else for (let i = 0; i < 16; i++) bytes[i] = Math.floor(Math.random() * 256);
        return Array.from(bytes, (b) => b.toString(16).padStart(2, '0')).join('');
    }
    if (!keyInput.value) keyInput.value = uuid();

    const el = (tag, attrs = {}, ...kids) => {
        const node = document.createElement(tag);
        for (const [key, value] of Object.entries(attrs)) {
            if (key === 'class') node.className = value;
            else if (key === 'text') node.textContent = value;
            else if (key.startsWith('on')) node.addEventListener(key.slice(2), value);
            else if (value !== false && value != null) node.setAttribute(key, value === true ? '' : value);
        }
        kids.flat().forEach((k) => { if (k != null) node.append(k); });
        return node;
    };

    function choose(product, keepUnit = false) {
        selected = product;
        productId.value = product.id;
        if (!keepUnit) unit.value = product.price;
        selectedBox.className = 'alert alert-secondary mb-0';
        selectedBox.replaceChildren(
            el('div', { class: 'fw-semibold', text: product.name }),
            el('div', { class: 'small', text: 'المخزون الحالي: ' + product.stock + ' · سعر البيع: ' + product.price + ' ج.م' + (product.barcode ? ' · ' + product.barcode : '') }));
        results.replaceChildren();
        search.value = '';
        stockHint.textContent = 'المتاح في المخزون: ' + product.stock;
        render();
        qty.focus();
        qty.select();
    }

    function renderResults(list, term) {
        results.replaceChildren();
        if (!list.length) {
            if (term) results.append(el('div', { class: 'list-group-item text-center text-body-secondary', text: 'لا توجد نتائج لـ «' + term + '»' }));
            return;
        }
        list.forEach((p) => {
            const empty = p.stock <= 0;
            results.append(el('button', {
                type: 'button', disabled: empty,
                class: 'list-group-item list-group-item-action d-flex align-items-center gap-2' + (empty ? ' disabled' : ''),
                onclick: () => choose(p),
            },
                p.image_url ? el('img', { src: p.image_url, alt: '', width: 40, height: 40, loading: 'lazy', class: 'rounded object-fit-cover flex-shrink-0', onerror: (e) => e.target.remove() }) : null,
                el('div', { class: 'flex-grow-1 text-truncate' },
                    el('div', { class: 'fw-semibold text-truncate', text: p.name }),
                    p.barcode ? el('small', { class: 'text-body-secondary', text: p.barcode }) : null),
                el('span', { class: 'badge ' + (empty ? 'text-bg-danger' : 'text-bg-secondary'), text: empty ? 'نفد' : 'المخزون: ' + p.stock })));
        });
    }

    async function runSearch(fromEnter) {
        const term = search.value.trim();
        if (term === '') { results.replaceChildren(); return; }
        controller?.abort();
        controller = new AbortController();
        const mine = ++seq;
        try {
            const res = await fetch(SEARCH_URL + '?q=' + encodeURIComponent(term), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin', signal: controller.signal,
            });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const json = await res.json();
            if (mine !== seq) return;
            renderResults(json.data, term);
            // A barcode scanner types the code and presses Enter: pick an exact barcode match directly.
            if (fromEnter) {
                const exact = json.data.find((p) => p.barcode === term && p.stock > 0);
                if (exact) choose(exact);
            }
        } catch (e) {
            if (e.name !== 'AbortError') results.replaceChildren(el('div', { class: 'list-group-item text-danger', text: 'تعذر البحث الآن. حاول مرة أخرى.' }));
        }
    }

    search.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(() => runSearch(false), 250); });
    search.addEventListener('keydown', (e) => { if (e.key === 'Enter') { e.preventDefault(); clearTimeout(timer); runSearch(true); } });

    function render() {
        const q = parseInt(ascii(qty.value), 10);
        const unitCents = cents(unit.value);
        const problems = [];

        if (!selected) problems.push('اختر المنتج أولاً.');
        else if (!Number.isInteger(q) || q < 1) problems.push('اكتب كمية صحيحة (1 أو أكثر).');
        else if (q > selected.stock) problems.push('الكمية أكبر من المخزون المتاح (' + selected.stock + ').');
        if (unit.value.trim() !== '' && unitCents === null) problems.push('قيمة الوحدة غير صالحة.');
        if (!reason.value) problems.push('اختر سبب الهالِك.');

        const nodes = [];
        if (selected) {
            const valid = Number.isInteger(q) && q >= 1;
            const perUnit = unitCents ?? Math.round(parseFloat(selected.price) * 100);
            const row = (label, value, cls = '') => el('div', { class: 'd-flex justify-content-between py-1 ' + cls },
                el('span', { text: label }), el('span', { class: 'fw-semibold', text: value }));

            nodes.push(el('div', { class: 'fw-semibold mb-2', text: selected.name }));
            nodes.push(row('المخزون الحالي', String(selected.stock)));
            if (valid) {
                nodes.push(row('المخزون بعد الخصم', String(selected.stock - q), selected.stock - q < 0 ? 'text-danger' : ''));
                nodes.push(row('قيمة الوحدة', fmt(perUnit) + ' ج.م'));
                nodes.push(row('إجمالي قيمة الهالِك', fmt(perUnit * q) + ' ج.م', 'fs-5 text-danger'));
            }
        } else {
            nodes.push(el('div', { class: 'text-body-secondary', text: 'اختر منتجاً لعرض الملخص.' }));
        }
        problems.forEach((text) => nodes.push(el('div', { class: 'alert alert-warning py-2 mt-2 mb-0 small', text })));

        summary.replaceChildren(...nodes);
        submitBtn.disabled = problems.length > 0;
    }

    [qty, unit].forEach((input) => input.addEventListener('input', () => { input.value = ascii(input.value); render(); }));
    reason.addEventListener('change', render);

    $('image').addEventListener('change', (event) => {
        const file = event.target.files && event.target.files[0];
        const preview = $('imagePreview');
        if (!file) { preview.classList.add('d-none'); return; }
        preview.src = URL.createObjectURL(file);
        preview.classList.remove('d-none');
    });

    form.addEventListener('submit', (event) => {
        if (submitBtn.disabled) { event.preventDefault(); return; }
        submitBtn.disabled = true;                     // the idempotency key also blocks a second submit on the server
        submitBtn.querySelector('span').textContent = 'جاري الحفظ…';
    });

    if (PRESELECTED && String(PRESELECTED.id) === String(productId.value)) choose(PRESELECTED, unit.value !== '');
    render();
})();
</script>
@endpush
