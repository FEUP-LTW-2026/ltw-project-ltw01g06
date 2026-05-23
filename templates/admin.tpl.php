<?php
declare(strict_types=1);

function drawAdminSidebar(string $activePage): void {
    $links = [
        ['page' => 'dashboard',       'href' => 'admin.php',           'label' => 'Dashboard',   'icon' => '<rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/>'],
        ['page' => 'analytics',       'href' => 'admin-analytics.php', 'label' => 'Analytics',   'icon' => '<path d="M3 3v18h18"/><path d="M18.7 8l-5.1 5.2-2.8-2.7L7 14.3"/>'],
    ];
    $mgmtLinks = [
        ['page' => 'members',  'href' => 'admin-members.php',   'label' => 'Membros',      'icon' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>'],
        ['page' => 'trainers', 'href' => 'admin-trainers.php',  'label' => 'Treinadores',  'icon' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/>'],
        ['page' => 'classes',  'href' => 'admin-classes.php',   'label' => 'Aulas',        'icon' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>'],
        ['page' => 'equipment','href' => 'admin-equipment.php', 'label' => 'Equipamento',  'icon' => '<path d="M6 4v6M6 14v6M18 4v6M18 14v6M3 7h6M15 7h6M3 17h6M15 17h6"/>'],
    ];
    ?>
    <aside class="admin-sidebar">
        <div class="admin-sidebar__section">
            <h3 class="admin-sidebar__title">Geral</h3>
            <nav class="admin-sidebar__nav">
                <?php foreach ($links as $link): ?>
                    <a href="<?= $link['href'] ?>"
                       class="admin-nav-link <?= $activePage === $link['page'] ? 'admin-nav-link--active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <?= $link['icon'] ?>
                        </svg>
                        <?= htmlspecialchars($link['label']) ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>
        <div class="admin-sidebar__section">
            <h3 class="admin-sidebar__title">Gestão</h3>
            <nav class="admin-sidebar__nav">
                <?php foreach ($mgmtLinks as $link): ?>
                    <a href="<?= $link['href'] ?>"
                       class="admin-nav-link <?= $activePage === $link['page'] ? 'admin-nav-link--active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <?= $link['icon'] ?>
                        </svg>
                        <?= htmlspecialchars($link['label']) ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>
        <div class="admin-sidebar__section">
            <h3 class="admin-sidebar__title">Sistema</h3>
            <nav class="admin-sidebar__nav">
                <a href="admin-settings.php" class="admin-nav-link <?= $activePage === 'settings' ? 'admin-nav-link--active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="3"/>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                    </svg>
                    Definições
                </a>
            </nav>
        </div>
    </aside>
    <?php
}

function drawAdminStats(array $stats): void {
    $cards = [
        [
            'key'   => 'members',
            'label' => 'Membros Ativos',
            'icon'  => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
            'mod'   => 'members',
        ],
        [
            'key'   => 'trainers',
            'label' => 'Treinadores',
            'icon'  => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/>',
            'mod'   => 'trainers',
        ],
        [
            'key'   => 'classes',
            'label' => 'Aulas Ativas',
            'icon'  => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
            'mod'   => 'classes',
        ],
    ];
    ?>
    <section class="admin-stats">
        <?php foreach ($cards as $card): ?>
            <div class="admin-stat-card">
                <div class="admin-stat-card__header">
                    <div class="admin-stat-card__icon admin-stat-card__icon--<?= $card['mod'] ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <?= $card['icon'] ?>
                        </svg>
                    </div>
                </div>
                <span class="admin-stat-card__value"><?= $stats[$card['key']] ?></span>
                <span class="admin-stat-card__label"><?= $card['label'] ?></span>
            </div>
        <?php endforeach; ?>
    </section>
    <?php
}

function drawMembersTable(array $members, int $currentPage, int $totalPages, int $total, int $perPage): void {
    $start = (($currentPage - 1) * $perPage) + 1;
    $end   = min($currentPage * $perPage, $total);
    ?>
    <section class="admin-table-card">
        <header class="admin-table-card__header">
            <h2 class="admin-table-card__title">Membros Recentes</h2>
            <div class="admin-table-card__actions">
                <a href="admin-members.php" class="btn btn--primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                    Ver Todos
                </a>
            </div>
        </header>

        <?php if (empty($members)): ?>
            <p class="dashboard-empty">Nenhum membro encontrado.</p>
        <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Membro</th>
                    <th>Função</th>
                    <th>Data Registo</th>
                    <th>Estado</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($members as $member): ?>
                    <?php drawMemberRow($member); ?>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="admin-pagination">
            <span class="admin-pagination__info">
                A mostrar <?= $start ?>-<?= $end ?> de <?= $total ?> membros
            </span>
            <div class="admin-pagination__controls">
                <?php if ($currentPage > 1): ?>
                    <a href="?page=<?= $currentPage - 1 ?>" class="pagination-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M15 18l-6-6 6-6"/>
                        </svg>
                    </a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i === 1 || $i === $totalPages || abs($i - $currentPage) <= 1): ?>
                        <a href="?page=<?= $i ?>"
                           class="pagination-btn <?= $i === $currentPage ? 'pagination-btn--active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php elseif (abs($i - $currentPage) === 2): ?>
                        <span class="pagination-btn">...</span>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="?page=<?= $currentPage + 1 ?>" class="pagination-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 18l6-6-6-6"/>
                        </svg>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </section>
    <?php
}

function drawMemberRow(User $member): void {
    $initials   = getAdminInitials($member->fullName);
    $statusClass = $member->isActive ? 'active' : 'inactive';
    $statusLabel = $member->isActive ? 'Ativo' : 'Inativo';
    $roleLabel   = match($member->role) {
        'admin'   => 'Admin',
        'trainer' => 'Treinador',
        default   => 'Membro',
    };
    ?>
    <tr>
        <td>
            <div class="admin-table__user">
                <div class="admin-table__user-avatar"><?= htmlspecialchars($initials) ?></div>
                <div class="admin-table__user-info">
                    <span class="admin-table__user-name"><?= htmlspecialchars($member->fullName) ?></span>
                    <span class="admin-table__user-email"><?= htmlspecialchars($member->email) ?></span>
                </div>
            </div>
        </td>
        <td><?= htmlspecialchars($roleLabel) ?></td>
        <td><?= htmlspecialchars(date('d M Y', strtotime($member->createdAt))) ?></td>
        <td><span class="status-pill status-pill--<?= $statusClass ?>"><?= $statusLabel ?></span></td>
        <td>
            <div class="admin-table__actions">
                <a href="admin-member-edit.php?id=<?= $member->id ?>" class="table-action-btn" title="Editar">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                </a>
                <form method="post" action="admin-delete-member.php" style="display:inline"
                      onsubmit="return confirm('Tens a certeza que queres eliminar este membro?')">
                    <input type="hidden" name="user_id" value="<?= $member->id ?>">
                    <button type="submit" class="table-action-btn table-action-btn--danger" title="Eliminar">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="3 6 5 6 21 6"/>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                        </svg>
                    </button>
                </form>
            </div>
        </td>
    </tr>
    <?php
}

function getAdminInitials(string $name): string {
    $parts = explode(' ', trim($name));
    $initials = '';
    foreach (array_slice($parts, 0, 2) as $part) {
        $initials .= strtoupper(mb_substr($part, 0, 1));
    }
    return $initials;
}
?>