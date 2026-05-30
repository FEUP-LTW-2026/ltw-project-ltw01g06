<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/session.php');
require_once(__DIR__ . '/../database/connection.db.php');
require_once(__DIR__ . '/../database/user.class.php');
require_once(__DIR__ . '/../database/class.class.php');
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

drawHeader($session, 'As Minhas Aulas', '', 'css/pages/dashboard.css');
?>

<main class="dashboard-container">
    <section class="dashboard-section">
        <header class="dashboard-section__header">
            <h2 class="dashboard-section__title">
                Aulas a Lecionar
            </h2>
        </header>
        <div class="dashboard-section__content">
            <?php if (empty($classes)): ?>
                <div class="empty-state">
                    <h3 class="empty-state__title">
                        Não tens aulas atribuídas
                    </h3>
                </div>
            <?php else: ?>
                <?php foreach ($classes as $class): ?>
                    <article class="upcoming-class">
                        <div class="upcoming-class__date">
                            <span class="upcoming-class__day">
                                <?php
$dayShort = [
    'monday'    => 'Seg',
    'tuesday'   => 'Ter',
    'wednesday' => 'Qua',
    'thursday'  => 'Qui',
    'friday'    => 'Sex',
    'saturday'  => 'Sáb',
    'sunday'    => 'Dom',
];
?>
<?= $dayShort[strtolower($class->dayOfWeek)] ?? $class->dayOfWeek ?>
                            </span>
                            <span class="upcoming-class__month">
                                <?= htmlspecialchars($class->startTime) ?>
                            </span>
                        </div>
                        <div class="upcoming-class__info">
                            <h3 class="upcoming-class__name">
                                <?= htmlspecialchars($class->name) ?>
                                <span class="tag">
                                    <?= htmlspecialchars($class->type) ?>
                                </span>
                            </h3>
                            <div class="upcoming-class__meta">
                                <span>
                                    <?= $class->currentEnrollment ?>/<?= $class->capacity ?> alunos
                                </span>
                                <span>
                                    <?= htmlspecialchars($class->startTime) ?>
                                    -
                                    <?= htmlspecialchars($class->getEndTime()) ?>
                                </span>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</main>
<?php drawFooter(); ?>