<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/session.php');
require_once(__DIR__ . '/../database/connection.db.php');
require_once(__DIR__ . '/../database/user.class.php');
require_once(__DIR__ . '/../database/class.class.php');
require_once(__DIR__ . '/../templates/common.tpl.php');
require_once(__DIR__ . '/../templates/admin.tpl.php');

$session = new Session();

if (!$session->isLoggedIn()) {
    $session->addMessage('error', 'Precisas de estar autenticado.');
    header('Location: login.php');
    exit;
}

if ($session->getRole() !== 'admin') {
    $session->addMessage('error', 'Acesso restrito a administradores.');
    header('Location: dashboard.php');
    exit;
}

$db = getDatabaseConnection();

// Todos os utilizadores membros, ordenados por data de registo (mais recentes primeiro)
$allMembers = User::getAllUsers($db, 'member');
usort($allMembers, fn(User $a, User $b) => strcmp($b->createdAt, $a->createdAt));

// Paginação
$perPage     = 10;
$totalMembers = count($allMembers);
$totalPages  = (int)ceil($totalMembers / $perPage);
$currentPage = max(1, min($totalPages, (int)filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1));
$pageMembers = array_slice($allMembers, ($currentPage - 1) * $perPage, $perPage);

// Stats
$allUsers    = User::getAllUsers($db);
$allTrainers = User::getAllUsers($db, 'trainer');
$allClasses  = GymClass::getAllClasses($db);

$stats = [
    'members'  => count($allMembers),
    'trainers' => count($allTrainers),
    'classes'  => count($allClasses),
];

drawHeader($session, 'Admin Dashboard', '/css/pages/admin.css');
?>

<div class="admin-layout">

    <?php drawAdminSidebar('dashboard'); ?>

    <main class="admin-content">
        <header class="admin-header">
            <div class="admin-header__info">
                <h1>Dashboard</h1>
                <p>Visão geral do ginásio Equilibrio</p>
            </div>
            <a href="admin-classes.php" class="btn btn--primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
                Nova Aula
            </a>
        </header>

        <?php drawAdminStats($stats); ?>

        <?php drawMembersTable($pageMembers, $currentPage, $totalPages, $totalMembers, $perPage); ?>

    </main>
</div>

<?php drawFooter(); ?>