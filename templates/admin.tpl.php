<?php
declare(strict_types=1);

/**
 * admin.tpl.php
 * drawAdminSidebar() — sidebar do painel de admin, reutilizada em todas as páginas admin.
 *
 * @param string $activePage  'dashboard' | 'analytics' | 'members' | 'classes' | 'equipment'
 * @param array  $counts      ['members' => int, 'trainers' => int, 'classes' => int, 'equipment' => int]
 */
function drawAdminSidebar(string $activePage, array $counts = []): void {
    $members   = $counts['members']   ?? '';
    $trainers  = $counts['trainers']  ?? '';
    $classes   = $counts['classes']   ?? '';
    $equipment = $counts['equipment'] ?? '';
    ?>
    <aside class="admin-sidebar">

        <div class="admin-sidebar__section">
            <h3 class="admin-sidebar__title">Geral</h3>
            <nav class="admin-sidebar__nav">
                <a href="admin.php"
                   class="admin-nav-link <?= $activePage === 'dashboard' ? 'admin-nav-link--active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="9"/>
                        <rect x="14" y="3" width="7" height="5"/>
                        <rect x="14" y="12" width="7" height="9"/>
                        <rect x="3" y="16" width="7" height="5"/>
                    </svg>
                    Dashboard
                </a>
                <a href="admin-analytics.php"
                   class="admin-nav-link <?= $activePage === 'analytics' ? 'admin-nav-link--active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 3v18h18"/>
                        <path d="M18.7 8l-5.1 5.2-2.8-2.7L7 14.3"/>
                    </svg>
                    Analytics
                </a>
            </nav>
        </div>

        <div class="admin-sidebar__section">
            <h3 class="admin-sidebar__title">Gestão</h3>
            <nav class="admin-sidebar__nav">
                <a href="admin-members.php"
                   class="admin-nav-link <?= $activePage === 'members' ? 'admin-nav-link--active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                    Membros
                    <?php if ($members !== ''): ?>
                        <span class="admin-nav-link__badge"><?= $members ?></span>
                    <?php endif; ?>
                </a>
                <a href="admin-classes.php"
                   class="admin-nav-link <?= $activePage === 'classes' ? 'admin-nav-link--active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2"/>
                        <path d="M16 2v4M8 2v4M3 10h18"/>
                    </svg>
                    Aulas
                    <?php if ($classes !== ''): ?>
                        <span class="admin-nav-link__badge"><?= $classes ?></span>
                    <?php endif; ?>
                </a>
                <a href="admin-equipment.php"
                   class="admin-nav-link <?= $activePage === 'equipment' ? 'admin-nav-link--active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 4v6M6 14v6M18 4v6M18 14v6M3 7h6M15 7h6M3 17h6M15 17h6"/>
                    </svg>
                    Equipamento
                    <?php if ($equipment !== ''): ?>
                        <span class="admin-nav-link__badge"><?= $equipment ?></span>
                    <?php endif; ?>
                </a>
            </nav>
        </div>

        <div class="admin-sidebar__section">
            <h3 class="admin-sidebar__title">Sistema</h3>
            <nav class="admin-sidebar__nav">
                <a href="#" class="admin-nav-link">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="3"/>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06
                                 a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09
                                 A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83
                                 l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09
                                 A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83
                                 l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09
                                 a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83
                                 l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09
                                 a1.65 1.65 0 0 0-1.51 1z"/>
                    </svg>
                    Definições
                </a>
            </nav>
        </div>

    </aside>
<?php
}