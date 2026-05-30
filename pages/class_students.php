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

$classId = (int)($_GET['id'] ?? 0);

if ($classId <= 0) {
    header('Location: trainer-classes.php');
    exit;
}

/* buscar aula */
$class = GymClass::getClass($db, $classId);

if (!$class) {
    header('Location: trainer-classes.php');
    exit;
}

/* segurança:
   treinador só pode ver alunos das próprias aulas */
if ($class->trainerId !== $user->id) {
    header('Location: trainer-classes.php');
    exit;
}

/* buscar alunos apenas desta aula */
$stmt = $db->prepare('
    SELECT users.id,
           users.full_name,
           users.email,
           users.photo
    FROM enrollments
    JOIN users
        ON enrollments.user_id = users.id
    WHERE enrollments.class_id = ?
    AND enrollments.status = "active"
    ORDER BY users.full_name ASC
');

$stmt->execute([$classId]);

$students = $stmt->fetchAll();

drawHeader(
    $session,
    'Alunos da Aula',
    '',
    'css/pages/dashboard.css'
);
?>

<main class="dashboard-container">
    <section class="dashboard-section">
        <header class="dashboard-section__header">
            <h2 class="dashboard-section__title">
                <?= htmlspecialchars($class->name) ?>
            </h2>
            <p>
                <?= count($students) ?>
                aluno<?= count($students) !== 1 ? 's' : '' ?>
                inscrito<?= count($students) !== 1 ? 's' : '' ?>
            </p>
        </header>
        <div class="dashboard-section__content">
            <?php if (empty($students)): ?>
                <div class="empty-state">
                    <h3 class="empty-state__title">
                        Ainda não existem alunos inscritos
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
```
