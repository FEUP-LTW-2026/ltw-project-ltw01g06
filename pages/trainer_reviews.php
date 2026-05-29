<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/session.php');
require_once(__DIR__ . '/../database/connection.db.php');
require_once(__DIR__ . '/../database/user.class.php');
require_once(__DIR__ . '/../database/class.class.php');
require_once(__DIR__ . '/../database/review.class.php');
require_once(__DIR__ . '/../templates/common.tpl.php');

$session = new Session();

if (!$session->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$db = getDatabaseConnection();

$user = User::getUser($db, $session->getId());

if (!$user || strtolower($user->role) !== 'trainer') {
    header('Location: dashboard.php');
    exit;
}

$classes = GymClass::getClassesByTrainer($db, $user->id);

$reviews = [];

foreach ($classes as $class) {

    $classReviews = Review::getClassReviews($db, $class->id) ?? [];

    foreach ($classReviews as $review) {
        $reviews[] = $review;
    }
}

usort($reviews, fn($a, $b) =>
    strtotime($b->createdAt ?? '0') - strtotime($a->createdAt ?? '0')
);

drawHeader($session, 'Reviews Recebidas', '', 'css/pages/dashboard.css');
?>

<main class="dashboard-container">

    <section class="dashboard-section">

        <header class="dashboard-section__header">
            <h2 class="dashboard-section__title">
                Reviews Recebidas
            </h2>
        </header>

        <div class="dashboard-section__content">

            <?php if (empty($reviews)): ?>

                <div class="empty-state">
                    <h3 class="empty-state__title">
                        Ainda não tens reviews
                    </h3>
                </div>

            <?php else: ?>

                <div class="activity-list">

                    <?php foreach ($reviews as $r): ?>

                        <div class="activity-item">

                            <div class="activity-item__icon activity-item__icon--review">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                                </svg>
                            </div>

                            <div class="activity-item__content">

                                <p class="activity-item__text">

                                    <strong>
                                        <?= htmlspecialchars($r->className ?? 'Aula') ?>
                                    </strong>

                                    <span class="review-stars">

                                        <?php for ($i = 1; $i <= 5; $i++): ?>

                                            <span class="review-stars__star<?= $i <= $r->rating ? ' review-stars__star--filled' : '' ?>">
                                                ★
                                            </span>

                                        <?php endfor; ?>

                                    </span>

                                </p>

                                <?php if (!empty($r->userName)): ?>

                                    <p class="activity-item__author">
                                        <?= htmlspecialchars($r->userName) ?>
                                    </p>

                                <?php endif; ?>

                                <?php if (!empty($r->comment)): ?>

                                    <p class="activity-item__comment">
                                        "<?= htmlspecialchars($r->comment) ?>"
                                    </p>

                                <?php endif; ?>

                                <span class="activity-item__time">

                                    <?= $r->createdAt
                                        ? date('d/m/Y', strtotime($r->createdAt))
                                        : 'Data desconhecida' ?>

                                </span>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

        </div>

    </section>

</main>

<?php drawFooter(); ?>