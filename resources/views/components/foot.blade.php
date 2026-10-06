{{-- footer start --}}
<footer>
    <div class="container-fluid">
        <div class="row">
            <div class="col">
                <p> created By: Hussien Helal | &copy; {{ date('Y') }} {{ config('app.name') }}</p>
            </div>
        </div>
    </div>
</footer>
{{-- footer end --}}

<script>
    // Helper to show Bootstrap alert in the dynamic alerts container
    window.showBootstrapAlert = function(message, type = 'danger', timeout = 8000) {
        try {
            const container = document.getElementById('dynamic-alerts');
            if (!container) {
                alert(message);
                return;
            }
            const wrapper = document.createElement('div');
            const alertElement = document.createElement('div');
            const alertType = ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark'].includes(type)
                ? type
                : 'danger';
            alertElement.className = `alert alert-${alertType} alert-dismissible fade show rounded-3 shadow-sm`;
            alertElement.setAttribute('role', 'alert');
            alertElement.appendChild(document.createTextNode(String(message)));

            const closeButton = document.createElement('button');
            closeButton.type = 'button';
            closeButton.className = 'btn-close';
            closeButton.setAttribute('data-bs-dismiss', 'alert');
            closeButton.setAttribute('aria-label', 'Close');
            alertElement.appendChild(closeButton);
            wrapper.appendChild(alertElement);
            container.appendChild(wrapper);
            if (timeout > 0) {
                setTimeout(() => {
                    const el = wrapper.querySelector('.alert');
                    if (window.bootstrap && window.bootstrap.Alert) {
                        const bsAlert = window.bootstrap.Alert.getOrCreateInstance(el);
                        bsAlert.close();
                    } else {
                        el.classList.remove('show');
                        el.remove();
                    }
                }, timeout);
            }
        } catch (e) {
            console.error('showBootstrapAlert error', e);
            alert(message);
        }
    };
</script>
