{{-- footer start --}}
<footer>
    <div class="container-fluid">
        <div class="row">
            <div class="col">
                <p>&copy; {{ date('Y') }} | {{ config('app.name') }}</p>
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
            wrapper.innerHTML = `
                <div class="alert alert-${type} alert-dismissible fade show rounded-3 shadow-sm" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
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
