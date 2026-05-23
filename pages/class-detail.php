<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/session.php');
require_once(__DIR__ . '/../database/connection.db.php');
require_once(__DIR__ . '/../database/user.class.php');
require_once(__DIR__ . '/../database/class.class.php');
require_once(__DIR__ . '/../database/enrollment.class.php');
require_once(__DIR__ . '/../database/waitlist.class.php');
require_once(__DIR__ . '/../database/review.class.php');
require_once(__DIR__ . '/../templates/common.tpl.php');
require_once(__DIR__ . '/../templates/class-detail.tpl.php');

$session = new Session();

if (!$session->isLoggedIn()) {
    $session->addMessage('error', 'Precisas de estar autenticado para ver os detalhes da aula.');
    header('Location: login.php');
    exit;
}

// Validar id da aula
$classId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$classId) {
    $session->addMessage('error', 'Aula não encontrada.');
    header('Location: schedule.php');
    exit;
}

$db     = getDatabaseConnection();
$userId = $session->getId();

$gymClass = GymClass::getClass($db, $classId);
if (!$gymClass) {
    $session->addMessage('error', 'Aula não encontrada.');
    header('Location: schedule.php');
    exit;
}

// Estado do utilizador relativamente a esta aula
$isEnrolled      = Enrollment::isUserEnrolled($db, $userId, $classId);
$waitlistPosition = Waitlist::getUserPosition($db, $userId, $classId);
$isOnWaitlist    = $waitlistPosition !== null;

// Reviews
$reviews      = Review::getClassReviews($db, $classId);
$reviewStats  = Review::getClassStats($db, $classId);
$userReview   = Review::getUserReview($db, $userId, $classId);

drawHeader($session, htmlspecialchars($gymClass->name) . ' — Equilibrio');
?>

<main class="class-detail-container">

    <!-- Breadcrumb -->
    <nav class="breadcrumb">
        <a href="schedule.php" class="breadcrumb__link">Horário</a>
        <span class="breadcrumb__separator">/</span>
        <span class="breadcrumb__current"><?= htmlspecialchars($gymClass->name) ?></span>
    </nav>

    <!-- Class Header -->
    <?php drawClassHeader($gymClass); ?>

    <!-- Enrollment Section -->
    <?php drawEnrollmentSection($gymClass, $isEnrolled, $isOnWaitlist, $waitlistPosition); ?>

    <!-- Reviews Section -->
    <?php drawReviewsSection($reviews, $reviewStats, $userReview, $classId, $isEnrolled); ?>

</main>

<?php drawFooter(); ?>