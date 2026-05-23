<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/session.php');
require_once(__DIR__ . '/../database/connection.db.php');
require_once(__DIR__ . '/../database/user.class.php');
require_once(__DIR__ . '/../database/class.class.php');
require_once(__DIR__ . '/../database/enrollment.class.php');
require_once(__DIR__ . '/../database/review.class.php');
require_once(__DIR__ . '/../templates/common.tpl.php');
require_once(__DIR__ . '/../templates/admin.tpl.php');
require_once(__DIR__ . '/../templates/admin-analytics.tpl.php');

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

// --- Aulas mais populares por número de inscrições ativas ---
$allClasses = GymClass::getAllClasses($db);
usort($allClasses, fn(GymClass $a, GymClass $b) => $b->currentEnrollment - $a->currentEnrollment);
$topClasses = array_slice($allClasses, 0, 5);

// --- Popularidade por tipo de aula ---
$typeStats = [];
foreach ($allClasses as $class) {
    $type = $class->type;
    if (!isset($typeStats[$type])) {
        $typeStats[$type] = 0;
    }
    $typeStats[$type] += $class->currentEnrollment;
}
arsort($typeStats);
$totalEnrollments = array_sum($typeStats);

// --- Inscrições por dia da semana (calculadas das aulas ativas) ---
$dayOrder = ['monday'=>'Seg','tuesday'=>'Ter','wednesday'=>'Qua','thursday'=>'Qui','friday'=>'Sex','saturday'=>'Sáb','sunday'=>'Dom'];
$enrollmentsByDay = array_fill_keys(array_keys($dayOrder), 0);
foreach ($allClasses as $class) {
    if (isset($enrollmentsByDay[$class->dayOfWeek])) {
        $enrollmentsByDay[$class->dayOfWeek] += $class->currentEnrollment;
    }
}
$maxDayEnrollment = max($enrollmentsByDay) ?: 1;

// --- Treinadores por avaliação média (via reviews) ---
$trainers = User::getAllUsers($db, 'trainer');
$trainerStats = [];
foreach ($trainers as $trainer) {
    // Buscar todas as aulas deste treinador e calcular média de reviews
    $trainerClasses = GymClass::getClassesByTrainer($db, $trainer->id);
    $totalRating = 0;
    $totalReviews = 0;
    $classTypes = [];
    foreach ($trainerClasses as $tc) {
        $stats = Review::getClassStats($db, $tc->id);
        $totalRating  += $stats['average'] * $stats['total'];
        $totalReviews += $stats['total'];
        $classTypes[]  = $tc->type;
    }
    $avgRating = $totalReviews > 0 ? round($totalRating / $totalReviews, 1) : 0;
    $trainerStats[] = [
        'trainer'    => $trainer,
        'avgRating'  => $avgRating,
        'types'      => implode(' e ', array_unique($classTypes)),
        'numClasses' => count($trainerClasses),
    ];
}
usort($trainerStats, fn($a, $b) => $b['avgRating'] <=> $a['avgRating']);
$topTrainers = array_slice($trainerStats, 0, 5);

// --- Stats gerais ---
$allMembers = User::getAllUsers($db, 'member');
$activeMembers = array_filter($allMembers, fn(User $u) => $u->isActive);
$avgEnrollmentsPerMember = count($allMembers) > 0
    ? round($totalEnrollments / count($allMembers), 1)
    : 0;

drawHeader($session, 'Analytics — Admin');
?>

<div class="admin-layout">

    <?php drawAdminSidebar('analytics'); ?>

    <main class="admin-content">
        <header class="admin-header">
            <div class="admin-header__info">
                <h1>Analytics</h1>
                <p>Métricas e insights do ginásio</p>
            </div>
        </header>

        <section class="analytics-grid">

            <?php drawEnrollmentsByDayChart($enrollmentsByDay, $dayOrder, $maxDayEnrollment); ?>

            <?php drawClassTypeDonut($typeStats, $totalEnrollments); ?>

            <?php drawKeyMetrics(count($activeMembers), count($allMembers), $avgEnrollmentsPerMember); ?>

            <?php drawTopClasses($topClasses); ?>

            <?php drawTopTrainers($topTrainers); ?>

        </section>
    </main>
</div>

<?php drawFooter(); ?>