<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/session.php');
require_once(__DIR__ . '/../database/connection.db.php');
require_once(__DIR__ . '/../database/user.class.php');
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
$user    = User::getUser($db, $session->getId());
$classes = GymClass::getAllClasses($db);

$isTrainer = $user && strtolower($user->role) === 'trainer';

// Título e subtítulo adaptados ao role
$pageSubtitle = $isTrainer
    ? 'Consulta as aulas do ginásio e gere as tuas aulas'
    : 'Explora e inscreve-te nas aulas disponíveis';

// Passa o role ao template via variável global simples
// (evita alterar a assinatura de drawScheduleTable)
$GLOBALS['scheduleIsTrainer'] = $isTrainer;

// Nota: drawHeader já carrega o JS via $pageJs — NÃO passar o JS ao drawFooter
drawHeader($session, 'Horário de Aulas', 'javascript/schedule.js', 'css/pages/schedule.css');
?>

<main class="schedule-container">

    <header class="schedule-header">
        <div class="schedule-header__info">
            <h1>Horário de Aulas</h1>
            <p><?= htmlspecialchars($pageSubtitle) ?></p>
        </div>
    </header>

    <?php drawScheduleTable($classes, $session, $db); ?>

</main>

<?php
// drawFooter SEM argumento — o JS já foi carregado pelo drawHeader
drawFooter();
?>