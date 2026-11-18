@extends('layouts.app')

@section('title', 'تشخيص الأجهزة')

@section('content')
<div class="container-fluid" dir="rtl">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div><h1 class="h3 mb-1">تشخيص الأجهزة</h1><p class="text-muted mb-0">اختبر دعم قارئ الباركود والطباعة على هذا الجهاز.</p></div>
        <button class="btn btn-outline-primary" type="button" id="runDiagnostics">إعادة الاختبار</button>
    </div>
    <div class="row g-3">
        <div class="col-lg-6"><section class="card h-100"><div class="card-header fw-semibold">قارئ الباركود</div><div class="card-body">
            <p class="mb-2">حالة واجهة الكاميرا: <strong id="cameraCapability">جارٍ الفحص…</strong></p>
            <p class="small text-muted">قد تمنع إعدادات المتصفح استخدام البث المباشر. استخدم التقاط صورة للباركود أو القارئ المتصل الذي يكتب الرقم في حقل.</p>
            <label class="form-label" for="diagnosticBarcode">جرّب قارئ باركود متصل أو أدخل الرقم</label>
            <input class="form-control" id="diagnosticBarcode" autocomplete="off" inputmode="none" placeholder="امسح الباركود هنا">
            <div class="mt-2" aria-live="polite" id="barcodeResult">امسح رمزًا للتحقق من إدخال القارئ.</div>
        </div></section></div>
        <div class="col-lg-6"><section class="card h-100"><div class="card-header fw-semibold">الطباعة</div><div class="card-body">
            <p>افتح مربع الطباعة للتأكد من أن المتصفح يتعرف على الطابعة.</p>
            <button class="btn btn-outline-secondary" id="printDiagnostic" type="button">طباعة صفحة اختبار</button>
            <p class="small text-muted mt-3 mb-0">اختيار الطابعة وإعداداتها يتم من نافذة الطباعة الخاصة بالنظام.</p>
        </div></section></div>
    </div>
</div>
<script>
(() => {
    const camera = document.getElementById('cameraCapability');
    const input = document.getElementById('diagnosticBarcode');
    const result = document.getElementById('barcodeResult');
    const run = () => {
        const available = Boolean(navigator.mediaDevices && typeof navigator.mediaDevices.getUserMedia === 'function');
        camera.textContent = available ? 'البث المباشر متاح' : 'البث المباشر غير متاح؛ استخدم التقاط الصورة أو القارئ المتصل';
        camera.className = available ? 'text-success' : 'text-warning-emphasis';
    };
    input.addEventListener('change', () => { result.textContent = input.value.trim() ? `تم استقبال الرمز: ${input.value.trim()}` : 'لم يتم استقبال رمز.'; });
    input.addEventListener('keydown', event => { if (event.key === 'Enter') { event.preventDefault(); input.dispatchEvent(new Event('change')); } });
    document.getElementById('runDiagnostics').addEventListener('click', run);
    document.getElementById('printDiagnostic').addEventListener('click', () => window.print());
    run();
})();
</script>
@endsection
