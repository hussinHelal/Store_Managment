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
    let fallbackButton = null;
    let fallbackInput = null;
    let imageUrl = null;
    let reader = null;
    const enablePhotoFallback = () => {
        if (!active || fallbackInput) return;
        video.dataset.scanMode = 'photo';
        video.classList.add('d-none');
        fallbackButton = document.createElement('button');
        fallbackButton.type = 'button';
        fallbackButton.className = 'btn btn-outline-primary w-100 mt-2';
        fallbackButton.textContent = 'التقاط صورة للباركود بالكاميرا';
        fallbackInput = document.createElement('input');
        fallbackInput.type = 'file';
        fallbackInput.accept = 'image/*';
        fallbackInput.setAttribute('capture', 'environment');
        fallbackInput.className = 'visually-hidden';
        fallbackInput.addEventListener('change', async () => {
            const file = fallbackInput?.files?.[0];
            if (!file || !active) return;
            if (imageUrl) URL.revokeObjectURL(imageUrl);
            imageUrl = URL.createObjectURL(file);
            try {
                const result = await reader.decodeFromImageUrl(imageUrl);
                if (!active) return;
                const barcode = result.getText().trim();
                if (barcode) { stop(); onScan(barcode); }
            } catch {
                if (active) onError(new Error('تعذرت قراءة الباركود من الصورة. أعد التقاط صورة أو استخدم البحث اليدوي.'));
            }
        });
        fallbackButton.addEventListener('click', () => fallbackInput?.click());
        video.insertAdjacentElement('afterend', fallbackButton);
        fallbackButton.insertAdjacentElement('afterend', fallbackInput);
        onReady();
    };
    const stop = () => {
        active = false;
        controls?.stop();
        controls = null;
        fallbackButton?.remove();
        fallbackInput?.remove();
        fallbackButton = null;
        fallbackInput = null;
        if (imageUrl) URL.revokeObjectURL(imageUrl);
        imageUrl = null;
        video.srcObject?.getTracks().forEach(track => track.stop());
        video.pause();
        video.srcObject = null;
        video.classList.remove('d-none');
        delete video.dataset.scanMode;
    };
    (async () => {
        try {
            const mediaDevices = navigator.mediaDevices;
            const [{ BrowserMultiFormatReader, BarcodeFormat }, { DecodeHintType }] = await Promise.all([
                import('@zxing/browser'), import('@zxing/library'),
            ]);
            if (!active) return;
            const hints = new Map([
                [DecodeHintType.POSSIBLE_FORMATS, [BarcodeFormat.EAN_13, BarcodeFormat.EAN_8, BarcodeFormat.UPC_A, BarcodeFormat.UPC_E, BarcodeFormat.CODE_128, BarcodeFormat.CODE_39, BarcodeFormat.CODE_93, BarcodeFormat.ITF, BarcodeFormat.CODABAR, BarcodeFormat.QR_CODE]],
                [DecodeHintType.TRY_HARDER, true],
            ]);
            reader = new BrowserMultiFormatReader(hints);
            if (!mediaDevices || typeof mediaDevices.getUserMedia !== 'function') {
                enablePhotoFallback();
                return;
            }
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
            video.dataset.scanMode = 'live';
            onReady();
        } catch (error) {
            if (!active) return;
            stop();
            if (reader) {
                active = true;
                enablePhotoFallback();
                return;
            }
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
