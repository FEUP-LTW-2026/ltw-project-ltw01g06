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

  if (window.innerWidth < 1200) switchView('list');

  /* ── Filtros ── */
  var filterType    = document.getElementById('filter-type');
  var filterTrainer = document.getElementById('filter-trainer');
  var filterChips   = document.querySelectorAll('.filter-chip');

  function applyFilters() {
    var type    = filterType    ? filterType.value.trim()    : '';
    var trainer = filterTrainer ? filterTrainer.value.trim() : '';
    var activeChip = document.querySelector('.filter-chip--active');
    var level   = activeChip ? (activeChip.dataset.level || '') : '';

    /* ── Vista Lista ── */
    document.querySelectorAll('.schedule-list__day').forEach(function (dayBlock) {
      var items = dayBlock.querySelectorAll('.class-list-item');
      var visibleInDay = 0;

      items.forEach(function (el) {
        var matchType    = !type    || el.dataset.type    === type;
        var matchTrainer = !trainer || el.dataset.trainer === trainer;
        var matchLevel   = !level   || el.dataset.level   === level;
        var show = matchType && matchTrainer && matchLevel;
        el.style.display = show ? '' : 'none';
        if (show) visibleInDay++;
      });

      /* Esconde o bloco do dia inteiro se não tiver nenhuma aula visível */
      dayBlock.style.display = visibleInDay > 0 ? '' : 'none';
    });

    /* ── Vista Calendário ── */
    /* Primeiro mostra todos os cards e linhas */
    document.querySelectorAll('.class-card').forEach(function (el) {
      var matchType    = !type    || el.dataset.type    === type;
      var matchTrainer = !trainer || el.dataset.trainer === trainer;
      var matchLevel   = !level   || el.dataset.level   === level;
      el.style.display = (matchType && matchTrainer && matchLevel) ? '' : 'none';
    });

    /* Esconde linhas de hora onde todas as células estão vazias */
    document.querySelectorAll('.calendar-grid__row').forEach(function (row) {
      var cells = row.querySelectorAll('.calendar-grid__cell');
      var hasVisible = false;
      cells.forEach(function (cell) {
        var visibleCards = Array.from(cell.querySelectorAll('.class-card'))
          .some(function (c) { return c.style.display !== 'none'; });
        if (visibleCards) hasVisible = true;
      });
      /* Esconde a linha de hora e as células mas não o header de hora */
      row.style.display = hasVisible ? '' : 'none';
    });

    /* Mensagem de sem resultados na lista */
    var listEmpty = document.getElementById('schedule-list-empty');
    if (listEmpty) {
      var anyVisible = Array.from(
        document.querySelectorAll('.schedule-list__day')
      ).some(function (d) { return d.style.display !== 'none'; });
      listEmpty.style.display = anyVisible ? 'none' : '';
    }
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

  /* ── Filtro de treinador via URL (?trainer=ID) ── */
  var params    = new URLSearchParams(window.location.search);
  var typeParam = params.get('type');
if (typeParam && filterType) {
    filterType.value = typeParam;
    switchView('list');
    applyFilters();
}

});