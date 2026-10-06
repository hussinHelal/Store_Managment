@extends('layouts.app')

@section('main')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h3 class="mb-0 d-flex align-items-center gap-2">
            <i class="fa-solid fa-cash-register" aria-hidden="true"></i>
            <span>الكاشير</span>
        </h3>
        <small class="text-body-secondary">
            F2 بحث &middot; F4 إتمام البيع &middot; Enter بعد مسح الباركود يضيف المنتج &middot; Enter على بحث فارغ ينتقل للمبلغ
        </small>
    </div>

    <div id="msg" class="alert d-none" role="alert"></div>
    <div id="successBox" class="d-none" role="status"></div>

    <div class="row g-3">
        {{-- Product search --}}
        <div class="col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body">
                    <label for="q" class="form-label fw-semibold">بحث بالاسم أو الباركود</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></span>
                        <input type="text" id="q" class="form-control form-control-lg" maxlength="100"
                               autocomplete="off" autofocus placeholder="امسح الباركود أو اكتب اسم المنتج">
                        <span class="input-group-text d-none" id="searchSpinner">
                            <span class="spinner-border spinner-border-sm" role="status" aria-label="جاري البحث"></span>
                        </span>
                    </div>
                </div>
                <div id="results" class="list-group list-group-flush border-top" style="max-height:60vh;overflow:auto"></div>
            </div>
        </div>

        {{-- Cart and payment --}}
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">السلة <span class="badge text-bg-secondary" id="cartCount">0</span></span>
                    <button type="button" class="btn btn-sm btn-outline-danger" id="clearCart">إفراغ السلة</button>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>المنتج</th>
                                <th style="width:11rem">الكمية</th>
                                <th class="text-nowrap">الإجمالي</th>
                                <th style="width:3rem"><span class="visually-hidden">حذف</span></th>
                            </tr>
                        </thead>
                        <tbody id="cartBody"></tbody>
                    </table>
                </div>

                <div class="card-body border-top">
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label for="customer" class="form-label small mb-1">اسم العميل</label>
                            <input type="text" id="customer" class="form-control" maxlength="255" autocomplete="off"
                                   placeholder="اختياري عند الدفع الكامل">
                        </div>
                        <div class="col-md-6">
                            <label for="received" class="form-label small mb-1">المبلغ المستلم</label>
                            <div class="input-group">
                                <input type="text" id="received" class="form-control" inputmode="decimal"
                                       autocomplete="off" placeholder="فارغ = دفع كامل">
                                <button type="button" class="btn btn-outline-secondary" id="payFull">كامل</button>
                            </div>
                            <div id="receivedHint" class="form-text text-danger"></div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center fs-5 mb-1">
                        <span>الإجمالي</span>
                        <span class="fw-bold" id="totalOut">0.00</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center fs-5 mb-3">
                        <span id="diffLabel">الباقي للعميل</span>
                        <span class="fw-bold text-success" id="diffOut">0.00</span>
                    </div>

                    <button type="button" class="btn btn-success btn-lg w-100" id="checkoutBtn" disabled>
                        <i class="fa-solid fa-check" aria-hidden="true"></i>
                        <span id="checkoutLabel">إتمام البيع (F4)</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(() => {
    'use strict';

    const CFG = {
        searchUrl: @json(route('cashier.products.search')),
        checkoutUrl: @json(route('cashier.checkout')),
        loginUrl: @json(route('showLogin')),
        csrf: @json(csrf_token()),
        currency: 'ج.م',
        // Per-user key so a shared PC never shows one cashier's cart to another.
        storageKey: 'cashier.cart.v1.' + @json((string) auth()->id()),
    };
    const HEADERS = { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' };

    const $ = (id) => document.getElementById(id);
    const qInput = $('q'), spinner = $('searchSpinner'), resultsBox = $('results');
    const cartBody = $('cartBody'), cartCount = $('cartCount');
    const customerInput = $('customer'), receivedInput = $('received'), receivedHint = $('receivedHint');
    const totalOut = $('totalOut'), diffLabel = $('diffLabel'), diffOut = $('diffOut');
    const checkoutBtn = $('checkoutBtn'), checkoutLabel = $('checkoutLabel');
    const clearBtn = $('clearCart'), payFullBtn = $('payFull');
    const msgBox = $('msg'), successBox = $('successBox');

    // ---------- helpers ----------
    // Safe DOM builder: text always goes through textContent/append, never innerHTML.
    const el = (tag, attrs = {}, ...kids) => {
        const node = document.createElement(tag);
        for (const [key, value] of Object.entries(attrs)) {
            if (key === 'class') node.className = value;
            else if (key === 'text') node.textContent = value;
            else if (key.startsWith('on')) node.addEventListener(key.slice(2), value);
            else if (value !== false && value != null) node.setAttribute(key, value === true ? '' : value);
        }
        kids.flat().forEach((kid) => { if (kid != null) node.append(kid); });
        return node;
    };

    const fmt = (cents) => (cents / 100).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const money = (cents) => fmt(cents) + ' ' + CFG.currency;
    const toCents = (value) => Math.round(parseFloat(value) * 100);

    // "12,5" / "١٢٫٥" / "12.50" -> cents. Returns null when empty, NaN when invalid.
    function parseCents(raw) {
        if (raw == null) return null;
        let s = String(raw).trim();
        if (s === '') return null;
        s = s.replace(/[٠-٩]/g, (d) => '٠١٢٣٤٥٦٧٨٩'.indexOf(d)).replace(/[٫,]/g, '.');
        if (s === '.' || !/^\d*\.?\d{0,2}$/.test(s)) return NaN;
        return Math.round(parseFloat(s) * 100);
    }

    // crypto.randomUUID only exists on secure origins; the LAN address is plain http.
    function uuid() {
        if (window.crypto && typeof crypto.randomUUID === 'function') return crypto.randomUUID();
        const bytes = new Uint8Array(16);
        if (window.crypto && crypto.getRandomValues) crypto.getRandomValues(bytes);
        else for (let i = 0; i < 16; i++) bytes[i] = Math.floor(Math.random() * 256);
        return Array.from(bytes, (b) => b.toString(16).padStart(2, '0')).join('');
    }

    // ---------- state (cart survives refresh and server errors) ----------
    const blank = () => ({ items: [], customer: '', received: '', token: uuid() });
    const validItem = (i) => i && Number.isInteger(i.id) && typeof i.name === 'string'
        && Number.isInteger(i.price) && i.price >= 0 && Number.isInteger(i.stock)
        && Number.isInteger(i.qty) && i.qty >= 1;

    function load() {
        try {
            const saved = JSON.parse(localStorage.getItem(CFG.storageKey));
            if (saved && Array.isArray(saved.items) && typeof saved.token === 'string') {
                return { ...blank(), ...saved, items: saved.items.filter(validItem) };
            }
        } catch (e) { /* ignore corrupt or unavailable storage */ }
        return blank();
    }

    function save() {
        try { localStorage.setItem(CFG.storageKey, JSON.stringify(state)); } catch (e) { /* storage full or blocked */ }
    }

    let state = load();
    let busy = false;

    // ---------- messages ----------
    let msgTimer;
    function showMsg(type, text, sticky = false) {
        clearTimeout(msgTimer);
        msgBox.className = 'alert alert-' + type;
        msgBox.textContent = text;
        if (!sticky) msgTimer = setTimeout(hideMsg, 7000);
    }
    function hideMsg() { msgBox.className = 'alert d-none'; msgBox.textContent = ''; }

    // ---------- cart ----------
    const totalCents = () => state.items.reduce((sum, i) => sum + i.price * i.qty, 0);

    function commit() {
        save();
        renderCart();
        renderTotals();
    }

    function addItem(product) {
        hideSuccess();
        const stock = Number(product.stock) || 0;
        if (stock <= 0) { showMsg('danger', '«' + product.name + '» غير متوفر في المخزون.'); return; }

        const price = toCents(product.price);
        const existing = state.items.find((i) => i.id === product.id);

        if (existing) {
            existing.stock = stock;
            existing.price = price;
            if (existing.qty + 1 > stock) showMsg('warning', 'المتاح من «' + product.name + '» هو ' + stock + ' فقط.');
            else existing.qty += 1;
        } else {
            state.items.push({ id: product.id, name: product.name, price, stock, qty: 1 });
        }
        commit();
    }

    function setQty(id, raw) {
        const item = state.items.find((i) => i.id === id);
        if (!item) return;
        let qty = parseInt(raw, 10);
        if (!Number.isInteger(qty) || qty < 1) qty = 1;
        if (qty > item.stock) {
            qty = item.stock;
            showMsg('warning', 'المتاح من «' + item.name + '» هو ' + item.stock + ' فقط.');
        }
        item.qty = qty;
        commit();
    }

    function removeItem(id) {
        state.items = state.items.filter((i) => i.id !== id);
        commit();
    }

    function renderCart() {
        cartBody.replaceChildren();

        if (!state.items.length) {
            cartBody.append(el('tr', {}, el('td', {
                colspan: 4,
                class: 'text-center text-body-secondary py-4',
                text: 'السلة فارغة. امسح الباركود أو ابحث عن منتج.',
            })));
        }

        state.items.forEach((item) => {
            const qtyInput = el('input', {
                type: 'number', class: 'form-control text-center', min: 1, max: Math.max(item.stock, 1),
                value: item.qty, inputmode: 'numeric', 'aria-label': 'الكمية',
                onchange: (e) => setQty(item.id, e.target.value),
                onkeydown: (e) => { if (e.key === 'Enter') { e.preventDefault(); e.target.blur(); qInput.focus(); } },
            });

            cartBody.append(el('tr', {},
                el('td', {},
                    el('div', { class: 'fw-semibold', text: item.name }),
                    el('div', {
                        class: 'small text-body-secondary',
                        text: money(item.price) + (item.stock <= 5 ? ' · المتبقي ' + item.stock : ''),
                    })),
                el('td', {},
                    el('div', { class: 'input-group input-group-sm', dir: 'ltr' },
                        el('button', { type: 'button', class: 'btn btn-outline-secondary', 'aria-label': 'تقليل', text: '−', onclick: () => setQty(item.id, item.qty - 1) }),
                        qtyInput,
                        el('button', { type: 'button', class: 'btn btn-outline-secondary', 'aria-label': 'زيادة', text: '+', onclick: () => setQty(item.id, item.qty + 1) }))),
                el('td', { class: 'text-nowrap fw-semibold', text: money(item.price * item.qty) }),
                el('td', {}, el('button', {
                    type: 'button', class: 'btn btn-sm btn-outline-danger', 'aria-label': 'حذف ' + item.name,
                    onclick: () => removeItem(item.id),
                }, el('i', { class: 'fa-solid fa-xmark', 'aria-hidden': 'true' })))));
        });
    }

    function renderTotals() {
        const total = totalCents();
        const received = parseCents(state.received);
        const invalid = Number.isNaN(received);
        const effective = (received === null || invalid) ? total : received;

        cartCount.textContent = String(state.items.reduce((sum, i) => sum + i.qty, 0));
        totalOut.textContent = money(total);

        receivedInput.classList.toggle('is-invalid', invalid);
        receivedHint.textContent = invalid ? 'اكتب مبلغاً صحيحاً (حتى رقمين بعد الفاصلة).' : '';

        if (effective >= total) {
            diffLabel.textContent = 'الباقي للعميل';
            diffOut.textContent = money(effective - total);
            diffOut.className = 'fw-bold text-success';
        } else {
            diffLabel.textContent = 'المتبقي (دين على العميل)';
            diffOut.textContent = money(total - effective);
            diffOut.className = 'fw-bold text-danger';
        }

        checkoutBtn.disabled = busy || invalid || !state.items.length;
        checkoutLabel.textContent = busy ? 'جاري الحفظ…' : 'إتمام البيع (F4)';
    }

    // ---------- search ----------
    let searchTimer, searchController, searchSeq = 0;

    function scheduleSearch() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => runSearch(false), 250);
    }

    async function runSearch(fromEnter) {
        const term = qInput.value.trim();
        searchController?.abort();
        searchController = new AbortController();
        const seq = ++searchSeq;
        spinner.classList.remove('d-none');

        try {
            const res = await fetch(CFG.searchUrl + '?q=' + encodeURIComponent(term), {
                headers: HEADERS, credentials: 'same-origin', signal: searchController.signal,
            });
            if (res.status === 401) { window.location.href = CFG.loginUrl; return; }
            if (!res.ok) throw new Error('HTTP ' + res.status);

            const json = await res.json();
            if (seq !== searchSeq) return; // a newer search replaced this one

            renderResults(json.data, term);

            // Scanner flow: Enter on an exact barcode, or a single match, adds it directly.
            if (fromEnter && term && json.data.length && (json.exact || json.data.length === 1)) {
                addItem(json.data[0]);
                qInput.value = '';
                runSearch(false);
            }
        } catch (e) {
            if (e.name === 'AbortError') return;
            showMsg('danger', 'تعذر البحث الآن. تحقق من الاتصال وحاول مرة أخرى.');
        } finally {
            if (seq === searchSeq) spinner.classList.add('d-none');
        }
    }

    function renderResults(list, term) {
        resultsBox.replaceChildren();

        if (!list.length) {
            resultsBox.append(el('div', {
                class: 'list-group-item text-center text-body-secondary py-4',
                text: term ? 'لا توجد نتائج لـ «' + term + '»' : 'لا توجد منتجات متاحة',
            }));
            return;
        }

        if (!term) {
            resultsBox.append(el('div', { class: 'list-group-item small text-body-secondary bg-body-tertiary', text: 'الأكثر مبيعاً' }));
        }

        list.forEach((product) => {
            const out = Number(product.stock) <= 0;
            const low = !out && Number(product.stock) <= 5;

            resultsBox.append(el('button', {
                type: 'button',
                class: 'list-group-item list-group-item-action d-flex align-items-center gap-2' + (out ? ' disabled' : ''),
                disabled: out,
                onclick: () => { addItem(product); qInput.focus(); qInput.select(); },
            },
                product.image_url ? el('img', {
                    src: product.image_url, alt: '', width: 40, height: 40, loading: 'lazy',
                    class: 'rounded object-fit-cover flex-shrink-0', onerror: (e) => e.target.remove(),
                }) : null,
                el('div', { class: 'flex-grow-1 text-truncate' },
                    el('div', { class: 'fw-semibold text-truncate', text: product.name }),
                    product.barcode ? el('div', { class: 'small text-body-secondary', text: product.barcode }) : null),
                el('div', { class: 'text-nowrap' },
                    el('div', { class: 'fw-semibold', text: money(toCents(product.price)) }),
                    el('span', {
                        class: 'badge ' + (out ? 'text-bg-danger' : (low ? 'text-bg-warning' : 'text-bg-secondary')),
                        text: out ? 'نفد' : 'المخزون: ' + product.stock,
                    }))));
        });
    }

    // ---------- checkout ----------
    function hideSuccess() { successBox.className = 'd-none'; successBox.replaceChildren(); }

    function newSale() { hideSuccess(); qInput.focus(); }

    function showSuccess(r, clientTotalCents) {
        const row = (label, value, cls = '') => el('div', { class: 'd-flex justify-content-between ' + cls },
            el('span', { text: label }), el('span', { text: value }));
        const priceChanged = toCents(r.total) !== clientTotalCents;

        successBox.className = 'alert alert-success';
        successBox.replaceChildren(
            el('div', { class: 'fw-bold fs-5 mb-2', text: 'تمت العملية بنجاح — فاتورة رقم ' + r.invoice_number }),
            row('الإجمالي', money(toCents(r.total))),
            row('المدفوع', money(toCents(r.paid))),
            toCents(r.remaining) > 0 ? row('المتبقي (دين)', money(toCents(r.remaining)), 'text-danger fw-bold') : null,
            row('الباقي للعميل', money(toCents(r.change)), 'fs-4 fw-bold'),
            priceChanged ? el('div', {
                class: 'small mt-2',
                text: 'تنبيه: السعر تغيّر عن المعروض في السلة. المعتمد هو المبلغ المسجل في الفاتورة.',
            }) : null,
            el('div', { class: 'd-flex gap-2 mt-3' },
                el('a', { href: r.print_url, target: '_blank', rel: 'noopener', class: 'btn btn-primary', text: 'طباعة الفاتورة' }),
                el('button', { type: 'button', class: 'btn btn-outline-secondary', text: 'عملية جديدة', onclick: newSale })));
        successBox.scrollIntoView({ block: 'nearest' });
    }

    function explainError(status, json) {
        const first = json && json.errors ? Object.values(json.errors).flat()[0] : null;
        switch (status) {
            case 401: window.location.href = CFG.loginUrl; return '';
            case 403: return 'ليس لديك صلاحية لإتمام البيع.';
            case 419: return 'انتهت صلاحية الصفحة. حدّث الصفحة (F5). سلتك محفوظة ولن تضيع.';
            case 429: return 'محاولات كثيرة. انتظر لحظة ثم حاول مرة أخرى.';
            case 409: return (json && json.message) || 'العملية قيد التنفيذ. انتظر لحظة.';
            case 422: return first || (json && json.message) || 'بيانات غير صالحة. راجع السلة.';
            default: return (json && json.message) || 'حدث خطأ في الخادم. لم يتم حفظ أي شيء، حاول مرة أخرى.';
        }
    }

    async function checkout() {
        if (busy || !state.items.length) return;

        const total = totalCents();
        const received = parseCents(state.received);

        if (Number.isNaN(received)) { showMsg('warning', 'المبلغ المستلم غير صالح.'); receivedInput.focus(); return; }
        if ((received !== null && received < total) && !state.customer.trim()) {
            showMsg('warning', 'عند الدفع الجزئي لازم تكتب اسم العميل لتسجيل المتبقي كدين.');
            customerInput.focus();
            return;
        }

        busy = true;
        renderTotals();
        hideMsg();

        try {
            const res = await fetch(CFG.checkoutUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { ...HEADERS, 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CFG.csrf },
                body: JSON.stringify({
                    token: state.token,
                    customer: state.customer.trim() || null,
                    items: state.items.map((i) => ({ product_id: i.id, quantity: i.qty })),
                    received: received === null ? null : (received / 100).toFixed(2),
                }),
            });
            const json = await res.json().catch(() => ({}));

            if (res.ok && json.ok) {
                state = blank();
                customerInput.value = '';
                receivedInput.value = '';
                commit();
                showSuccess(json, total);
                runSearch(false); // refresh stock numbers
                return;
            }

            const message = explainError(res.status, json);
            if (message) showMsg('danger', message, true);
        } catch (e) {
            // The same token is reused on retry, so pressing again can never create a second invoice.
            showMsg('danger', 'تعذر الاتصال بالخادم. سلتك محفوظة، اضغط إتمام البيع مرة أخرى بأمان.', true);
        } finally {
            busy = false;
            renderTotals();
        }
    }

    // ---------- wiring ----------
    qInput.addEventListener('input', scheduleSearch);
    qInput.addEventListener('keydown', (e) => {
        if (e.key !== 'Enter') return;
        e.preventDefault();
        clearTimeout(searchTimer);
        if (qInput.value.trim() === '') { if (state.items.length) receivedInput.focus(); return; }
        runSearch(true);
    });

    customerInput.addEventListener('input', () => { state.customer = customerInput.value; save(); renderTotals(); });
    receivedInput.addEventListener('input', () => { state.received = receivedInput.value; save(); renderTotals(); });
    receivedInput.addEventListener('keydown', (e) => { if (e.key === 'Enter') { e.preventDefault(); checkout(); } });

    payFullBtn.addEventListener('click', () => {
        state.received = (totalCents() / 100).toFixed(2);
        receivedInput.value = state.received;
        save();
        renderTotals();
    });

    clearBtn.addEventListener('click', () => {
        if (!state.items.length) return;
        if (!window.confirm('إفراغ السلة؟')) return;
        state = blank();
        customerInput.value = '';
        receivedInput.value = '';
        commit();
        qInput.focus();
    });

    checkoutBtn.addEventListener('click', checkout);

    document.addEventListener('keydown', (e) => {
        if (e.key === 'F2') { e.preventDefault(); qInput.focus(); qInput.select(); }
        else if (e.key === 'F4') { e.preventDefault(); checkout(); }
        else if (e.key === 'Escape' && document.activeElement === qInput && qInput.value) {
            qInput.value = '';
            scheduleSearch();
        }
    });

    customerInput.value = state.customer;
    receivedInput.value = state.received;
    commit();
    runSearch(false);
    qInput.focus();
})();
</script>
@endpush
