<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/session.php');
require_once(__DIR__ . '/../database/connection.db.php');
require_once(__DIR__ . '/../database/user.class.php');
require_once(__DIR__ . '/../database/class.class.php');
require_once(__DIR__ . '/../database/enrollment.class.php');
require_once(__DIR__ . '/../database/review.class.php');
require_once(__DIR__ . '/../templates/common.tpl.php');

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
    header('Location: login.php');
    exit;
}

$dayOrder = ['monday'=>1,'tuesday'=>2,'wednesday'=>3,'thursday'=>4,'friday'=>5,'saturday'=>6,'sunday'=>7];
$dayShort = ['monday'=>'Seg','tuesday'=>'Ter','wednesday'=>'Qua','thursday'=>'Qui',
             'friday'=>'Sex','saturday'=>'Sáb','sunday'=>'Dom'];

$upcomingClasses = [];
foreach ($enrollments as $e) {
    $class = GymClass::getClass($db, $e->classId);
    if ($class && $class->isActive) {
        $upcomingClasses[] = ['class' => $class, 'enrollment' => $e];
    }
}
usort($upcomingClasses, fn($a, $b) =>
    ($dayOrder[$a['class']->dayOfWeek] ?? 8) - ($dayOrder[$b['class']->dayOfWeek] ?? 8)
    ?: strcmp($a['class']->startTime, $b['class']->startTime)
);

$reviews = [];
foreach (array_slice($enrollments, 0, 8) as $e) {
    $r = Review::getUserReview($db, $user->id, $e->classId);
    if ($r) $reviews[] = $r;
}

$hour     = (int)date('H');
$greeting = $hour < 12 ? 'Bom dia' : ($hour < 18 ? 'Boa tarde' : 'Boa noite');

drawHeader($session, 'Dashboard', '', 'css/pages/dashboard.css');
?>

