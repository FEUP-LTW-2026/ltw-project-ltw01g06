/**
 * auth.js
 * Tab switching com slider animado e validação de password.
 */

document.addEventListener('DOMContentLoaded', function () {

    var nav = document.querySelector('.auth-tabs');

    function switchTab(tab) {
        document.querySelectorAll('.auth-tabs__tab').forEach(function (t) {
            t.classList.remove('auth-tabs__tab--active');
            t.setAttribute('aria-selected', 'false');
        });
        document.querySelectorAll('.auth-card').forEach(function (c) {
            c.classList.add('auth-card--hidden');
        });

        var btn = document.querySelector('[data-tab="' + tab + '"]');
        if (btn) {
            btn.classList.add('auth-tabs__tab--active');
            btn.setAttribute('aria-selected', 'true');
        }

        if (nav) nav.setAttribute('data-active', tab);

        var panel = document.getElementById('panel-' + tab);
        if (panel) panel.classList.remove('auth-card--hidden');
    }

    /* ── Tab switching ao clicar ── */
    document.querySelectorAll('[data-tab]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            switchTab(btn.getAttribute('data-tab'));
        });
    });

    /* ── Abrir tab de registo se URL tiver #register ── */
    if (window.location.hash === '#register') {
        switchTab('register');
        history.replaceState(null, '', window.location.pathname);
    }

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