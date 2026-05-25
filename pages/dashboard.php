<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/session.php');
require_once(__DIR__ . '/../database/connection.db.php');
require_once(__DIR__ . '/../database/user.class.php');
require_once(__DIR__ . '/../database/enrollment.class.php');
require_once(__DIR__ . '/../database/class.class.php');
require_once(__DIR__ . '/../templates/common.tpl.php');
require_once(__DIR__ . '/../templates/dashboard.tpl.php');

$session = new Session();

if (!$session->isLoggedIn()) {
    $session->addMessage('error', 'Precisas de estar autenticado para ver o dashboard.');
    header('Location: login.php');
    exit;
}

$db          = getDatabaseConnection();
$user        = User::getUser($db, $session->getId());
$enrollments = Enrollment::getUserEnrollments($db, $session->getId());

if (!$user) {
    $session->addMessage('error', 'Utilizador não encontrado.');
    header('Location: login.php');
    exit;
}

// Buscar detalhes das aulas inscritas
$upcomingClasses = [];
foreach ($enrollments as $enrollment) {
    $gymClass = GymClass::getClass($db, $enrollment->classId);
    if ($gymClass && $gymClass->isActive) {
        $upcomingClasses[] = $gymClass;
    }
}

// Ordenar por dia da semana e hora
$dayOrder = ['monday'=>1,'tuesday'=>2,'wednesday'=>3,'thursday'=>4,'friday'=>5,'saturday'=>6,'sunday'=>7];
usort($upcomingClasses, function (GymClass $a, GymClass $b) use ($dayOrder) {
    $diff = ($dayOrder[$a->dayOfWeek] ?? 8) - ($dayOrder[$b->dayOfWeek] ?? 8);
    return $diff !== 0 ? $diff : strcmp($a->startTime, $b->startTime);
});

$recentEnrollments = array_slice($enrollments, 0, 4);

drawHeader($session, 'Dashboard', '', 'css/pages/dashboard.css');
?>

<main class="dashboard-container">

    <!-- Boas-vindas -->
    <section class="dashboard-welcome">
        <div class="dashboard-welcome__content">
            <p class="dashboard-welcome__greeting">Bem-vindo,</p>
            <h1 class="dashboard-welcome__name"><?= htmlspecialchars($user->fullName) ?></h1>
            <p class="dashboard-welcome__message">
                <?php if (count($upcomingClasses) > 0): ?>
                    Tens <?= count($upcomingClasses) ?> aula<?= count($upcomingClasses) !== 1 ? 's' : '' ?> inscrita<?= count($upcomingClasses) !== 1 ? 's' : '' ?>. Continua assim!
                <?php else: ?>
                    Ainda não tens aulas inscritas. Explora o horário e inscreve-te!
                <?php endif; ?>
            </p>
        </div>
    </section>

    <!-- Estatísticas -->
    <?php drawDashboardStats(count($enrollments), $user); ?>

    <!-- Grelha principal -->
    <div class="dashboard-grid">

        <!-- Próximas aulas -->
        <section class="dashboard-section">
            <header class="dashboard-section__header">
                <h2 class="dashboard-section__title">Próximas Aulas</h2>
                <a href="schedule.php" class="btn btn--ghost">Ver Todas</a>
            </header>
            <div class="dashboard-section__content">
                <?php if (empty($upcomingClasses)): ?>
                    <p class="dashboard-empty">
                        Não tens aulas inscritas. <a href="schedule.php">Ver horário</a>
                    </p>
                <?php else: ?>
                    <?php foreach ($upcomingClasses as $gymClass): ?>
                        <?php drawUpcomingClass($gymClass, $session->getId(), $db); ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- Atividade recente -->
        <section class="dashboard-section">
            <header class="dashboard-section__header">
                <h2 class="dashboard-section__title">Atividade Recente</h2>
            </header>
            <div class="dashboard-section__content">
                <?php if (empty($recentEnrollments)): ?>
                    <p class="dashboard-empty">Sem atividade recente.</p>
                <?php else: ?>
                    <div class="activity-list">
                        <?php foreach ($recentEnrollments as $enrollment): ?>
                            <?php drawActivityItem($enrollment); ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

    </div>
</main>

<?php drawFooter(); ?>