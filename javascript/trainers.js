document.addEventListener('DOMContentLoaded', () => {
  const filters = document.querySelectorAll('.trainers-filter');
  const cards   = document.querySelectorAll('.trainer-card');
  const grid    = document.querySelector('.trainers-grid');

  // Mensagem "sem resultados"
  let emptyMsg = grid.querySelector('.trainers-empty');
  if (!emptyMsg) {
    emptyMsg = document.createElement('p');
    emptyMsg.className = 'trainers-empty';
    emptyMsg.textContent = 'Nenhum treinador encontrado para esta categoria.';
    emptyMsg.style.display = 'none';
    grid.appendChild(emptyMsg);
  }

  filters.forEach(btn => {
    btn.addEventListener('click', () => {
      // Atualiza botão ativo
      filters.forEach(b => b.classList.remove('trainers-filter--active'));
      btn.classList.add('trainers-filter--active');

      const selected = btn.dataset.spec.toLowerCase();
      let visible = 0;

      cards.forEach(card => {
        const specs = card.dataset.specs || '';
        const show  = selected === '' || specs.split(',').map(s => s.trim()).includes(selected);
        card.classList.toggle('trainer-card--hidden', !show);
        if (show) visible++;
      });

      emptyMsg.style.display = visible === 0 ? 'block' : 'none';
    });
  });
});