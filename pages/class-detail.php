<?php
declare(strict_types=1);

/**
 * class-detail.tpl.php
 * Funções de renderização da página de detalhe de aula.
 */

function drawClassHeader(object $gymClass): void {
    $typeSlug = strtolower(str_replace(' ', '-', $gymClass->type));
    $levelLabel = match($gymClass->level) {
        'beginner'     => 'Iniciante',
        'intermediate' => 'Intermédio',
        'advanced'     => 'Avançado',
        default        => ucfirst($gymClass->level)
    };
    $dayLabels = [
        'monday' => 'Segunda', 'tuesday' => 'Terça', 'wednesday' => 'Quarta',
        'thursday' => 'Quinta', 'friday' => 'Sexta', 'saturday' => 'Sábado', 'sunday' => 'Domingo'
    ];
    $day = $dayLabels[$gymClass->dayOfWeek] ?? ucfirst($gymClass->dayOfWeek);
    ?>
    <article class="class-header">
        <div class="class-header__banner">
            <div class="class-header__banner-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M6 4v6M6 14v6M18 4v6M18 14v6M3 7h6M15 7h6M3 17h6M15 17h6"/>
                </svg>
            </div>
        </div>
        <div class="class-header__content">
            <div class="class-header__tags">
                <span class="tag tag--<?= $typeSlug ?>"><?= htmlspecialchars($gymClass->type) ?></span>
                <span class="tag tag--<?= htmlspecialchars($gymClass->level) ?>"><?= $levelLabel ?></span>
            </div>
            <h1 class="class-header__title"><?= htmlspecialchars($gymClass->name) ?></h1>
            <?php if ($gymClass->description): ?>
                <p class="class-header__description"><?= htmlspecialchars($gymClass->description) ?></p>
            <?php endif; ?>
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
<?php }

function drawEnrollmentSection(object $gymClass, bool $isEnrolled, bool $isOnWaitlist, ?int $waitlistPos): void {
    $pct = $gymClass->capacity > 0
        ? round($gymClass->currentEnrollment / $gymClass->capacity * 100) : 0;
    ?>
    <section class="enrollment-section">
        <header class="enrollment-section__header">
            <h2 class="enrollment-section__title">Inscrição</h2>
            <div class="enrollment-section__spots">
                <div class="spots-progress">
                    <div class="spots-progress__fill" style="width:<?= $pct ?>%"></div>
                </div>
                <span class="spots-text">
                    <strong><?= $gymClass->currentEnrollment ?></strong>
                    de <?= $gymClass->capacity ?> vagas ocupadas
                </span>
            </div>
        </header>

        <?php if ($isEnrolled): ?>
            <div class="enrollment-actions">
                <form method="post" action="../actions/action_enroll.php">
                    <input type="hidden" name="class_id" value="<?= $gymClass->id ?>">
                    <input type="hidden" name="action" value="cancel">
                    <button type="submit" class="btn btn--secondary btn--large"
                            onclick="return confirm('Cancelar inscrição nesta aula?')">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                            <path d="M18 6L6 18M6 6l12 12"/>
                        </svg>
                        Cancelar Inscrição
                    </button>
                </form>
            </div>

        <?php elseif ($isOnWaitlist): ?>
            <div class="waitlist-notice">
                <div class="waitlist-notice__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M12 6v6l4 2"/>
                    </svg>
                </div>
                <div class="waitlist-notice__content">
                    <span class="waitlist-notice__title">Na Lista de Espera</span>
                    <span class="waitlist-notice__text">Estás na posição <?= $waitlistPos ?>. Serás notificado se uma vaga abrir.</span>
                </div>
                <form method="post" action="../actions/action_enroll.php">
                    <input type="hidden" name="class_id" value="<?= $gymClass->id ?>">
                    <input type="hidden" name="action" value="waitlist_leave">
                    <button type="submit" class="btn btn--secondary">Sair da Lista</button>
                </form>
            </div>

        <?php elseif ($gymClass->hasAvailableSpots()): ?>
            <div class="enrollment-actions">
                <form method="post" action="../actions/action_enroll.php">
                    <input type="hidden" name="class_id" value="<?= $gymClass->id ?>">
                    <input type="hidden" name="action" value="enroll">
                    <button type="submit" class="btn btn--primary btn--large">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                            <path d="M22 4L12 14.01l-3-3"/>
                        </svg>
                        Inscrever-me nesta Aula
                    </button>
                </form>
            </div>

        <?php else: ?>
            <div class="waitlist-notice">
                <div class="waitlist-notice__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M12 6v6l4 2"/>
                    </svg>
                </div>
                <div class="waitlist-notice__content">
                    <span class="waitlist-notice__title">Aula Completa</span>
                    <span class="waitlist-notice__text">Pode juntar-se à lista de espera. Será notificado se uma vaga abrir.</span>
                </div>
                <form method="post" action="../actions/action_enroll.php">
                    <input type="hidden" name="class_id" value="<?= $gymClass->id ?>">
                    <input type="hidden" name="action" value="waitlist">
                    <button type="submit" class="btn btn--secondary">Juntar à Lista de Espera</button>
                </form>
            </div>
        <?php endif; ?>
    </section>
<?php }

