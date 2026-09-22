<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1090;" id="toastContainer"></div>

<script>
    function showToast(message, type = 'success') {
        const container = document.getElementById('toastContainer');
        const bgClass = {
            'success': 'text-bg-success',
            'error': 'text-bg-danger',
            'info': 'text-bg-primary',
            'warning': 'text-bg-warning',
        } [type] || 'text-bg-secondary';

        const icon = {
            'success': 'fa-check-circle',
            'error': 'fa-exclamation-circle',
            'info': 'fa-info-circle',
            'warning': 'fa-exclamation-triangle',
        } [type] || 'fa-bell';

        const toastEl = document.createElement('div');
        toastEl.className = `toast align-items-center ${bgClass} border-0`;
        toastEl.setAttribute('role', 'alert');
        toastEl.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas ${icon} me-2"></i>${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        container.appendChild(toastEl);
        const bsToast = new bootstrap.Toast(toastEl, {
            delay: 3500
        });
        bsToast.show();
        toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
    }
</script>
