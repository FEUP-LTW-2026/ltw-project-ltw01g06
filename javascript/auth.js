/**
 * auth.js
 * Lógica de autenticação: tab switching e validação de password.
 */

document.addEventListener('DOMContentLoaded', function () {

    /* ── Tab switching (login ↔ registar) ── */
    document.querySelectorAll('[data-tab]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var tab = btn.getAttribute('data-tab');

            document.querySelectorAll('.auth-tabs__tab').forEach(function (t) {
                t.classList.remove('auth-tabs__tab--active');
                t.setAttribute('aria-selected', 'false');
            });

            document.querySelectorAll('.auth-card').forEach(function (c) {
                c.classList.add('auth-card--hidden');
            });

            btn.classList.add('auth-tabs__tab--active');
            btn.setAttribute('aria-selected', 'true');

            var panel = document.getElementById('panel-' + tab);
            if (panel) panel.classList.remove('auth-card--hidden');
        });
    });

    /* ── Confirmação de password no registo ── */
    var registerForm = document.querySelector('#panel-register form');
    if (registerForm) {
        registerForm.addEventListener('submit', function (e) {
            var pw = registerForm.querySelector('[name="password"]').value;
            var cf = registerForm.querySelector('[name="confirm_password"]').value;
            if (pw !== cf) {
                e.preventDefault();
                alert('As palavras-passe não coincidem.');
            }
        });
    }

});
