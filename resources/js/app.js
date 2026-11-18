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
    const stop = () => {
        active = false;
        controls?.stop();
        controls = null;
        video.srcObject?.getTracks().forEach(track => track.stop());
        video.pause();
        video.srcObject = null;
    };
    (async () => {
        try {
            const mediaDevices = navigator.mediaDevices;
            if (!mediaDevices || typeof mediaDevices.getUserMedia !== 'function') {
                throw new Error('الكاميرا غير متاحة في هذا المتصفح أو الاتصال. استخدم قارئ باركود متصلًا أو حقل البحث اليدوي.');
            }
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
            let lastError = null;
            for (const constraints of [
                { audio: false, video: { facingMode: { ideal: 'environment' }, width: { ideal: 1920 }, height: { ideal: 1080 } } },
                { audio: false, video: true },
            ]) {
                try {
                    controls = await reader.decodeFromConstraints(constraints, video, onDecode);
                    lastError = null;
                    break;
                } catch (error) {
                    lastError = error;
                    if (!active) return;
                }
            }
            if (lastError) throw lastError;
            if (!active) { controls?.stop(); return; }
            onReady();
        } catch (error) {
            if (!active) return;
            stop();
            onError(error instanceof Error ? error : new Error('تعذر تشغيل قارئ الباركود. استخدم البحث اليدوي.'));
        }
    })();
    return { stop };
};
document.addEventListener('DOMContentLoaded', () => {
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
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
