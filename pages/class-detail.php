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

$session = new Session();

if (!$session->isLoggedIn()) {
    $session->addMessage('error', 'Precisas de estar autenticado.');
    header('Location: login.php');
    exit;
}

$db      = getDatabaseConnection();
$classId = (int)($_GET['id'] ?? 0);

if ($classId <= 0) {
    header('Location: schedule.php');
    exit;
}

$gymClass = GymClass::getClass($db, $classId);

if (!$gymClass) {
    $session->addMessage('error', 'Aula não encontrada.');
    header('Location: schedule.php');
    exit;
}

$userId    = $session->getId();
$user      = User::getUser($db, $userId);
$isTrainer = $user && strtolower($user->role) === 'trainer';

// Treinador desta aula específica não se pode inscrever
$isOwnClass = $isTrainer && $gymClass->trainerId === $userId;

$isEnrolled  = !$isTrainer && Enrollment::isUserEnrolled($db, $userId, $classId);
$waitlistPos = !$isTrainer && !$isEnrolled
    ? Waitlist::getUserPosition($db, $userId, $classId)
    : null;
$reviews    = Review::getClassReviews($db, $classId);
$stats      = Review::getClassStats($db, $classId);
$userReview = !$isTrainer ? Review::getUserReview($db, $userId, $classId) : null;

$typeSlug   = strtolower(str_replace(' ', '-', $gymClass->type));
$levelLabel = match($gymClass->level) {
    'beginner'     => 'Iniciante',
    'intermediate' => 'Intermédio',
    'advanced'     => 'Avançado',
    default        => ucfirst($gymClass->level)
};
$dayLabels = [
    'monday'    => 'Segunda', 'tuesday'  => 'Terça',   'wednesday' => 'Quarta',
    'thursday'  => 'Quinta',  'friday'   => 'Sexta',   'saturday'  => 'Sábado',
    'sunday'    => 'Domingo',
];
$day = $dayLabels[$gymClass->dayOfWeek] ?? ucfirst($gymClass->dayOfWeek);
$pct = $gymClass->capacity > 0
    ? round($gymClass->currentEnrollment / $gymClass->capacity * 100) : 0;

// SVGs de estrela cheia e vazia
$starFilled = '<svg class="star star--filled" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>';
$starEmpty  = '<svg class="star star--empty"  viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>';

// Distribuição de ratings
$dist = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
foreach ($reviews as $r) { $dist[$r->rating] = ($dist[$r->rating] ?? 0) + 1; }

// Classe CSS da barra de progresso
$progressClass = $pct >= 100 ? 'spots-progress__fill--full'
    : ($pct >= 80 ? 'spots-progress__fill--warning' : '');

// Nota: drawHeader já carrega o JS — NÃO passar ao drawFooter
drawHeader($session, htmlspecialchars($gymClass->name), 'javascript/class-detail.js', 'css/pages/classe-detail.css');
?>

