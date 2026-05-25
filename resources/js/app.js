import * as bootstrap from 'bootstrap';
import Chart from 'chart.js/auto';

import jQuery from 'jquery';
window.$ = window.jQuery = jQuery;
window.Chart = Chart;

document.addEventListener('DOMContentLoaded', () => {
    // Enable tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltips = [...tooltipTriggerList].map(tooltipTriggerEl =>
        new bootstrap.Tooltip(tooltipTriggerEl)
    );
});

$(function () {

    const themeToggle = $('#theme-toggle');
    const themeIcon = $('#theme-icon');
    const themeLabel = $('.theme-label');

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

    let currentTheme = localStorage.getItem('theme') || document.documentElement.getAttribute('data-bs-theme') || 'light';

    setTheme(currentTheme);

    if (themeToggle.length) {
        themeToggle.on('click', function () {
            currentTheme = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
            setTheme(currentTheme);
        });
    }

});
