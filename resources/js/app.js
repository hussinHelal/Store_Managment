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

        const stream = video.srcObject;
        stream?.getTracks().forEach((track) => track.stop());
        video.pause();
        video.srcObject = null;
    };

    (async () => {
        try {
            const [{ BrowserMultiFormatReader, BarcodeFormat }, { DecodeHintType }] = await Promise.all([
                import('@zxing/browser'),
                import('@zxing/library'),
            ]);
            if (!active) return;

            const cameras = await BrowserMultiFormatReader.listVideoInputDevices();
            const preferredCamera = cameras.find((camera) => /rear|back|environment/i.test(camera.label)) ?? cameras[0];
            if (!preferredCamera) throw new Error('لم يتم العثور على كاميرا متاحة.');

            const hints = new Map([
                [DecodeHintType.POSSIBLE_FORMATS, [
                    BarcodeFormat.EAN_13,
                    BarcodeFormat.EAN_8,
                    BarcodeFormat.UPC_A,
                    BarcodeFormat.UPC_E,
                    BarcodeFormat.CODE_128,
                    BarcodeFormat.CODE_39,
                    BarcodeFormat.CODE_93,
                    BarcodeFormat.ITF,
                    BarcodeFormat.CODABAR,
                    BarcodeFormat.QR_CODE,
                ]],
                [DecodeHintType.TRY_HARDER, true],
            ]);
            const reader = new BrowserMultiFormatReader(hints);
            controls = await reader.decodeFromConstraints({
                audio: false,
                video: {
                    deviceId: { exact: preferredCamera.deviceId },
                    width: { ideal: 1920 },
                    height: { ideal: 1080 },
                },
            }, video, (result, error) => {
                if (!active) return;
                if (result) {
                    const barcode = result.getText().trim();
                    if (!barcode) return;

                    stop();
                    onScan(barcode);
                    return;
                }

                const decodeMiss = error && (
                    ['NotFoundException', 'ChecksumException', 'FormatException'].includes(error.name)
                    || /No MultiFormat Readers were able to detect/i.test(error.message || '')
                );
                if (decodeMiss) return;

                if (error && !['NotFoundException', 'ChecksumException', 'FormatException'].includes(error.name)) {
                    stop();
                    onError(error);
                }
            });

            if (!active) {
                controls.stop();
                return;
            }
            onReady();
        } catch (error) {
            if (!active) return;
            stop();
            onError(error);
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