<main class="class-detail-container">

    <!-- Breadcrumb -->
    <nav class="breadcrumb">
        <a href="schedule.php" class="breadcrumb__link">Horário</a>
        <span class="breadcrumb__separator">/</span>
        <span class="breadcrumb__current"><?= htmlspecialchars($gymClass->name) ?></span>
    </nav>

    <!-- ── Cabeçalho da aula ────────────────────────── -->
    <article class="class-header">
        <div class="class-header__content">
            <div class="class-header__tags">
                <span class="tag tag--<?= $typeSlug ?>"><?= htmlspecialchars($gymClass->type) ?></span>
                <span class="tag tag--<?= htmlspecialchars($gymClass->level) ?>"><?= $levelLabel ?></span>
                <?php if ($isOwnClass): ?>
                    <span class="tag tag--trainer-own">A tua aula</span>
                <?php endif; ?>
            </div>
            <h1 class="class-header__title"><?= htmlspecialchars($gymClass->name) ?></h1>
            <p class="class-header__description">
                <?= htmlspecialchars($gymClass->description ?: 'Sem descrição disponível.') ?>
            </p>
            <div class="class-header__meta">
                <div class="class-meta-item">
                    <div class="class-meta-item__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2"/>
                            <path d="M16 2v4M8 2v4M3 10h18"/>
                        </svg>
                    </div>
                    <div class="class-meta-item__info">
                        <span class="class-meta-item__label">Dia</span>
                        <span class="class-meta-item__value"><?= $day ?></span>
                    </div>
                </div>
                <div class="class-meta-item">
                    <div class="class-meta-item__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M12 6v6l4 2"/>
                        </svg>
                    </div>
                    <div class="class-meta-item__info">
                        <span class="class-meta-item__label">Horário</span>
                        <span class="class-meta-item__value">
                            <?= htmlspecialchars($gymClass->startTime) ?> – <?= htmlspecialchars($gymClass->getEndTime()) ?>
                        </span>
                    </div>
                </div>
                <?php if ($gymClass->trainerName): ?>
                    <div class="class-meta-item">
                        <div class="class-meta-item__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        </div>
                        <div class="class-meta-item__info">
                            <span class="class-meta-item__label">Treinador</span>
                            <span class="class-meta-item__value"><?= htmlspecialchars($gymClass->trainerName) ?></span>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="class-meta-item">
                    <div class="class-meta-item__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                    </div>
                    <div class="class-meta-item__info">
                        <span class="class-meta-item__label">Vagas</span>
                        <span class="class-meta-item__value">
                            <?= $gymClass->currentEnrollment ?>/<?= $gymClass->capacity ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </article>

    <!-- ── Secção de inscrição ──────────────────────── -->
    <section class="enrollment-section">
        <header class="enrollment-section__header">
            <h2 class="enrollment-section__title">Inscrição</h2>
            <div class="enrollment-section__spots">
                <div class="spots-progress">
                    <div class="spots-progress__fill <?= $progressClass ?>"
                         style="width:<?= $pct ?>%"></div>
                </div>
                <span class="spots-text">
                    <strong><?= $gymClass->currentEnrollment ?></strong>
                    de <?= $gymClass->capacity ?> vagas ocupadas
                </span>
            </div>
        </header>

        <?php if ($isTrainer): ?>
            <!-- Treinador: não se inscreve, apenas vê o estado -->
            <div class="enrollment-trainer-info">
                <div class="enrollment-trainer-info__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
                <div class="enrollment-trainer-info__content">
                    <?php if ($isOwnClass): ?>
                        <span class="enrollment-trainer-info__title">É o treinador desta aula</span>
                        <span class="enrollment-trainer-info__text">
        <?= $gymClass->currentEnrollment ?>
        aluno<?= $gymClass->currentEnrollment !== 1 ? 's' : '' ?>
        inscrito<?= $gymClass->currentEnrollment !== 1 ? 's' : '' ?>
    </span>

    <a
        href="class_students.php?id=<?= $classId ?>"
        class="enrollment-trainer-info__students-link"
        title="Ver alunos inscritos"
    >
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M15 3h6v6"/>
            <path d="M10 14L21 3"/>
            <path d="M21 14v7H3V3h7"/>
        </svg>
    </a>
                    <?php else: ?>
                        <span class="enrollment-trainer-info__title">Aula de outro treinador</span>
                        <span class="enrollment-trainer-info__text">Apenas membros se podem inscrever.</span>
                    <?php endif; ?>
                </div>
            </div>

        <?php elseif ($isEnrolled): ?>
            <!-- Membro inscrito -->
            <div class="enrollment-enrolled-notice">
                <div class="enrollment-enrolled-notice__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                        <path d="M22 4L12 14.01l-3-3"/>
                    </svg>
                </div>
                <div class="enrollment-enrolled-notice__content">
                    <span class="enrollment-enrolled-notice__title">Estás inscrito nesta aula!</span>
                    <span class="enrollment-enrolled-notice__text">Até já na próxima <?= $day ?>.</span>
                </div>
            </div>
            <div class="enrollment-actions" style="margin-top: var(--space-md)">
                <form method="post" action="../actions/action_enroll.php">
                    <input type="hidden" name="class_id" value="<?= $classId ?>">
                    <input type="hidden" name="action" value="cancel">
                    <button type="submit" class="btn btn--secondary"
                            onclick="return confirm('Cancelar inscrição?')">
                        Cancelar Inscrição
                    </button>
                </form>
            </div>

        <?php elseif ($waitlistPos !== null): ?>
            <!-- Na lista de espera -->
            <div class="waitlist-notice">
                <div class="waitlist-notice__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>
                    </svg>
                </div>
                <div class="waitlist-notice__content">
                    <span class="waitlist-notice__title">Na Lista de Espera</span>
                    <span class="waitlist-notice__text">Estás na posição <?= $waitlistPos ?>.</span>
                </div>
                <form method="post" action="../actions/action_enroll.php">
                    <input type="hidden" name="class_id" value="<?= $classId ?>">
                    <input type="hidden" name="action" value="waitlist_leave">
                    <button type="submit" class="btn btn--secondary">Sair da Lista</button>
                </form>
            </div>

        <?php elseif ($gymClass->hasAvailableSpots()): ?>
            <!-- Vaga disponível -->
            <div class="enrollment-actions">
                <form method="post" action="../actions/action_enroll.php">
                    <input type="hidden" name="class_id" value="<?= $classId ?>">
                    <input type="hidden" name="action" value="enroll">
                    <button type="submit" class="btn btn--primary btn--large">
                        Inscrever-me nesta Aula
                    </button>
                </form>
            </div>

        <?php else: ?>
            <!-- Aula cheia -->
            <div class="waitlist-notice">
                <div class="waitlist-notice__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>
                    </svg>
                </div>
                <div class="waitlist-notice__content">
                    <span class="waitlist-notice__title">Aula Completa</span>
                    <span class="waitlist-notice__text">Pode juntar-se à lista de espera.</span>
                </div>
                <form method="post" action="../actions/action_enroll.php">
                    <input type="hidden" name="class_id" value="<?= $classId ?>">
                    <input type="hidden" name="action" value="waitlist">
                    <button type="submit" class="btn btn--secondary">Juntar à Lista de Espera</button>
                </form>
            </div>
        <?php endif; ?>
    </section>

    <!-- ── Reviews ──────────────────────────────────── -->
    <section class="reviews-section">
        <header class="reviews-header">
            <div class="reviews-header__info">
                <h2>Avaliações e Reviews</h2>
                <span class="reviews-header__count"><?= $stats['total'] ?> avalia<?= $stats['total'] !== 1 ? 'ções' : 'ção' ?>
            </div>
        </header>

        <?php if ($stats['total'] > 0): ?>
            <div class="rating-summary">
                <div class="rating-summary__score">
                    <span class="rating-summary__number"><?= number_format($stats['average'], 1) ?></span>
                    <div class="rating-summary__stars">
                        <?php
                        $avg   = $stats['average'];
                        $full  = (int) floor($avg);
                        $empty = 5 - $full;
                        for ($i = 0; $i < $full;  $i++) echo $starFilled;
                        for ($i = 0; $i < $empty; $i++) echo $starEmpty;
                        ?>
                    </div>
                    <span class="rating-summary__total"><?= $stats['total'] ?> avaliações</span>
                </div>
                <div class="rating-bars">
                    <?php foreach ([5,4,3,2,1] as $star):
                        $count  = $dist[$star] ?? 0;
                        $barPct = $stats['total'] > 0 ? round($count / $stats['total'] * 100) : 0;
                    ?>
                        <div class="rating-bar">
                            <span class="rating-bar__label"><?= $star ?> ★</span>
                            <div class="rating-bar__track">
                                <div class="rating-bar__fill" style="width:<?= $barPct ?>%"></div>
                            </div>
                            <span class="rating-bar__count"><?= $count ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Formulário de review (só membros inscritos) -->
        <?php if ($isEnrolled): ?>
            <form class="review-form" method="post" action="../actions/action_review.php">
                <input type="hidden" name="class_id" value="<?= $classId ?>">
                <h3 class="review-form__title">
                    <?= $userReview ? 'Atualizar avaliação' : 'Deixe a sua avaliação' ?>
                </h3>
                <div class="review-form__rating">
                    <span class="review-form__rating-label">A sua avaliação:</span>
                    <div class="star-rating" id="star-rating">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <label class="star-rating__btn">
                                <!-- input escondido via CSS, não inline -->
                                <input type="radio" name="rating" value="<?= $i ?>"
                                       class="star-rating__input"
                                       <?= $userReview && $userReview->rating === $i ? 'checked' : '' ?> />
                                <?= $starFilled ?>
                            </label>
                        <?php endfor; ?>
                    </div>
                </div>
                <textarea class="review-form__textarea" name="comment"
                          placeholder="Partilhe a sua experiência..."><?= htmlspecialchars($userReview->comment ?? '') ?></textarea>
                <div class="review-form__actions">
                    <button type="submit" class="btn btn--primary">
                        <?= $userReview ? 'Atualizar Review' : 'Submeter Review' ?>
                    </button>
                    <?php if ($userReview): ?>
                        <button type="submit" name="delete" value="1" class="btn btn--secondary"
                                onclick="return confirm('Eliminar avaliação?')">
                            Eliminar
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        <?php endif; ?>

        <!-- Lista de reviews -->
        <div class="reviews-list">
            <?php if (empty($reviews)): ?>
                <p class="reviews-empty">
                    Ainda não há avaliações.
                    <?= $isEnrolled ? 'Sê o primeiro a avaliar!' : '' ?>
                </p>
            <?php else: ?>
                <?php foreach ($reviews as $review):
                    $rParts    = explode(' ', $review->userName ?? '');
                    $rInitials = mb_substr(
                        implode('', array_map(fn($p) => strtoupper(mb_substr($p, 0, 1)), $rParts)),
                        0, 2
                    );
                ?>
                    <article class="review-card">
                        <header class="review-card__header">
                            <div class="review-card__avatar"><?= htmlspecialchars($rInitials) ?></div>
                            <div class="review-card__info">
                                <span class="review-card__name"><?= htmlspecialchars($review->userName ?? 'Membro') ?></span>
                                <span class="review-card__date">
                                    <?= $review->createdAt ? date('d M Y', strtotime($review->createdAt)) : '' ?>
                                </span>
                            </div>
                            <div class="review-card__rating">
                                <?php
                                for ($i = 1; $i <= $review->rating;  $i++) echo $starFilled;
                                for ($i = $review->rating + 1; $i <= 5; $i++) echo $starEmpty;
                                ?>
                            </div>
                        </header>
                        <?php if ($review->comment): ?>
                            <p class="review-card__content"><?= htmlspecialchars($review->comment) ?></p>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

</main>

<?php
// drawFooter SEM argumento — JS já carregado pelo drawHeader
drawFooter();
?>