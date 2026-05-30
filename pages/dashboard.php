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

$db   = getDatabaseConnection();
$user = User::getUser($db, $session->getId());

if (!$user) {
    header('Location: login.php');
    exit;
}

/* ------------------------------------------------------------------ */
/*  Mapeamentos de dias                                                  */
/* ------------------------------------------------------------------ */
$dayOrder = [
    'monday'    => 1, 'tuesday' => 2, 'wednesday' => 3,
    'thursday'  => 4, 'friday'  => 5, 'saturday'  => 6, 'sunday' => 7,
];
$dayShort = [
    'monday'    => 'Seg', 'tuesday'   => 'Ter', 'wednesday' => 'Qua',
    'thursday'  => 'Qui', 'friday'    => 'Sex', 'saturday'  => 'Sáb',
    'sunday'    => 'Dom',
];

/* ------------------------------------------------------------------ */
/*  Helper: verificar se uma aula ainda não aconteceu esta semana       */
/* ------------------------------------------------------------------ */
function isUpcoming(string $dayOfWeek, string $startTime, array $dayOrder): bool {
    $todayNum  = (int) date('N');
    $classNum  = $dayOrder[$dayOfWeek] ?? 8;
    $nowTime   = date('H:i');

    if ($classNum > $todayNum) return true;
    if ($classNum === $todayNum && $startTime > $nowTime) return true;
    return false;
}

/* ------------------------------------------------------------------ */
/*  TREINADOR                                                            */
/* ------------------------------------------------------------------ */
$isTrainer = strtolower($user->role) === 'trainer';

if ($isTrainer) {
    $trainerClasses = GymClass::getClassesByTrainer($db, $user->id) ?? [];

    $upcomingClasses = [];
    foreach ($trainerClasses as $class) {
        if ($class->isActive) {
            $upcomingClasses[] = ['class' => $class, 'enrollment' => null];
        }
    }
    usort($upcomingClasses, fn($a, $b) =>
        ($dayOrder[$a['class']->dayOfWeek] ?? 8) - ($dayOrder[$b['class']->dayOfWeek] ?? 8)
        ?: strcmp($a['class']->startTime, $b['class']->startTime)
    );

    $totalStudents = 0;
    foreach ($trainerClasses as $class) {
        $totalStudents += $class->currentEnrollment; // MELHORIA: usar currentEnrollment em vez de query extra
    }

    $trainerReviews = [];
    foreach ($trainerClasses as $class) {
        $classReviews = Review::getClassReviews($db, $class->id) ?? [];
        foreach ($classReviews as $r) {
            $trainerReviews[] = $r;
        }
    }
    usort($trainerReviews, fn($a, $b) => strtotime($b->createdAt ?? '0') - strtotime($a->createdAt ?? '0'));
    $recentReviews = array_slice($trainerReviews, 0, 4);

    // MELHORIA: calcular rating médio para mostrar no banner
    $avgRating = 0;
    if (count($trainerReviews) > 0) {
        $avgRating = round(
            array_sum(array_map(fn($r) => $r->rating, $trainerReviews)) / count($trainerReviews),
            1
        );
    }

/* ------------------------------------------------------------------ */
/*  MEMBRO                                                               */
/* ------------------------------------------------------------------ */
} else {
    $enrollments = Enrollment::getUserEnrollments($db, $session->getId()) ?? [];

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

    $reviewCount = Review::countUserReviews($db, $user->id) ?? 0;

    $recentActivity = [];
    foreach (array_slice($enrollments, 0, 4) as $e) {
        $recentActivity[] = ['type' => 'enrollment', 'data' => $e];
    }
    $allReviews = [];
    foreach ($enrollments as $e) {
        $r = Review::getUserReview($db, $user->id, $e->classId);
        if ($r) $allReviews[] = $r;
    }
    usort($allReviews, fn($a, $b) => strtotime($b->createdAt ?? '0') - strtotime($a->createdAt ?? '0'));
    foreach (array_slice($allReviews, 0, 3) as $r) {
        $recentActivity[] = ['type' => 'review', 'data' => $r];
    }
}

