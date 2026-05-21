import 'bootstrap/dist/js/bootstrap.bundle.min.js';

import jQuery from 'jquery';
window.$ = window.jQuery = jQuery;

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
            themeLabel.text('Light Mode');
            themeToggle.attr('aria-label', 'Switch to light mode');
        } else {
            themeIcon.removeClass('fa-moon').addClass('fa-sun');
            themeLabel.text('Dark Mode');
            themeToggle.attr('aria-label', 'Switch to dark mode');
        }
    }

    let currentTheme = localStorage.getItem('theme') || document.documentElement.getAttribute('data-bs-theme') || 'light';

    setTheme(currentTheme);

    themeToggle.on('click', function () {
        currentTheme = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
        setTheme(currentTheme);
    });

});