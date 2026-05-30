/**
 * admin.js
 * Toggle utilizadores, toggle aulas, expandir inscritos, filtro de membros.
 */

/* ── Filtro de membros (client-side) ──────────────────────── */
function filterMembers() {
    var search  = (document.getElementById('member-search')?.value || '').toLowerCase();
    var role    = document.getElementById('member-role')?.value   || '';
    var status  = document.getElementById('member-status')?.value || '';
    var rows    = document.querySelectorAll('#members-table tbody tr');
    var visible = 0;

    rows.forEach(function (row) {
        var name     = row.dataset.name     || '';
        var email    = row.dataset.email    || '';
        var username = row.dataset.username || '';
        var rowRole  = row.dataset.role     || '';
        var rowStat  = row.dataset.status   || '';

        var matchSearch = !search || name.includes(search) || email.includes(search) || username.includes(search);
        var matchRole   = !role   || rowRole === role;
        var matchStatus = !status || rowStat === status;

        var show = matchSearch && matchRole && matchStatus;
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });

    var countEl = document.getElementById('member-count');
    if (countEl) countEl.textContent = visible + ' utilizador' + (visible !== 1 ? 'es' : '');
}

/* ── Toggle utilizador ativo/inativo ──────────────────────── */
function toggleUser(userId, btn) {
    fetch('../actions/action_admin_toggle_user.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'user_id=' + userId + '&ajax=1'
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        if (!data.ok) return;
        var pill = document.getElementById('status-' + userId);
        var row  = btn.closest('tr');
        if (data.isActive) {
            if (pill) { pill.textContent = 'Ativo'; pill.className = 'status-pill status-pill--active'; }
            if (row)  row.dataset.status = 'active';
            btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 7 7 17M7 7l10 10"/></svg>';
            btn.title = 'Desativar';
        } else {
            if (pill) { pill.textContent = 'Inativo'; pill.className = 'status-pill status-pill--inactive'; }
            if (row)  row.dataset.status = 'inactive';
            btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/></svg>';
            btn.title = 'Ativar';
        }
        filterMembers();
    })
    .catch(function () { location.reload(); });
}

/* ── Toggle aula ativa/inativa ───────────────────────────── */
function toggleClass(classId, btn) {
    fetch('../actions/action_admin_toggle_class.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'class_id=' + classId + '&ajax=1'
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        if (!data.ok) return;
        var pill = document.getElementById('class-status-' + classId);
        if (data.isActive) {
            if (pill) { pill.textContent = 'Ativa'; pill.className = 'status-pill status-pill--active'; }
            btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 7 7 17M7 7l10 10"/></svg>';
            btn.title = 'Desativar';
        } else {
            if (pill) { pill.textContent = 'Inativa'; pill.className = 'status-pill status-pill--inactive'; }
            btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/></svg>';
            btn.title = 'Ativar';
        }
    })
    .catch(function () { location.reload(); });
}

/* ── Expandir/colapsar inscritos ─────────────────────────── */
function toggleEnrollments(classId) {
    var row = document.getElementById('enrollments-' + classId);
    var btn = document.getElementById('toggle-btn-' + classId);
    if (!row) return;
    var open = row.style.display !== 'none';
    row.style.display = open ? 'none' : '';
    if (btn) btn.style.transform = open ? '' : 'rotate(180deg)';
}

/* ── Atualiza contador e barra na tabela ─────────────────── */
function updateEnrollCount(classId, delta) {
    var countEl = document.getElementById('enroll-count-' + classId);
    if (!countEl) return;
    var parts = countEl.textContent.trim().split('/');
    if (parts.length !== 2) return;
    var newCount = Math.max(0, parseInt(parts[0]) + delta);
    var capacity = parseInt(parts[1]);
    countEl.textContent = newCount + '/' + capacity;
    var bar = countEl.previousElementSibling;
    if (bar) {
        var fill = bar.querySelector('div');
        if (fill) fill.style.width = (capacity > 0 ? Math.round(newCount / capacity * 100) : 0) + '%';
    }
}

