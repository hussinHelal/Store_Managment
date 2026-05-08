import * as bootstrap from 'bootstrap';

// Make Bootstrap globally available
window.bootstrap = bootstrap;

// Import jQuery
import jQuery from 'jquery';
window.$ = window.jQuery = jQuery;

document.addEventListener('DOMContentLoaded', () => {
    // Enable tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltips = [...tooltipTriggerList].map(tooltipTriggerEl => 
        new bootstrap.Tooltip(tooltipTriggerEl)
    );
});