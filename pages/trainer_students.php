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

/* =========================================================
   Buscar alunos inscritos nas aulas do treinador
========================================================= */

$stmt = $db->prepare('
    SELECT DISTINCT users.id, users.full_name, users.email, users.photo
    FROM enrollments
    JOIN classes ON enrollments.class_id = classes.id
    JOIN users ON enrollments.user_id = users.id
    WHERE classes.trainer_id = ?
    ORDER BY users.full_name ASC
');

$stmt->execute([$user->id]);

$students = $stmt->fetchAll();

drawHeader($session, 'Alunos Inscritos', '', 'css/pages/dashboard.css');
?>

<main class="dashboard-container">
    <section class="dashboard-section">
        <header class="dashboard-section__header">
            <h2 class="dashboard-section__title">
                Alunos Inscritos
            </h2>
        </header>
        <div class="dashboard-section__content">
            <?php if (empty($students)): ?>
                <div class="empty-state">
                    <h3 class="empty-state__title">
                        Ainda não tens alunos inscritos
                    </h3>
                </div>
            <?php else: ?>
                <div class="activity-list">
                    <?php foreach ($students as $student): ?>

                        <div class="activity-item">
    <div class="roster-item__avatar">
    <?php if (
        !empty($student['photo']) &&
        file_exists(__DIR__ . '/../images/profiles/' . $student['photo'])
    ): ?>
        <img
            src="/images/profiles/<?= htmlspecialchars($student['photo']) ?>"
            alt="<?= htmlspecialchars($student['full_name']) ?>"
        >
    <?php else: ?>
        <span>
            <?= strtoupper(substr($student['full_name'], 0, 1)) ?>
        </span>
    <?php endif; ?>

</div>
    <div class="activity-item__content">
        <p class="activity-item__text">
            <strong><?= htmlspecialchars($student['full_name']) ?></strong>
        </p>
        <span class="activity-item__time">
            <?= htmlspecialchars($student['email']) ?>
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