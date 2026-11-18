import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;
import Chart from 'chart.js/auto';
import "@fortawesome/fontawesome-free/js/all.js";

import jQuery from 'jquery';
window.$ = window.jQuery = jQuery;
window.Chart = Chart;

window.createBarcodeScanner = function (video, onScan, onError, onReady = () => {}) {
    let active = true;
    let controls = null;
    let stream = null;
    let cameraRequest = null;
    const stop = () => {
        active = false;
        controls?.stop();
        controls = null;
        stream?.getTracks().forEach(track => track.stop());
        stream = null;
        cameraRequest?.then(requestedStream => requestedStream.getTracks().forEach(track => track.stop())).catch(() => {});
        cameraRequest = null;
        video.pause();
        video.srcObject = null;
        delete video.dataset.scanMode;
    };
    (async () => {
        try {
            const mediaDevices = navigator.mediaDevices;
            if (!mediaDevices || typeof mediaDevices.getUserMedia !== 'function') {
                throw new Error('المتصفح لا يتيح الكاميرا الحية لهذا العنوان. افتح الموقع عبر HTTPS أو localhost واسمح للمتصفح باستخدام الكاميرا.');
            }
            // Start the permission request synchronously from the button's click event,
            // before loading the decoder modules, so browsers can present the prompt.
            cameraRequest = mediaDevices.getUserMedia({
                audio: false,
                video: { facingMode: { ideal: 'environment' }, width: { ideal: 1280 }, height: { ideal: 720 } },
            });
            cameraRequest.catch(() => {}); // The rejection is surfaced below after ZXing loads.
            const [{ BrowserMultiFormatReader, BarcodeFormat }, { DecodeHintType }] = await Promise.all([
                import('@zxing/browser'), import('@zxing/library'),
            ]);
            if (!active) return;
            const hints = new Map([
                [DecodeHintType.POSSIBLE_FORMATS, [BarcodeFormat.EAN_13, BarcodeFormat.EAN_8, BarcodeFormat.UPC_A, BarcodeFormat.UPC_E, BarcodeFormat.CODE_128, BarcodeFormat.CODE_39, BarcodeFormat.CODE_93, BarcodeFormat.ITF, BarcodeFormat.CODABAR, BarcodeFormat.QR_CODE]],
                [DecodeHintType.TRY_HARDER, true],
            ]);
            const reader = new BrowserMultiFormatReader(hints);
            const onDecode = (result, error) => {
                if (!active) return;
                if (result) {
                    const barcode = result.getText().trim();
                    if (barcode) { stop(); onScan(barcode); }
                    return;
                }
                if (error && !['NotFoundException', 'ChecksumException', 'FormatException'].includes(error.name)) {
                    stop(); onError(error);
                }
            };
            // No device enumeration: use the stream requested directly above.
            stream = await cameraRequest;
            cameraRequest = null;
            if (!active) { stream.getTracks().forEach(track => track.stop()); return; }
            controls = await reader.decodeFromStream(stream, video, onDecode);
            if (!active) { controls.stop(); return; }
            video.dataset.scanMode = 'live';
            onReady();
        } catch (error) {
            if (!active) return;
            stop();
            const cameraErrors = {
                NotAllowedError: 'لم يسمح المتصفح باستخدام الكاميرا. اسمح بها من إعدادات الموقع ثم أعد المحاولة.',
                NotFoundError: 'لم يتم العثور على كاميرا متاحة في هذا الجهاز.',
                NotReadableError: 'تعذر فتح الكاميرا. أغلق التطبيقات الأخرى التي تستخدمها ثم أعد المحاولة.',
                OverconstrainedError: 'الكاميرا لا تدعم إعدادات الفيديو المطلوبة.',
            };
            onError(cameraErrors[error?.name] ? new Error(cameraErrors[error.name]) : error instanceof Error ? error : new Error('تعذر تشغيل قارئ الباركود. تحقق من إذن الكاميرا أو استخدم قارئًا متصلًا.'));
        }
    })();
    return { stop };
};document.addEventListener('DOMContentLoaded', () => {
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

    const sidebarGroups = [...document.querySelectorAll('.sidebar-group')];
    sidebarGroups.forEach((group) => {
        group.addEventListener('toggle', () => {
            if (!group.open) return;
            sidebarGroups.forEach((otherGroup) => {
                if (otherGroup !== group && otherGroup.open) otherGroup.open = false;
            });
        });
    });
});

$(function () {
    const themeToggle = $('#theme-toggle');
    const themeIcon = $('#theme-icon');
    const themeLabel = $('.theme-label');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

    function setTheme(theme) {
        document.documentElement.setAttribute('data-bs-theme', theme);
        localStorage.setItem('theme', theme);
        updateButton(theme);
    }

    function updateButton(theme) {
        if (theme === 'dark') {
            themeIcon.removeClass('fa-sun').addClass('fa-moon');
            themeLabel.text('الوضع الفاتح');
            themeToggle.attr('aria-label', 'التحويل إلى الوضع الفاتح');
        } else {
            themeIcon.removeClass('fa-moon').addClass('fa-sun');
            themeLabel.text('الوضع الداكن');
            themeToggle.attr('aria-label', 'التحويل إلى الوضع الداكن');
        }
    }

    let currentTheme = localStorage.getItem('theme') || (prefersDark ? 'dark' : 'light');
    setTheme(currentTheme);

    if (themeToggle.length) {
        themeToggle.on('click', function () {
            currentTheme = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
            setTheme(currentTheme);
        });
    }
});
