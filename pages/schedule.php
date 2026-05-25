<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/session.php');
require_once(__DIR__ . '/../database/connection.db.php');
require_once(__DIR__ . '/../database/class.class.php');
require_once(__DIR__ . '/../templates/common.tpl.php');
require_once(__DIR__ . '/../templates/class.tpl.php');

$session = new Session();

if (!$session->isLoggedIn()) {
    $session->addMessage('error', 'Precisas de estar autenticado para ver o horário.');
    header('Location: login.php');
    exit;
}

$db      = getDatabaseConnection();
$classes = GymClass::getAllClasses($db);

drawHeader($session, 'Horário de Aulas', '/css/pages/schedule.css');
?>

<main class="schedule-container">
    <header class="schedule-header">
        <div class="schedule-header__info">
            <h1>Horário de Aulas</h1>
            <p>Explore e inscreva-se nas aulas disponíveis</p>
        </div>
    </header>

    <?php drawScheduleTable($classes, $session, $db); ?>
</main>

<?php drawFooter(); ?>