/* ------------------------------------------------------------------ */
/*  Saudação                                                             */
/* ------------------------------------------------------------------ */
$hour     = (int) date('H');
$greeting = $hour < 12 ? 'Bom dia' : ($hour < 18 ? 'Boa tarde' : 'Boa noite');

drawHeader($session, 'Dashboard', '', 'css/pages/dashboard.css');
?>

<main class="dashboard-container">

    <!-- ============================================================
         Banner de boas-vindas
    ============================================================= -->
    <section class="dashboard-welcome">
        <div class="dashboard-welcome__content">
            <p class="dashboard-welcome__greeting"><?= $greeting ?>,</p>
            <h1 class="dashboard-welcome__name"><?= htmlspecialchars($user->fullName) ?></h1>
            <p class="dashboard-welcome__message">
                <?php if ($isTrainer): ?>
                    <?php $n = count($upcomingClasses); ?>
                    <?php if ($n > 0): ?>
                        Tens <?= $n ?> aula<?= $n !== 1 ? 's' : '' ?> para lecionar esta semana. Bom trabalho!
                    <?php else: ?>
                        Não tens aulas para lecionar esta semana.
                        <a href="schedule.php" class="dashboard-welcome__link">Ver horário completo</a>
                    <?php endif; ?>
                <?php else: ?>
                    <?php $n = count($upcomingClasses); ?>
                    <?php if ($n > 0): ?>
                        Tens <?= $n ?> aula<?= $n !== 1 ? 's' : '' ?> inscrita<?= $n !== 1 ? 's' : '' ?> esta semana. Continua assim!
                    <?php else: ?>
                        Ainda não tens aulas inscritas.
                        <a href="schedule.php" class="dashboard-welcome__link">Explora o horário!</a>
                    <?php endif; ?>
                <?php endif; ?>
            </p>
        </div>
        <!-- MELHORIA: badges de resumo rápido no banner (só treinador) -->
        <?php if ($isTrainer && (count($trainerReviews) > 0 || $totalStudents > 0)): ?>
        <div class="dashboard-welcome__badges">
            <?php if ($totalStudents > 0): ?>
            <div class="dashboard-welcome__badge">
                <span class="dashboard-welcome__badge-value"><?= $totalStudents ?></span>
                <span class="dashboard-welcome__badge-label">alunos</span>
            </div>
            <?php endif; ?>
            <?php if ($avgRating > 0): ?>
            <div class="dashboard-welcome__badge">
                <span class="dashboard-welcome__badge-value"><?= $avgRating ?> ⭐</span>
                <span class="dashboard-welcome__badge-label">rating médio</span>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </section>

    <!-- ============================================================
         Cards de estatísticas
    ============================================================= -->
    <section class="dashboard-stats">

        <?php if ($isTrainer): ?>

            <a href="trainer_classes.php" class="dashboard-stat dashboard-stat--clickable">

    <div class="dashboard-stat__icon dashboard-stat__icon--classes">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="4" width="18" height="18" rx="2"/>
            <path d="M16 2v4M8 2v4M3 10h18"/>
        </svg>
    </div>

    <div class="dashboard-stat__info">
        <span class="dashboard-stat__value"><?= count($trainerClasses) ?></span>
        <span class="dashboard-stat__label">Aulas a lecionar</span>
    </div>

</a>

            <a href="trainer_students.php" class="dashboard-stat dashboard-stat--clickable">

    <div class="dashboard-stat__icon dashboard-stat__icon--attendance">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
        </svg>
    </div>

    <div class="dashboard-stat__info">
        <span class="dashboard-stat__value"><?= $totalStudents ?></span>
        <span class="dashboard-stat__label">Alunos inscritos</span>
    </div>

