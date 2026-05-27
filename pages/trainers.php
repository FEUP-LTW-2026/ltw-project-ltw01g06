<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/session.php');
require_once(__DIR__ . '/../database/connection.db.php');
require_once(__DIR__ . '/../database/trainer.class.php');
require_once(__DIR__ . '/../templates/common.tpl.php');

$session  = new Session();
$db       = getDatabaseConnection();
$trainers = TrainerProfile::getAllProfiles($db);

$allSpecs = [];
foreach ($trainers as $t) {
    foreach ($t->getSpecializationsArray() as $s) {
        $allSpecs[strtolower($s)] = $s;
    }
}
ksort($allSpecs);

drawHeader($session, 'Treinadores', 'javascript/trainers.js', 'css/pages/trainers.css');
?>

<main class="trainers-container">

    <header class="trainers-header">
        <h1 class="trainers-header__title">Os Nossos Treinadores</h1>
        <p class="trainers-header__subtitle">
            Conheça a equipa de profissionais dedicados ao seu bem-estar e objetivos fitness
        </p>
    </header>

    <nav class="trainers-filters">
        <button class="trainers-filter trainers-filter--active" data-spec="">Todos</button>
        <?php foreach ($allSpecs as $slug => $label): ?>
            <button class="trainers-filter" data-spec="<?= htmlspecialchars($slug) ?>">
                <?= htmlspecialchars($label) ?>
            </button>
        <?php endforeach; ?>
    </nav>

    <section class="trainers-grid">
        <?php foreach ($trainers as $trainer):
            $parts      = explode(' ', $trainer->fullName ?? '');
            $initials   = mb_substr(implode('', array_map(fn($p) => strtoupper(mb_substr($p, 0, 1)), $parts)), 0, 2);
            $specs      = $trainer->getSpecializationsArray();
            $certs      = $trainer->getCertificationsArray();
            $specsLower = implode(',', array_map('strtolower', $specs));
        ?>
            <article class="trainer-card" data-specs="<?= htmlspecialchars($specsLower) ?>">
                <div class="trainer-card__image">
                    <div class="trainer-card__avatar"><?= htmlspecialchars($initials) ?></div>
                </div>
                <div class="trainer-card__content">
                    <h3 class="trainer-card__name"><?= htmlspecialchars($trainer->fullName ?? '') ?></h3>

                    <?php if (!empty($specs)): ?>
                        <p class="trainer-card__role"><?= htmlspecialchars($specs[0]) ?></p>
                    <?php endif; ?>

                    <?php if ($trainer->bio): ?>
                        <p class="trainer-card__bio"><?= htmlspecialchars($trainer->bio) ?></p>
                    <?php endif; ?>

                    <?php if (!empty($specs)): ?>
                        <div class="trainer-card__specializations">
                            <?php foreach ($specs as $spec): ?>
                                <span class="tag tag--<?= htmlspecialchars(strtolower(str_replace(' ', '-', $spec))) ?>">
                                    <?= htmlspecialchars($spec) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($certs)): ?>
                        <div class="trainer-card__stats">
                            <?php foreach ($certs as $cert): ?>
                                <div class="trainer-card__stat">
                                    <span class="trainer-card__stat-value">✓</span>
                                    <span class="trainer-card__stat-label"><?= htmlspecialchars($cert) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="trainer-card__actions">
                        <a href="schedule.php?trainer=<?= $trainer->userId ?>" class="btn btn--primary">Ver Aulas</a>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>

        <?php if (empty($trainers)): ?>
            <p style="grid-column:1/-1;text-align:center;color:var(--color-text-muted);padding:3rem">
                Nenhum treinador disponível de momento.
            </p>
        <?php endif; ?>
    </section>

</main>

<?php drawFooter('javascript/trainers.js'); ?>