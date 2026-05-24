<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/session.php');
require_once(__DIR__ . '/../database/connection.db.php');
require_once(__DIR__ . '/../database/user.class.php');
require_once(__DIR__ . '/../templates/common.tpl.php');
require_once(__DIR__ . '/../templates/admin.tpl.php');
require_once(__DIR__ . '/../templates/admin-members.tpl.php');

$session = new Session();

if (!$session->isLoggedIn() || !$session->isAdmin()) {
    $session->addMessage('error', 'Acesso restrito a administradores.');
    header('Location: login.php');
    exit;
}

$db = getDatabaseConnection();

// Filtro por role
$roleFilter = $_GET['role'] ?? 'all';
$search     = trim($_GET['search'] ?? '');

$allUsers = $roleFilter !== 'all'
    ? User::getAllUsers($db, $roleFilter)
    : User::getAllUsers($db);

// Pesquisa por nome ou email
if ($search !== '') {
    $allUsers = array_filter($allUsers, function(User $u) use ($search) {
        return stripos($u->fullName, $search) !== false
            || stripos($u->email, $search) !== false
            || stripos($u->username, $search) !== false;
    });
    $allUsers = array_values($allUsers);
}

// Paginação
$perPage      = 15;
$total        = count($allUsers);
$totalPages   = max(1, (int)ceil($total / $perPage));
$currentPage  = max(1, min($totalPages, (int)filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1));
$pageUsers    = array_slice($allUsers, ($currentPage - 1) * $perPage, $perPage);

drawHeader($session, 'Gestão de Membros — Admin');
?>

<div class="admin-layout">
    <?php drawAdminSidebar('members'); ?>

    <main class="admin-content">
        <header class="admin-header">
            <div class="admin-header__info">
                <h1>Utilizadores</h1>
                <p>Gerir membros, treinadores e admins</p>
            </div>
            <a href="admin-user-create.php" class="btn btn--primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
                Novo Utilizador
            </a>
        </header>

        <?php drawUsersFilter($roleFilter, $search); ?>
        <?php drawUsersTable($pageUsers, $currentPage, $totalPages, $total, $perPage); ?>
    </main>
</div>

<?php drawFooter(); ?>
