<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/session.php');
require_once(__DIR__ . '/../database/connection.db.php');
require_once(__DIR__ . '/../database/user.class.php');
require_once(__DIR__ . '/../database/class.class.php');
require_once(__DIR__ . '/../database/equipment.class.php');
require_once(__DIR__ . '/../templates/common.tpl.php');
require_once(__DIR__ . '/../templates/admin.tpl.php');

$session = new Session();

if (!$session->isLoggedIn() || !$session->isAdmin()) {
    $session->addMessage('error', 'Acesso restrito a administradores.');
    header('Location: login.php');
    exit;
}

$db = getDatabaseConnection();

$counts = [
    'members'   => count(User::getAllUsers($db)),
    'trainers'  => count(User::getAllUsers($db, 'trainer')),
    'classes'   => count(GymClass::getAllClasses($db)),
    'equipment' => count(Equipment::getAll($db)),
];

$perPage     = 10;
$allUsers    = User::getAllUsers($db);
$total       = count($allUsers);
$totalPages  = max(1, (int)ceil($total / $perPage));
$currentPage = max(1, min($totalPages, (int)(filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1)));
$pageUsers   = array_slice($allUsers, ($currentPage - 1) * $perPage, $perPage);

drawHeader($session, 'Admin Dashboard', 'javascript/admin.js', 'css/pages/admin.css');
?>

<div class="admin-layout">
    <?php drawAdminSidebar('dashboard', $counts); ?>

    <main class="admin-content">
        <header class="admin-header">
            <div class="admin-header__info">
                <h1>Dashboard</h1>
                <p>Visão geral do ginásio Equilibrio</p>
            </div>
        </header>

        <!-- Stats -->
        <section class="admin-stats">
            <div class="admin-stat-card">
                <div class="admin-stat-card__header">
                    <div class="admin-stat-card__icon admin-stat-card__icon--members">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                    </div>
                    <span class="admin-stat-card__trend admin-stat-card__trend--up">Membros</span>
                </div>
                <span class="admin-stat-card__value"><?= $counts['members'] ?></span>
                <span class="admin-stat-card__label">Membros Ativos</span>
            </div>
            <div class="admin-stat-card">
                <div class="admin-stat-card__header">
                    <div class="admin-stat-card__icon admin-stat-card__icon--trainers">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <line x1="19" y1="8" x2="19" y2="14"/>
                            <line x1="22" y1="11" x2="16" y2="11"/>
                        </svg>
                    </div>
                    <span class="admin-stat-card__trend admin-stat-card__trend--up">Treinadores</span>
                </div>
                <span class="admin-stat-card__value"><?= $counts['trainers'] ?></span>
                <span class="admin-stat-card__label">Treinadores</span>
            </div>
            <div class="admin-stat-card">
                <div class="admin-stat-card__header">
                    <div class="admin-stat-card__icon admin-stat-card__icon--classes">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2"/>
                            <path d="M16 2v4M8 2v4M3 10h18"/>
                        </svg>
                    </div>
                    <span class="admin-stat-card__trend admin-stat-card__trend--up">Aulas</span>
                </div>
                <span class="admin-stat-card__value"><?= $counts['classes'] ?></span>
                <span class="admin-stat-card__label">Aulas Ativas</span>
            </div>
            <div class="admin-stat-card">
                <div class="admin-stat-card__header">
                    <div class="admin-stat-card__icon admin-stat-card__icon--revenue">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 4v6M6 14v6M18 4v6M18 14v6M3 7h6M15 7h6M3 17h6M15 17h6"/>
                        </svg>
                    </div>
                    <span class="admin-stat-card__trend admin-stat-card__trend--up">Equipamento</span>
                </div>
                <span class="admin-stat-card__value"><?= $counts['equipment'] ?></span>
                <span class="admin-stat-card__label">Equipamentos</span>
            </div>
        </section>

        <!-- Tabela de utilizadores -->
        <section class="admin-table-card">
            <header class="admin-table-card__header">
                <h2 class="admin-table-card__title">Utilizadores Recentes</h2>
                <div class="admin-table-card__actions">
                    <a href="admin-members.php" class="btn btn--secondary">Ver Todos</a>
                </div>
            </header>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Utilizador</th>
                        <th>Papel</th>
                        <th>Data Registo</th>
                        <th>Estado</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pageUsers as $u):
                        $parts    = explode(' ', $u->fullName);
                        $initials = mb_substr(implode('', array_map(fn($p) => strtoupper(mb_substr($p, 0, 1)), $parts)), 0, 2);
                        $roleLabel   = match($u->role) { 'admin' => 'Admin', 'trainer' => 'Treinador', default => 'Membro' };
                        $statusClass = $u->isActive ? 'active' : 'inactive';
                        $statusLabel = $u->isActive ? 'Ativo' : 'Inativo';
                    ?>
                        <tr>
                            <td>
                                <div class="admin-table__user">
                                    <div class="admin-table__user-avatar"><?= htmlspecialchars($initials) ?></div>
                                    <div class="admin-table__user-info">
                                        <span class="admin-table__user-name"><?= htmlspecialchars($u->fullName) ?></span>
                                        <span class="admin-table__user-email"><?= htmlspecialchars($u->email) ?></span>
                                    </div>
                                </div>
                            </td>
                            <td><?= $roleLabel ?></td>
                            <td><?= date('d M Y', strtotime($u->createdAt)) ?></td>
                            <td><span class="status-pill status-pill--<?= $statusClass ?>"><?= $statusLabel ?></span></td>
                            <td>
                                <div class="admin-table__actions">
                                    <form method="post" action="../actions/action_admin_toggle_user.php" style="display:inline">
                                        <input type="hidden" name="user_id" value="<?= $u->id ?>">
                                        <button type="submit" class="table-action-btn" title="<?= $u->isActive ? 'Desativar' : 'Ativar' ?>">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <?php if ($u->isActive): ?>
                                                    <path d="M17 7 7 17M7 7l10 10"/>
                                                <?php else: ?>
                                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                                    <path d="M22 4L12 14.01l-3-3"/>
                                                <?php endif; ?>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if ($totalPages > 1): ?>
                <div class="admin-pagination">
                    <span class="admin-pagination__info">
                        A mostrar <?= (($currentPage-1)*$perPage)+1 ?>–<?= min($currentPage*$perPage,$total) ?> de <?= $total ?>
                    </span>
                    <div class="admin-pagination__controls">
                        <?php if ($currentPage > 1): ?>
                            <a href="?page=<?= $currentPage-1 ?>" class="pagination-btn">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
                            </a>
                        <?php endif; ?>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?page=<?= $i ?>" class="pagination-btn <?= $i === $currentPage ? 'pagination-btn--active' : '' ?>"><?= $i ?></a>
                        <?php endfor; ?>
                        <?php if ($currentPage < $totalPages): ?>
                            <a href="?page=<?= $currentPage+1 ?>" class="pagination-btn">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </section>
    </main>
</div>

<?php drawFooter('javascript/admin.js'); ?>