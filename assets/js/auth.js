// Toggle password visibility for auth forms.
document.addEventListener('DOMContentLoaded', function () {
    // handle checkboxes with class 'show-password-toggle'
    document.querySelectorAll('input.show-password-toggle[type="checkbox"]').forEach(function (cb) {
        var form = cb.closest('form');
        if (!form) return;

        // target inputs with a dedicated class so toggling back works
        var pwdInputs = form.querySelectorAll('input.auth-password');

        // initialize toggle state on load
        cb.addEventListener('change', function () {
            pwdInputs.forEach(function (pwd) {
                pwd.type = cb.checked ? 'text' : 'password';
            });
        });
    });
});
