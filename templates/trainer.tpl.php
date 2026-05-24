<?php
declare(strict_types=1);

function drawTrainersList(array $trainers): void {
    if (empty($trainers)): ?>
        <p style="text-align:center; color:var(--gray-color);">Nenhum treinador disponível de momento.</p>
    <?php return;
    endif;
    ?>
    <div class="trainers-grid">
        <?php foreach ($trainers as $trainer): ?>
            <article class="trainer-card">
                <div class="trainer-image">
                    <?php if ($trainer->photo && $trainer->photo !== 'default_profile.png'): ?>
                        <img src="/docs/<?= htmlspecialchars($trainer->photo) ?>"
                             alt="<?= htmlspecialchars($trainer->fullName ?? '') ?>" />
                    <?php else: ?>
                        <div class="trainer-placeholder">
                            <i class="fas fa-user"></i>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="trainer-info">
                    <h3><?= htmlspecialchars($trainer->fullName ?? 'Treinador') ?></h3>

                    <?php if ($trainer->bio): ?>
                        <p class="trainer-bio"><?= htmlspecialchars($trainer->bio) ?></p>
                    <?php endif; ?>

                    <?php $specs = $trainer->getSpecializationsArray(); ?>
                    <?php if (!empty($specs)): ?>
                        <div class="trainer-specializations">
                            <h4>Especializações</h4>
                            <ul>
                                <?php foreach ($specs as $spec): ?>
                                    <li>
                                        <i class="fas fa-check-circle"></i>
                                        <?= htmlspecialchars($spec) ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php $certs = $trainer->getCertificationsArray(); ?>
                    <?php if (!empty($certs)): ?>
                        <div class="trainer-certifications" style="margin-top:.75rem">
                            <h4>Certificações</h4>
                            <ul>
                                <?php foreach ($certs as $cert): ?>
                                    <li>
                                        <i class="fas fa-certificate"></i>
                                        <?= htmlspecialchars($cert) ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
    <?php
}
?>
