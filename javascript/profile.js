/**
 * profile.js
 * Validação visual dos requisitos de palavra-passe na página de perfil.
 */

document.addEventListener('DOMContentLoaded', function () {

    var newPw = document.getElementById('new-password');
    if (!newPw) return;

    newPw.addEventListener('input', function () {
        var v = newPw.value;
        setRequirement('req-length', v.length >= 8);
        setRequirement('req-upper',  /[A-Z]/.test(v));
        setRequirement('req-number', /[0-9]/.test(v));
    });

    function setRequirement(id, ok) {
        var el  = document.getElementById(id);
        if (!el) return;
        var svg = el.querySelector('svg');

        if (ok) {
            svg.innerHTML = '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>'
                          + '<path d="M22 4L12 14.01l-3-3"/>';
            el.style.color = 'var(--color-success, #16a34a)';
        } else {
            svg.innerHTML = '<circle cx="12" cy="12" r="10"/>';
            el.style.color = '';
        }
    }

});
