/**
 * equipment.js
 * Filtros de pesquisa, categoria e estado na página de equipamento.
 */

document.addEventListener('DOMContentLoaded', function () {

    var search   = document.getElementById('eq-search');
    var category = document.getElementById('eq-category');
    var status   = document.getElementById('eq-status');

    if (!search || !category || !status) return;

    function applyFilters() {
        var q   = search.value.toLowerCase();
        var cat = category.value;
        var st  = status.value;

        document.querySelectorAll('.equipment-card').forEach(function (card) {
            var matchName = !q   || card.dataset.name.includes(q);
            var matchCat  = !cat || card.dataset.category === cat;
            var matchSt   = !st  || card.dataset.status === st;
            card.style.display = (matchName && matchCat && matchSt) ? '' : 'none';
        });
    }

    search.addEventListener('input', applyFilters);
    category.addEventListener('change', applyFilters);
    status.addEventListener('change', applyFilters);

});
