/**
 * class-detail.js
 * Interatividade das estrelas de avaliação.
 */

function initStarRating() {
    var container = document.querySelector('.star-rating');
    if (!container) return;

    var starBtns = container.querySelectorAll('.star-rating__btn');
    var selected  = -1;

    /* Highlight ao passar o rato */
    starBtns.forEach(function (btn, index) {
        btn.addEventListener('mouseenter', function () {
            starBtns.forEach(function (b, i) {
                b.classList.toggle('star-rating__btn--active', i <= index);
            });
        });

        btn.addEventListener('click', function () {
            var input = btn.querySelector('input[type="radio"]');
            if (input) {
                input.checked = true;
                selected = index;
            }
        });
    });

    /* Ao sair do container, volta ao estado selecionado */
    container.addEventListener('mouseleave', function () {
        starBtns.forEach(function (b, i) {
            b.classList.toggle('star-rating__btn--active', i <= selected);
        });
    });

    /* Marcar estrelas já selecionadas ao carregar */
    var checkedInput = container.querySelector('input[type="radio"]:checked');
    if (checkedInput) {
        selected = parseInt(checkedInput.value) - 1;
        starBtns.forEach(function (b, i) {
            b.classList.toggle('star-rating__btn--active', i <= selected);
        });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initStarRating);
} else {
    initStarRating();
}