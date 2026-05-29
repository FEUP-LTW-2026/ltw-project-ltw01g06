<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/session.php');
require_once(__DIR__ . '/../database/connection.db.php');
require_once(__DIR__ . '/../database/user.class.php');
require_once(__DIR__ . '/../database/class.class.php');
require_once(__DIR__ . '/../database/trainer.class.php');
require_once(__DIR__ . '/../templates/common.tpl.php');
require_once(__DIR__ . '/../templates/class.tpl.php');

$session = new Session();
if (!$session->isLoggedIn()) {
    $session->addMessage('error', 'Precisas de estar autenticado para ver o horário.');
    header('Location: login.php');
    exit;
}

$db   = getDatabaseConnection();
$user = User::getUser($db, $session->getId());

$isTrainer = $user && strtolower($user->role) === 'trainer';

// --- Filtro por treinador via ?trainer=ID (vindo do botão "Ver Horário") ---
$filterTrainerId   = null;
$filterTrainerName = null;

if (isset($_GET['trainer']) && ctype_digit($_GET['trainer'])) {
    $filterTrainerId = (int) $_GET['trainer'];

    // Carrega o nome do treinador para o subtítulo da página
    $trainerProfile = TrainerProfile::getProfile($db, $filterTrainerId);
    if ($trainerProfile) {
        $filterTrainerName = $trainerProfile->fullName;
    } else {
        // ID inválido — ignora o filtro
        $filterTrainerId = null;
    }
}

// Carrega todas as aulas ou só as do treinador filtrado
$classes = $filterTrainerId !== null
    ? GymClass::getClassesByTrainer($db, $filterTrainerId)
    : GymClass::getAllClasses($db);

// Título e subtítulo adaptados
if ($filterTrainerName) {
    $pageSubtitle = 'A mostrar as aulas de ' . htmlspecialchars($filterTrainerName);
} elseif ($isTrainer) {
    $pageSubtitle = 'Consulta as aulas do ginásio e gere as tuas aulas';
} else {
    $pageSubtitle = 'Explora e inscreve-te nas aulas disponíveis';
}

$GLOBALS['scheduleIsTrainer']   = $isTrainer;
$GLOBALS['scheduleTrainerId']   = $filterTrainerId;   // usado em class.tpl.php para pré-selecionar o dropdown

drawHeader($session, 'Horário de Aulas', 'javascript/schedule.js', 'css/pages/schedule.css');
?>

<main class="schedule-container">

    <header class="schedule-header">
        <div class="schedule-header__info">
            <h1>Horário de Aulas</h1>
            <p><?= htmlspecialchars($pageSubtitle) ?></p>
        </div>
        <?php if ($filterTrainerId !== null): ?>
            <a href="schedule.php" class="btn btn--secondary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     width="16" height="16">
                    <path d="M19 12H5M12 5l-7 7 7 7"/>
                </svg>
                Ver todas as aulas
            </a>
        <?php endif; ?>
    </header>

    <?php drawScheduleTable($classes, $session, $db); ?>

</main>

<?php
drawFooter('javascript/schedule.js');