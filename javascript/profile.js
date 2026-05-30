/**
 * profile.js
 * Preview de foto e validação de password.
 */

document.addEventListener('DOMContentLoaded', function () {

    var photoInput    = document.getElementById('photo-input');
    var photoFile     = document.getElementById('photo-file');
    var avatarPreview = document.getElementById('avatar-preview');

    if (photoInput && photoFile && avatarPreview) {
        photoInput.addEventListener('change', function () {
            var file = photoInput.files[0];
            if (!file) return;

            if (file.size > 2 * 1024 * 1024) {
                alert('A imagem não pode ter mais de 2MB.');
                photoInput.value = '';
                return;
            }

            // Copiar o ficheiro para o input dentro do form principal
            var dt = new DataTransfer();
            dt.items.add(file);
            photoFile.files = dt.files;

            // Mostrar preview
            var reader = new FileReader();
            reader.onload = function (e) {
                avatarPreview.innerHTML =
                    '<img src="' + e.target.result
                    + '" alt="Preview" style="width:100%;height:100%;object-fit:cover;border-radius:inherit;" />';
            };
            reader.readAsDataURL(file);
        });
    }

    /* ── Validação visual de password ── */
    var newPw = document.getElementById('new-password');
    if (!newPw) return;

    newPw.addEventListener('input', function () {
        var v = newPw.value;
        setRequirement('req-length', v.length >= 8);
        setRequirement('req-upper',  /[A-Z]/.test(v));
        setRequirement('req-number', /[0-9]/.test(v));
    });

    function setRequirement(id, ok) {
        var el = document.getElementById(id);
        if (!el) return;
        var svg = el.querySelector('svg');
        if (ok) {
            svg.innerHTML = '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>'
                          + '<path d="M22 4L12 14.01l-3-3"/>';
            el.style.color = '#16a34a';
        } else {
            svg.innerHTML = '<circle cx="12" cy="12" r="10"/>';
            el.style.color = '';
        }
    }

});