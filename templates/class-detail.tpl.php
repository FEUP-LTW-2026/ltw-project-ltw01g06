<?php
declare(strict_types=1);

function drawClassHeader(object $gymClass): void {
    $dayLabels = [
        'monday'=>'Segunda','tuesday'=>'Terça','wednesday'=>'Quarta',
        'thursday'=>'Quinta','friday'=>'Sexta','saturday'=>'Sábado','sunday'=>'Domingo'
    ];
    $day = $dayLabels[$gymClass->dayOfWeek] ?? ucfirst($gymClass->dayOfWeek);
    ?>
    <section class="class-detail-header">
        <div class="class-detail-header__content">
            <div class="class-detail-header__meta">
                <span class="tag tag--<?= htmlspecialchars(strtolower($gymClass->type)) ?>">
                    <?= htmlspecialchars($gymClass->type) ?>
                </span>
                <span class="tag tag--level">
                    <?= htmlspecialchars(ucfirst($gymClass->level)) ?>
                </span>
            </div>
            <h1 class="class-detail-header__title"><?= htmlspecialchars($gymClass->name) ?></h1>
            <?php if ($gymClass->description): ?>
                <p class="class-detail-header__desc"><?= htmlspecialchars($gymClass->description) ?></p>
            <?php endif; ?>

            <div class="class-detail-header__info">
                <span>
                    <i class="fas fa-calendar"></i>
                    <?= $day ?>
                </span>
                <span>
                    <i class="fas fa-clock"></i>
                    <?= htmlspecialchars($gymClass->startTime) ?> – <?= htmlspecialchars($gymClass->getEndTime()) ?>
                    (<?= $gymClass->durationMin ?> min)
                </span>
                <?php if ($gymClass->trainerName): ?>
                <span>
                    <i class="fas fa-user"></i>
                    <?= htmlspecialchars($gymClass->trainerName) ?>
                </span>
                <?php endif; ?>
                <span>
                    <i class="fas fa-users"></i>
                    <?= $gymClass->currentEnrollment ?>/<?= $gymClass->capacity ?> inscritos
                </span>
            </div>
        </div>
    </section>
    <?php
}

function drawEnrollmentSection(object $gymClass, bool $isEnrolled, bool $isOnWaitlist, ?int $waitlistPosition): void {
    ?>
    <section class="class-detail-section">
        <h2>Inscrição</h2>

        <?php if ($isEnrolled): ?>
            <p class="status-pill status-pill--active" style="display:inline-block;margin-bottom:1rem;">
                <i class="fas fa-check-circle"></i> Já estás inscrito nesta aula
            </p>
            <form action="../actions/action_enroll.php" method="post">
                <input type="hidden" name="class_id" value="<?= $gymClass->id ?>" />
                <input type="hidden" name="action" value="cancel" />
                <button type="submit" class="btn btn-danger"
                        onclick="return confirm('Cancelar inscrição nesta aula?')">
                    <i class="fas fa-times"></i> Cancelar Inscrição
                </button>
            </form>

        <?php elseif ($isOnWaitlist): ?>
            <p style="margin-bottom:1rem; color:var(--warning-color);">
                <i class="fas fa-hourglass-half"></i>
                Estás na lista de espera na posição <strong><?= $waitlistPosition ?></strong>.
            </p>
            <form action="../actions/action_enroll.php" method="post">
                <input type="hidden" name="class_id" value="<?= $gymClass->id ?>" />
                <input type="hidden" name="action" value="waitlist_leave" />
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt"></i> Sair da Lista de Espera
                </button>
            </form>

        <?php elseif ($gymClass->hasAvailableSpots()): ?>
            <p style="margin-bottom:1rem;">
                <i class="fas fa-circle" style="color:var(--success-color)"></i>
                <?= $gymClass->getAvailableSpots() ?> lugar(es) disponível(is)
            </p>
            <form action="../actions/action_enroll.php" method="post">
                <input type="hidden" name="class_id" value="<?= $gymClass->id ?>" />
                <input type="hidden" name="action" value="enroll" />
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Inscrever
                </button>
            </form>

        <?php else: ?>
            <p style="margin-bottom:1rem; color:var(--error-color);">
                <i class="fas fa-ban"></i> Aula cheia
            </p>
            <form action="../actions/action_enroll.php" method="post">
                <input type="hidden" name="class_id" value="<?= $gymClass->id ?>" />
                <input type="hidden" name="action" value="waitlist" />
                <button type="submit" class="btn btn-secondary">
                    <i class="fas fa-list"></i> Entrar na Lista de Espera
                </button>
            </form>
        <?php endif; ?>
    </section>
    <?php
}