</a>

            
<a href="trainer_reviews.php" class="dashboard-stat dashboard-stat--clickable">

    <div class="dashboard-stat__icon dashboard-stat__icon--streak">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
        </svg>
    </div>

    <div class="dashboard-stat__info">

        <span class="dashboard-stat__value">
            <?= count($trainerReviews) ?>
        </span>

        <span class="dashboard-stat__label">
            Reviews recebidas
        </span>

        <?php if ($avgRating > 0): ?>
            <span class="dashboard-stat__sublabel">
                <?= $avgRating ?> ⭐ média
            </span>
        <?php endif; ?>

    </div>

</a>

        <?php else: ?>

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
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                    </svg>
                </div>
                <div class="dashboard-stat__info">
                    <span class="dashboard-stat__value"><?= $reviewCount ?></span>
                    <span class="dashboard-stat__label">Reviews dadas</span>
                </div>
            </div>

        <?php endif; ?>


    </section>

    <!-- ============================================================
         Grelha principal
    ============================================================= -->
    <div class="dashboard-grid">

        <!-- Coluna esquerda: Próximas Aulas -->
        <section class="dashboard-section">
            <header class="dashboard-section__header">
                <h2 class="dashboard-section__title">
                    <?= $isTrainer ? 'Próximas Aulas a Lecionar' : 'Próximas Aulas' ?>
                </h2>
                <a href="schedule.php?trainer=<?= (int) $user->id ?>" class="btn btn--ghost">Ver Todas</a>
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
                        <h3 class="empty-state__title">
                            <?= $isTrainer ? 'Sem aulas esta semana' : 'Sem aulas inscritas' ?>
                        </h3>
                        <p class="empty-state__text">
                            <?= $isTrainer ? 'Não tens aulas agendadas para os próximos dias.' : 'Explora o horário e inscreve-te!' ?>
                        </p>
                        <a href="schedule.php" class="btn btn--primary">Ver Horário</a>
                    </div>

                <?php else: ?>
                    <?php foreach ($upcomingClasses as $item):
                        $class    = $item['class'];
                        $typeSlug = strtolower(str_replace(' ', '-', $class->type));
                        $day      = $dayShort[$class->dayOfWeek] ?? ucfirst($class->dayOfWeek);
                        // MELHORIA: calcular percentagem de ocupação
                        $fillPct  = $class->capacity > 0
                            ? min(100, (int) round($class->currentEnrollment / $class->capacity * 100))
                            : 0;
                        $fillClass = $fillPct >= 90 ? 'full' : ($fillPct >= 60 ? 'busy' : 'free');
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
                                    <a href="class-detail.php?id=<?= $class->id ?>" class="upcoming-class__link">
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
                                    <?php if (!$isTrainer && $class->trainerName): ?>
                                        <span>
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                                <circle cx="12" cy="7" r="4"/>
                                            </svg>
                                            <?= htmlspecialchars($class->trainerName) ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($isTrainer): ?>
                                        <span>
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                                <circle cx="9" cy="7" r="4"/>
                                                <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                                            </svg>
                                            <?= $class->currentEnrollment ?>/<?= $class->capacity ?> alunos
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <!-- MELHORIA: barra de ocupação (só treinador) -->
                                <?php if ($isTrainer): ?>
                                <div class="upcoming-class__occupancy">
                                    <div class="occupancy-bar">
                                        <div class="occupancy-bar__fill occupancy-bar__fill--<?= $fillClass ?>"
                                             style="width: <?= $fillPct ?>%"></div>
                                    </div>
                                    <span class="occupancy-bar__label occupancy-bar__label--<?= $fillClass ?>">
                                        <?php if ($fillPct >= 90): ?>Quase cheio
                                        <?php elseif ($fillPct >= 60): ?>A encher
                                        <?php else: ?>Com vagas
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php if (!$isTrainer): ?>
                                <div class="upcoming-class__action">
                                    <form method="post" action="../actions/action_enroll.php">
                <?= csrfInput($session) ?>
                                        <input type="hidden" name="class_id" value="<?= $class->id ?>">
                                        <input type="hidden" name="action" value="cancel">
                                        <button type="submit" class="btn btn--secondary"
                                                onclick="return confirm('Cancelar inscrição?')">
                                            Cancelar
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>

            </div>
        </section>

        <!-- Coluna direita: Reviews / Atividade Recente -->
        <section class="dashboard-section">
            <header class="dashboard-section__header">
                <h2 class="dashboard-section__title">
                    <?= $isTrainer ? 'Reviews Recebidas' : 'Atividade Recente' ?>
                </h2>
            </header>
            <div class="dashboard-section__content">

                <?php if ($isTrainer): ?>

                    <?php if (empty($recentReviews)): ?>
                        <div class="empty-state">
                            <div class="empty-state__icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                                </svg>
                            </div>
                            <h3 class="empty-state__title">Sem reviews</h3>
                            <p class="empty-state__text">As reviews dos teus alunos vão aparecer aqui.</p>
                        </div>
                    <?php else: ?>
                        <div class="activity-list">
                            <?php foreach ($recentReviews as $r): ?>
                                <div class="activity-item">
                                    <div class="activity-item__icon activity-item__icon--review">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                                        </svg>
                                    </div>
                                    <div class="activity-item__content">
                                        <p class="activity-item__text">
                                            Review em <strong><?= htmlspecialchars($r->className ?? 'aula') ?></strong>
                                            <!-- MELHORIA: estrelas visuais em vez de "X/5 ⭐" -->
                                            <?php if (!empty($r->rating)): ?>
                                            <span class="review-stars" aria-label="<?= (int)$r->rating ?> estrelas">
                                                <?php for ($s = 1; $s <= 5; $s++): ?>
                                                    <span class="review-stars__star<?= $s <= $r->rating ? ' review-stars__star--filled' : '' ?>">★</span>
                                                <?php endfor; ?>
                                            </span>
                                            <?php endif; ?>
                                        </p>
                                        <!-- MELHORIA: nome do aluno -->
                                        <?php if (!empty($r->userName)): ?>
                                        <p class="activity-item__author">
                                            <?= htmlspecialchars($r->userName) ?>
                                        </p>
                                        <?php endif; ?>
                                        <!-- MELHORIA: mostrar o comentário da review -->
                                        <?php if (!empty($r->comment)): ?>
                                        <p class="activity-item__comment">
                                            "<?= htmlspecialchars($r->comment) ?>"
                                        </p>
                                        <?php endif; ?>
                                        <span class="activity-item__time">
                                            <?= $r->createdAt ? date('d M Y', strtotime($r->createdAt)) : 'Data desconhecida' ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                <?php else: ?>

                    <?php if (empty($recentActivity)): ?>
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
                            <?php foreach ($recentActivity as $item): ?>
                                <div class="activity-item">
                                    <?php if ($item['type'] === 'enrollment'): ?>
                                        <div class="activity-item__icon activity-item__icon--class">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                                <path d="M22 4L12 14.01l-3-3"/>
                                            </svg>
                                        </div>
                                        <div class="activity-item__content">
                                            <p class="activity-item__text">
                                                Inscrito em <strong><?= htmlspecialchars($item['data']->className ?? 'aula') ?></strong>
                                            </p>
                                            <span class="activity-item__time">
                                                <?= !empty($item['data']->enrolledAt)
                                                    ? date('d M Y', strtotime($item['data']->enrolledAt))
                                                    : 'Data desconhecida' ?>
                                            </span>
                                        </div>
                                    <?php else: ?>
                                        <div class="activity-item__icon activity-item__icon--review">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                                            </svg>
                                        </div>
                                        <div class="activity-item__content">
                                            <p class="activity-item__text">
                                                Review em <strong><?= htmlspecialchars($item['data']->className ?? 'aula') ?></strong>
                                            </p>
                                            <span class="activity-item__time">
                                                <?= !empty($item['data']->createdAt)
                                                    ? date('d M Y', strtotime($item['data']->createdAt))
                                                    : 'Data desconhecida' ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                <?php endif; ?>

            </div>
        </section>

    </div>
</main>

<?php drawFooter(); ?>