<main class="dashboard-container">

    <section class="dashboard-welcome">
        <div class="dashboard-welcome__content">
            <p class="dashboard-welcome__greeting"><?= $greeting ?>,</p>
            <h1 class="dashboard-welcome__name"><?= htmlspecialchars($user->fullName) ?></h1>
            <p class="dashboard-welcome__message">
                <?php if (count($upcomingClasses) > 0): ?>
                    Tens <?= count($upcomingClasses) ?> aula<?= count($upcomingClasses) !== 1 ? 's' : '' ?> inscrita<?= count($upcomingClasses) !== 1 ? 's' : '' ?>. Continua assim!
                <?php else: ?>
                    Ainda não tens aulas inscritas.
                    <a href="schedule.php" style="color:white;text-decoration:underline">
                        Explora o horário!
                    </a>
                <?php endif; ?>
            </p>
        </div>
    </section>

    <section class="dashboard-stats">
        <div class="dashboard-stat">
            <div class="dashboard-stat__icon dashboard-stat__icon--classes">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2"/>
                    <path d="M16 2v4M8 2v4M3 10h18"/>
                </svg>
            </div>
            <div class="dashboard-stat__info">
                <span class="dashboard-stat__value"><?= count($enrollments) ?></span>
                <span class="dashboard-stat__label">Aulas inscritas</span>
            </div>
        </div>
        <div class="dashboard-stat">
            <div class="dashboard-stat__icon dashboard-stat__icon--attendance">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <path d="M22 4L12 14.01l-3-3"/>
                </svg>
            </div>
            <div class="dashboard-stat__info">
                <span class="dashboard-stat__value"><?= htmlspecialchars(ucfirst($user->role)) ?></span>
                <span class="dashboard-stat__label">Tipo de conta</span>
            </div>
        </div>
        <div class="dashboard-stat">
            <div class="dashboard-stat__icon dashboard-stat__icon--streak">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
                </svg>
            </div>
            <div class="dashboard-stat__info">
                <span class="dashboard-stat__value"><?= count($reviews) ?></span>
                <span class="dashboard-stat__label">Reviews dadas</span>
            </div>
        </div>
        <div class="dashboard-stat">
            <div class="dashboard-stat__icon dashboard-stat__icon--points">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"/>
                </svg>
            </div>
            <div class="dashboard-stat__info">
                <span class="dashboard-stat__value"><?= $user->isActive ? 'Ativa' : 'Inativa' ?></span>
                <span class="dashboard-stat__label">Estado da conta</span>
            </div>
        </div>
    </section>

    <div class="dashboard-grid">

        <section class="dashboard-section">
            <header class="dashboard-section__header">
                <h2 class="dashboard-section__title">Próximas Aulas</h2>
                <a href="schedule.php" class="btn btn--ghost">Ver Todas</a>
            </header>
            <div class="dashboard-section__content">
                <?php if (empty($upcomingClasses)): ?>
                    <div class="empty-state">
                        <div class="empty-state__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2"/>
                                <path d="M16 2v4M8 2v4M3 10h18"/>
                            </svg>
                        </div>
                        <h3 class="empty-state__title">Sem aulas inscritas</h3>
                        <p class="empty-state__text">Explora o horário e inscreve-te!</p>
                        <a href="schedule.php" class="btn btn--primary">Ver Horário</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($upcomingClasses as $item):
                        $class    = $item['class'];
                        $typeSlug = strtolower(str_replace(' ', '-', $class->type));
                        $day      = $dayShort[$class->dayOfWeek] ?? ucfirst($class->dayOfWeek);
                    ?>
                        <article class="upcoming-class">
                            <div class="upcoming-class__date">
                                <span class="upcoming-class__day"><?= $day ?></span>
                                <span class="upcoming-class__month">
                                    <?= htmlspecialchars($class->startTime) ?>
                                </span>
                            </div>
                            <div class="upcoming-class__info">
                                <h3 class="upcoming-class__name">
                                    <a href="class-detail.php?id=<?= $class->id ?>"
                                       style="color:inherit;text-decoration:none">
                                        <?= htmlspecialchars($class->name) ?>
                                    </a>
                                    <span class="tag tag--<?= $typeSlug ?>">
                                        <?= htmlspecialchars($class->type) ?>
                                    </span>
                                </h3>
                                <div class="upcoming-class__meta">
                                    <span>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"/>
                                            <path d="M12 6v6l4 2"/>
                                        </svg>
                                        <?= htmlspecialchars($class->startTime) ?> – <?= htmlspecialchars($class->getEndTime()) ?>
                                    </span>
                                    <?php if ($class->trainerName): ?>
                                        <span>
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                                <circle cx="12" cy="7" r="4"/>
                                            </svg>
                                            <?= htmlspecialchars($class->trainerName) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="upcoming-class__action">
                                <form method="post" action="../actions/action_enroll.php">
                                    <input type="hidden" name="class_id" value="<?= $class->id ?>">
                                    <input type="hidden" name="action" value="cancel">
                                    <button type="submit" class="btn btn--secondary"
                                            onclick="return confirm('Cancelar inscrição?')">
                                        Cancelar
                                    </button>
                                </form>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <section class="dashboard-section">
            <header class="dashboard-section__header">
                <h2 class="dashboard-section__title">Atividade Recente</h2>
            </header>
            <div class="dashboard-section__content">
                <?php if (empty($enrollments) && empty($reviews)): ?>
                    <div class="empty-state">
                        <div class="empty-state__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                <path d="M22 4L12 14.01l-3-3"/>
                            </svg>
                        </div>
                        <h3 class="empty-state__title">Sem atividade</h3>
                        <p class="empty-state__text">A tua atividade vai aparecer aqui.</p>
                    </div>
                <?php else: ?>
                    <div class="activity-list">
                        <?php foreach (array_slice($enrollments, 0, 4) as $e): ?>
                            <div class="activity-item">
                                <div class="activity-item__icon activity-item__icon--class">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                        <path d="M22 4L12 14.01l-3-3"/>
                                    </svg>
                                </div>
                                <div class="activity-item__content">
                                    <p class="activity-item__text">
                                        Inscrito em
                                        <strong><?= htmlspecialchars($e->className ?? 'aula') ?></strong>
                                    </p>
                                    <span class="activity-item__time">
                                        <?= date('d M Y', strtotime($e->enrolledAt)) ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php foreach ($reviews as $r): ?>
                            <div class="activity-item">
                                <div class="activity-item__icon activity-item__icon--review">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                                    </svg>
                                </div>
                                <div class="activity-item__content">
                                    <p class="activity-item__text">
                                        Review em
                                        <strong><?= htmlspecialchars($r->className ?? 'aula') ?></strong>
                                    </p>
                                    <span class="activity-item__time">
                                        <?= date('d M Y', strtotime($r->createdAt)) ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

    </div>
</main>

<?php drawFooter(); ?>