/* ── Remover inscrição ───────────────────────────────────── */
function removeEnrollment(enrollmentId, classId, btn) {
    if (!confirm('Remover inscrição?')) return;

    fetch('../actions/action_admin_remove_enrollment.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'enrollment_id=' + enrollmentId + '&ajax=1'
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        if (!data.ok) return;

        var chip = btn.closest('div[style*="display:flex"]');
        if (chip) {
            // Adicionar de volta ao select
            var select = document.getElementById('add-member-' + classId);
            if (select) {
                var name = chip.querySelector('span') ? chip.querySelector('span').textContent : '';
                var userId = data.userId;
                if (userId) {
                    var opt = document.createElement('option');
                    opt.value = userId;
                    opt.textContent = name;
                    select.appendChild(opt);
                }
            }
            chip.remove();
        }

        var chips = document.getElementById('chips-' + classId);
        if (chips) {
            var remaining = chips.children.length;
            var label = chips.previousElementSibling;
            if (label && label.tagName === 'P') label.textContent = remaining + ' inscritos';
            if (remaining === 0 && label) label.textContent = 'Nenhum inscrito nesta aula.';
        }

        updateEnrollCount(classId, -1);
    })
    .catch(function () { location.reload(); });
}

/* ── Adicionar inscrição ─────────────────────────────────── */
function addEnrollment(classId, btn) {
    var select = document.getElementById('add-member-' + classId);
    if (!select || !select.value) return;

    var userId   = select.value;
    var userName = select.options[select.selectedIndex].text;

    fetch('../actions/action_admin_add_enrollment.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'class_id=' + classId + '&user_id=' + userId + '&ajax=1'
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        if (!data.ok) { alert(data.error || 'Erro ao inscrever.'); return; }

        // Remove do select
        select.remove(select.selectedIndex);
        select.value = '';

        // Adiciona chip
        var chips = document.getElementById('chips-' + classId);
        if (!chips) {
            // Criar container de chips se não existir
            var container = btn.closest('div[style*="padding:var(--space-lg)"]');
            if (container) {
                chips = document.createElement('div');
                chips.id = 'chips-' + classId;
                chips.style.cssText = 'display:flex;flex-wrap:wrap;gap:.5rem;margin-bottom:.75rem';
                container.insertBefore(chips, container.querySelector('div[style*="display:flex;gap:.5rem"]'));
            }
        }

        if (chips) {
            var ini = userName.split(' ').map(function(p){return p[0]||'';}).join('').substring(0,2).toUpperCase();
            var chip = document.createElement('div');
            chip.style.cssText = 'display:flex;align-items:center;gap:.5rem;padding:.5rem .75rem;background:var(--color-surface);border:1px solid var(--color-border);border-radius:var(--radius-md);font-size:.85rem';
            chip.innerHTML = '<div class="admin-table__user-avatar" style="width:28px;height:28px;font-size:.7rem">' + ini + '</div>'
                + '<span>' + userName + '</span>'
                + '<button onclick="removeEnrollment(' + data.enrollmentId + ',' + classId + ',this)" data-uid="' + userId + '" style="background:none;border:none;cursor:pointer;color:var(--color-text-muted);padding:0;line-height:1" title="Remover inscrição">'
                + '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M18 6L6 18M6 6l12 12"/></svg></button>';
            chips.appendChild(chip);

            var label = chips.previousElementSibling;
            if (label && label.tagName === 'P') label.textContent = chips.children.length + ' inscritos';
        }

        updateEnrollCount(classId, 1);
    })
    .catch(function () { location.reload(); });
}

/* ── Fechar mensagens flash ──────────────────────────────── */
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-close-message]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            btn.closest('.message')?.remove();
        });
    });
});