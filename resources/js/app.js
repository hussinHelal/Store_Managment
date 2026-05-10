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