function drawReviewsSection(array $reviews, array $stats, ?object $userReview, int $classId, bool $isEnrolled): void {
    $starSvg = '<svg viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>';

    // Distribuição por estrelas
    $dist = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
    foreach ($reviews as $r) {
        $dist[$r->rating] = ($dist[$r->rating] ?? 0) + 1;
    }
    ?>
    <section class="reviews-section">
        <header class="reviews-header">
            <div class="reviews-header__info">
                <h2>Avaliações e Reviews</h2>
                <span class="reviews-header__count"><?= $stats['total'] ?> avaliações</span>
            </div>
        </header>

        <?php if ($stats['total'] > 0): ?>
            <div class="rating-summary">
                <div class="rating-summary__score">
                    <span class="rating-summary__number"><?= number_format($stats['average'], 1) ?></span>
                    <div class="rating-summary__stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <?= $starSvg ?>
                        <?php endfor; ?>
                    </div>
                    <span class="rating-summary__total"><?= $stats['total'] ?> avaliações</span>
                </div>
                <div class="rating-bars">
                    <?php foreach ([5, 4, 3, 2, 1] as $star):
                        $count = $dist[$star] ?? 0;
                        $pct   = $stats['total'] > 0 ? round($count / $stats['total'] * 100) : 0;
                    ?>
                        <div class="rating-bar">
                            <span class="rating-bar__label"><?= $star ?> estrelas</span>
                            <div class="rating-bar__track">
                                <div class="rating-bar__fill" style="width:<?= $pct ?>%"></div>
                            </div>
                            <span class="rating-bar__count"><?= $count ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($isEnrolled): ?>
            <form class="review-form" method="post" action="../actions/action_review.php">
                <input type="hidden" name="class_id" value="<?= $classId ?>">
                <h3 class="review-form__title">
                    <?= $userReview ? 'Atualizar a sua avaliação' : 'Deixe a sua avaliação' ?>
                </h3>
                <div class="review-form__rating">
                    <span class="review-form__rating-label">A sua avaliação:</span>
                    <div class="star-rating">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <label class="star-rating__btn" style="cursor:pointer;">
                                <input type="radio" name="rating" value="<?= $i ?>"
                                       style="display:none"
                                       <?= $userReview && $userReview->rating === $i ? 'checked' : '' ?> required />
                                <?= $starSvg ?>
                            </label>
                        <?php endfor; ?>
                    </div>
                </div>
                <textarea class="review-form__textarea" name="comment"
                          placeholder="Partilhe a sua experiência com esta aula..."><?= htmlspecialchars($userReview->comment ?? '') ?></textarea>
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

        <?php if (!empty($reviews)): ?>
            <div class="reviews-list">
                <?php foreach ($reviews as $review):
                    $parts    = explode(' ', $review->userName ?? '');
                    $initials = mb_substr(
                        implode('', array_map(fn($p) => strtoupper(mb_substr($p, 0, 1)), $parts)),
                        0, 2
                    );
                ?>
                    <article class="review-card">
                        <header class="review-card__header">
                            <div class="review-card__avatar"><?= htmlspecialchars($initials) ?></div>
                            <div class="review-card__info">
                                <span class="review-card__name"><?= htmlspecialchars($review->userName ?? 'Membro') ?></span>
                                <span class="review-card__date">
                                    <?= htmlspecialchars(date('d M Y', strtotime($review->createdAt))) ?>
                                </span>
                            </div>
                            <div class="review-card__rating">
                                <?php for ($i = 1; $i <= $review->rating; $i++): ?>
                                    <?= $starSvg ?>
                                <?php endfor; ?>
                            </div>
                        </header>
                        <?php if ($review->comment): ?>
                            <p class="review-card__content"><?= htmlspecialchars($review->comment) ?></p>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php elseif ($stats['total'] === 0): ?>
            <p style="color:var(--color-text-muted);text-align:center;padding:2rem 0;">
                Ainda não há avaliações para esta aula. Sê o primeiro!
            </p>
        <?php endif; ?>
    </section>
<?php }