<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/session.php');
require_once(__DIR__ . '/../database/connection.db.php');
require_once(__DIR__ . '/../database/equipment.class.php');
require_once(__DIR__ . '/../templates/common.tpl.php');
require_once(__DIR__ . '/../templates/admin.tpl.php');
require_once(__DIR__ . '/../templates/admin-equipment.tpl.php');

$session = new Session();

if (!$session->isLoggedIn() || !$session->isAdmin()) {
    $session->addMessage('error', 'Acesso restrito a administradores.');
    header('Location: login.php');
    exit;
}

$db        = getDatabaseConnection();
$equipment = Equipment::getAll($db);

drawHeader($session, 'Equipamento — Admin', '/css/pages/admin.css');
?>

<div class="admin-layout">
    <?php drawAdminSidebar('equipment'); ?>

    <main class="admin-content">
        <header class="admin-header">
            <div class="admin-header__info">
                <h1>Equipamento</h1>
                <p>Gerir o equipamento do ginásio</p>
            </div>
            <button class="btn btn--primary" onclick="document.getElementById('modal-new-equipment').style.display='flex'">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
                Novo Equipamento
            </button>
        </header>

        <?php drawEquipmentTable($equipment); ?>
        <?php drawNewEquipmentModal(); ?>
    </main>
</div>

<?php drawFooter(); ?>