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
            <p class="small mb-2">وضع الصفحة: <span id="cameraContext" class="fw-semibold"></span></p>
            <p class="small text-muted">يطلب المتصفح إذن الكاميرا عند بدء المسح. استخدم HTTPS، أو localhost إذا كان المتصفح وخادم النظام على نفس الجهاز. عنوان الشبكة المحلي مثل 192.168.x.x عبر HTTP لا يسمح بطلب الإذن. القارئ المتصل يعمل بإدخال الباركود في الحقل أدناه.</p>
            <div class="d-flex flex-wrap gap-2 mb-3">
                <button class="btn btn-primary" type="button" id="startDiagnosticScan">بدء المسح بالكاميرا</button>
                <button class="btn btn-outline-secondary d-none" type="button" id="stopDiagnosticScan">إيقاف الكاميرا</button>
            </div>
            <video id="diagnosticCameraVideo" class="w-100 rounded bg-dark d-none" playsinline muted aria-label="معاينة الكاميرا لقراءة الباركود"></video>
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
    const video = document.getElementById('diagnosticCameraVideo');
    const startButton = document.getElementById('startDiagnosticScan');
    const stopButton = document.getElementById('stopDiagnosticScan');
    let scanSession = null;
    const run = () => {
        const available = Boolean(navigator.mediaDevices && typeof navigator.mediaDevices.getUserMedia === 'function');
        const localHost = ['localhost', '127.0.0.1', '::1'].includes(window.location.hostname);
        camera.textContent = available
            ? 'البث المباشر متاح، وسيطلب المتصفح الإذن عند بدء المسح'
            : window.isSecureContext
                ? 'المتصفح أو الجهاز عطّل واجهة الكاميرا'
                : localHost
                    ? 'localhost غير آمن في إعداد المتصفح الحالي؛ تحقق من إعدادات المتصفح'
                    : 'اتصال HTTP عبر عنوان شبكة؛ افتح الموقع عبر HTTPS. localhost يعمل فقط على نفس جهاز الخادم.';
        camera.className = available ? 'text-success' : 'text-warning-emphasis';
        document.getElementById('cameraContext').textContent = window.isSecureContext
            ? 'اتصال آمن؛ يمكن بدء طلب الإذن.'
            : 'اتصال غير آمن؛ المتصفح يحجب الكاميرا ولا يمكن للصفحة إظهار طلب الإذن.';
    };
    const stopScan = () => {
        scanSession?.stop();
        scanSession = null;
        video.srcObject?.getTracks().forEach(track => track.stop());
        video.srcObject = null;
        video.pause();
        video.classList.add('d-none');
        stopButton.classList.add('d-none');
        startButton.disabled = false;
    };
    startButton.addEventListener('click', () => {
        if (!window.createBarcodeScanner) {
            result.textContent = 'تعذر تحميل قارئ الباركود. أعد تحميل الصفحة.';
            return;
        }
        stopScan();
        startButton.disabled = true;
        result.textContent = 'جارٍ طلب إذن الكاميرا من المتصفح…';
        video.classList.remove('d-none');
        scanSession = window.createBarcodeScanner(video, barcode => {
            input.value = barcode;
            result.textContent = `تمت قراءة الباركود: ${barcode}`;
            stopScan();
        }, error => {
            result.textContent = error?.message || 'تعذر تشغيل الكاميرا. تحقق من إذن الموقع واتصال HTTPS.';
            video.classList.add('d-none');
            startButton.disabled = false;
            stopButton.classList.add('d-none');
        }, () => {
            result.textContent = 'الكاميرا تعمل مباشرة. قرّب الباركود أمامها.';
            startButton.disabled = true;
            stopButton.classList.remove('d-none');
        });
    });
    stopButton.addEventListener('click', stopScan);
    input.addEventListener('change', () => { result.textContent = input.value.trim() ? `تم استقبال الرمز: ${input.value.trim()}` : 'لم يتم استقبال رمز.'; });
    input.addEventListener('keydown', event => { if (event.key === 'Enter') { event.preventDefault(); input.dispatchEvent(new Event('change')); } });
    document.getElementById('runDiagnostics').addEventListener('click', run);
    document.getElementById('printDiagnostic').addEventListener('click', () => window.print());
    run();
    window.addEventListener('pagehide', stopScan, { once: true });
})();
</script>
@endsection
