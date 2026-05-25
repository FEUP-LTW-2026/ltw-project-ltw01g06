<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/session.php');
require_once(__DIR__ . '/../database/connection.db.php');
require_once(__DIR__ . '/../database/trainer.class.php');
require_once(__DIR__ . '/../database/class.class.php');
require_once(__DIR__ . '/../templates/common.tpl.php');

$session  = new Session();
$db       = getDatabaseConnection();
$trainers = TrainerProfile::getAllProfiles($db);

// Recolher todos os tipos de especialização para os filtros
$allSpecs = [];
foreach ($trainers as $t) {
    foreach ($t->getSpecializationsArray() as $s) {
        $allSpecs[strtolower($s)] = $s;
    }
}
ksort($allSpecs);

drawHeader($session, 'Treinadores', '/css/pages/trainers.css');
?>

<main class="trainers-container">
    <header class="trainers-header">
        <h1 class="trainers-header__title">Os Nossos Treinadores</h1>
        <p class="trainers-header__subtitle">Conheça a equipa de profissionais dedicados ao seu bem-estar e objetivos fitness</p>
    </header>

    <!-- Filtros -->
    <nav class="trainers-filters">
        <button class="trainers-filter trainers-filter--active" data-spec="">Todos</button>
        <?php foreach ($allSpecs as $slug => $label): ?>
            <button class="trainers-filter" data-spec="<?= htmlspecialchars($slug) ?>">
                <?= htmlspecialchars($label) ?>
            </button>
        <?php endforeach; ?>
    </nav>

    <!-- Grid de Treinadores -->
    <section class="trainers-grid">
        <?php foreach ($trainers as $trainer):
            $parts    = explode(' ', $trainer->fullName ?? '');
            $initials = implode('', array_map(fn($p) => strtoupper(mb_substr($p, 0, 1)), $parts));
            $initials = mb_substr($initials, 0, 2);
            $specs    = $trainer->getSpecializationsArray();
            $certs    = $trainer->getCertificationsArray();
            $specsLower = array_map('strtolower', $specs);
        ?>
            <article class="trainer-card" data-specs="<?= htmlspecialchars(implode(',', $specsLower)) ?>">
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
                        <div class="trainer-card__stats" style="margin-top:var(--space-md);">
                            <?php foreach ($certs as $cert): ?>
                                <span style="font-size:.8rem;color:var(--color-text-muted);">
                                    🎓 <?= htmlspecialchars($cert) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="trainer-card__actions">
                        <a href="/pages/schedule.php" class="btn btn--primary">Ver Aulas</a>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>

        <?php if (empty($trainers)): ?>
            <p style="grid-column:1/-1;text-align:center;color:var(--color-text-muted);padding:3rem;">
                Nenhum treinador disponível de momento.
            </p>
        <?php endif; ?>
    </section>
</main>

<script>
document.querySelectorAll('.trainers-filter').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.trainers-filter').forEach(function(b) {
            b.classList.remove('trainers-filter--active');
        });
        btn.classList.add('trainers-filter--active');

        var spec = btn.dataset.spec;
        document.querySelectorAll('.trainer-card').forEach(function(card) {
            if (!spec || card.dataset.specs.split(',').includes(spec)) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    });
});
</script>

<?php drawFooter(); ?>