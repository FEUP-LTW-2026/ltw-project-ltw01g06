/**
 * schedule.js
 * Toggle semana/lista e filtros na página de horário.
 */

document.addEventListener('DOMContentLoaded', function () {

    var calendarView = document.getElementById('view-calendar');
    var listView     = document.getElementById('view-list');
    var btnWeek      = document.getElementById('btn-week');
    var btnList      = document.getElementById('btn-list');

    /* ── Toggle de vista ── */
    function switchView(view) {
        var isCalendar = view === 'calendar';
        if (calendarView) calendarView.style.display = isCalendar ? '' : 'none';
        if (listView)     listView.style.display     = isCalendar ? 'none' : '';
        if (btnWeek) btnWeek.classList.toggle('calendar-view-btn--active', isCalendar);
        if (btnList) btnList.classList.toggle('calendar-view-btn--active', !isCalendar);
    }

    if (btnWeek) btnWeek.addEventListener('click', function () { switchView('calendar'); });
    if (btnList) btnList.addEventListener('click', function () { switchView('list'); });

    /* Em mobile, mostrar lista por defeito */
    if (window.innerWidth < 1200) switchView('list');

    /* ── Filtros ── */
    var filterType    = document.getElementById('filter-type');
    var filterTrainer = document.getElementById('filter-trainer');
    var filterChips   = document.querySelectorAll('.filter-chip');

    function applyFilters() {
        var type    = filterType    ? filterType.value    : '';
        var trainer = filterTrainer ? filterTrainer.value : '';
        var activeChip = document.querySelector('.filter-chip--active');
        var level   = activeChip ? (activeChip.dataset.level || '') : '';

        document.querySelectorAll('.class-list-item, .class-card').forEach(function (el) {
            var matchType    = !type    || el.dataset.type    === type;
            var matchTrainer = !trainer || el.dataset.trainer === trainer;
            var matchLevel   = !level   || el.dataset.level   === level;
            el.style.display = (matchType && matchTrainer && matchLevel) ? '' : 'none';
        });
    }

    if (filterType)    filterType.addEventListener('change', applyFilters);
    if (filterTrainer) filterTrainer.addEventListener('change', applyFilters);

    filterChips.forEach(function (chip) {
        chip.addEventListener('click', function () {
            filterChips.forEach(function (c) { c.classList.remove('filter-chip--active'); });
            chip.classList.add('filter-chip--active');
            applyFilters();
        });
    });

});
