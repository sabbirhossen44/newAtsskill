/* Admin JS: sidebar theme toggle, delete-confirm modal wiring, status toggles. No secrets here. */
document.addEventListener('DOMContentLoaded', function () {
    var themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', function () {
            var html = document.documentElement;
            var next = html.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-bs-theme', next);
            try { localStorage.setItem('admin-theme', next); } catch (e) {}
        });
        try {
            var saved = localStorage.getItem('admin-theme');
            if (saved) document.documentElement.setAttribute('data-bs-theme', saved);
        } catch (e) {}
    }
    // Delete modal: fill the shared #deleteForm action from the clicked button.
    var deleteModal = document.getElementById('deleteModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var url = button ? button.getAttribute('data-delete-url') : '';
            var form = document.getElementById('deleteForm');
            if (form && url) form.setAttribute('action', url);
        });
    }
    // Status selects that submit on change are plain forms; confirm before destructive change.
    document.querySelectorAll('.status-form select').forEach(function (sel) {
        sel.addEventListener('change', function () { sel.closest('form').submit(); });
    });
});
