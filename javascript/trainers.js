/**
 * trainers.js
 * Filtro por especialização na página de treinadores.
 */

document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.trainers-filter').forEach(function (btn) {
        btn.addEventListener('click', function () {

            document.querySelectorAll('.trainers-filter').forEach(function (b) {
                b.classList.remove('trainers-filter--active');
            });
            btn.classList.add('trainers-filter--active');

            var spec = btn.dataset.spec;

            document.querySelectorAll('.trainer-card').forEach(function (card) {
                var specs = card.dataset.specs ? card.dataset.specs.split(',') : [];
                card.style.display = (!spec || specs.includes(spec)) ? '' : 'none';
            });

        });
    });

});
