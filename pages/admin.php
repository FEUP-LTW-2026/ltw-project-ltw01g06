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

// Estatísticas reais
$stats = [
    'members'   => count(User::getAllUsers($db, 'member')),
    'trainers'  => count(User::getAllUsers($db, 'trainer')),
    'classes'   => count(GymClass::getAllClasses($db)),
    'equipment' => count(Equipment::getAll($db)),
];

// Membros recentes para a tabela
$perPage      = 5;
$allMembers   = User::getAllUsers($db);
$totalMembers = count($allMembers);
$totalPages   = max(1, (int)ceil($totalMembers / $perPage));
$currentPage  = max(1, min($totalPages, (int)(filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1)));
$pageMembers  = array_slice($allMembers, ($currentPage - 1) * $perPage, $perPage);

drawHeader($session, 'Admin Dashboard', 'javascript/admin.js', 'css/pages/admin.css');
?>

<div class="admin-layout">
    <?php drawAdminSidebar('dashboard'); ?>

    <main class="admin-content">
        <header class="admin-header">
            <div class="admin-header__info">
                <h1>Dashboard</h1>
                <p>Visão geral do ginásio Equilibrio</p>
            </div>
        </header>

        <?php drawAdminStats($stats); ?>
        <?php drawMembersTable($pageMembers, $currentPage, $totalPages, $totalMembers, $perPage); ?>
    </main>
</div>

<?php drawFooter('javascript/admin.js'); ?>