function drawReviewsSection(array $reviews, array $stats, ?object $userReview, int $classId, bool $isEnrolled): void {
    ?>
    <section class="class-detail-section">
        <h2>Avaliações</h2>

        <?php if ($stats['total'] > 0): ?>
            <div style="margin-bottom:1.5rem;">
                <strong style="font-size:2rem;"><?= number_format($stats['average'], 1) ?></strong>
                <span style="color:var(--gray-color);"> / 5 (<?= $stats['total'] ?> avaliação<?= $stats['total'] !== 1 ? 'ões' : '' ?>)</span>
                <div style="color:#f59e0b;font-size:1.25rem;margin-top:.25rem;">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <?= $i <= round($stats['average']) ? '★' : '☆' ?>
                    <?php endfor; ?>
                </div>
            </div>
        <?php else: ?>
            <p style="color:var(--gray-color);margin-bottom:1.5rem;">Ainda não há avaliações para esta aula.</p>
        <?php endif; ?>

        <?php if ($isEnrolled): ?>
            <div class="profile-card" style="margin-bottom:1.5rem;">
                <h3 style="margin-bottom:1rem;">
                    <?= $userReview ? 'A tua avaliação' : 'Deixar avaliação' ?>
                </h3>
                <form action="../actions/action_review.php" method="post">
                    <input type="hidden" name="class_id" value="<?= $classId ?>" />
                    <div class="form-group">
                        <label>Classificação</label>
                        <div style="display:flex;gap:.5rem;">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <label style="cursor:pointer;font-size:1.5rem;color:#f59e0b;">
                                    <input type="radio" name="rating" value="<?= $i ?>" required
                                           style="display:none"
                                           <?= $userReview && $userReview->rating === $i ? 'checked' : '' ?> />
                                    <?= $i <= ($userReview->rating ?? 0) ? '★' : '☆' ?>
                                </label>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="comment">Comentário (opcional)</label>
                        <textarea id="comment" name="comment" rows="3"
                                  placeholder="Partilha a tua experiência..."><?= htmlspecialchars($userReview->comment ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-star"></i>
                        <?= $userReview ? 'Atualizar Avaliação' : 'Submeter Avaliação' ?>
                    </button>
                    <?php if ($userReview): ?>
                        <button type="submit" name="delete" value="1" class="btn btn-danger"
                                style="margin-left:.5rem"
                                onclick="return confirm('Eliminar avaliação?')">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    <?php endif; ?>
                </form>
            </div>
        <?php endif; ?>

        <?php foreach ($reviews as $review): ?>
            <div class="profile-card" style="margin-bottom:1rem;">
                <div style="display:flex;justify-content:space-between;margin-bottom:.5rem;">
                    <strong><?= htmlspecialchars($review->userName ?? 'Membro') ?></strong>
                    <span style="color:#f59e0b;">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <?= $i <= $review->rating ? '★' : '☆' ?>
                        <?php endfor; ?>
                    </span>
                </div>
                <?php if ($review->comment): ?>
                    <p style="color:var(--gray-color);"><?= htmlspecialchars($review->comment) ?></p>
                <?php endif; ?>
                <small style="color:var(--gray-color);">
                    <?= htmlspecialchars(date('d/m/Y', strtotime($review->createdAt))) ?>
                </small>
            </div>
        <?php endforeach; ?>
    </section>
    <?php
}
?>
