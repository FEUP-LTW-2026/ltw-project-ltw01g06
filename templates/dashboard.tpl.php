<?php
declare(strict_types=1);

function drawDashboardStats(int $totalEnrollments, object $user): void {
    $firstName = explode(' ', $user->fullName)[0];
    ?>
    <section class="dashboard-stats">

        <div class="dashboard-stat">
            <div class="dashboard-stat__icon dashboard-stat__icon--classes">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2"/>
                    <path d="M16 2v4M8 2v4M3 10h18"/>
                </svg>
            </div>
            <div class="dashboard-stat__info">
                <span class="dashboard-stat__value"><?= $totalEnrollments ?></span>
                <span class="dashboard-stat__label">Aulas inscritas</span>
            </div>
        </div>

        <div class="dashboard-stat">
            <div class="dashboard-stat__icon dashboard-stat__icon--attendance">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <path d="M22 4L12 14.01l-3-3"/>
                </svg>
            </div>
            <div class="dashboard-stat__info">
                <span class="dashboard-stat__value"><?= htmlspecialchars(ucfirst($user->role)) ?></span>
                <span class="dashboard-stat__label">Tipo de conta</span>
            </div>
        </div>

        <div class="dashboard-stat">
            <div class="dashboard-stat__icon dashboard-stat__icon--streak">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
                </svg>
            </div>
            <div class="dashboard-stat__info">
                <span class="dashboard-stat__value"><?= $user->isActive ? 'Ativa' : 'Inativa' ?></span>
                <span class="dashboard-stat__label">Estado</span>
            </div>
        </div>

        <div class="dashboard-stat">
            <div class="dashboard-stat__icon dashboard-stat__icon--points">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"/>
                </svg>
            </div>
            <div class="dashboard-stat__info">
                <span class="dashboard-stat__value"><?= htmlspecialchars(date('d/m/Y', strtotime($user->createdAt))) ?></span>
                <span class="dashboard-stat__label">Membro desde</span>
            </div>
        </div>

    </section>
    <?php
}

function drawUpcomingClass(GymClass $gymClass, int $userId, PDO $db): void {
    $dayLabels = [
        'monday'    => 'Seg',
        'tuesday'   => 'Ter',
        'wednesday' => 'Qua',
        'thursday'  => 'Qui',
        'friday'    => 'Sex',
        'saturday'  => 'Sáb',
        'sunday'    => 'Dom',
    ];
    $dayLabel = $dayLabels[$gymClass->dayOfWeek] ?? ucfirst($gymClass->dayOfWeek);
    $endTime  = $gymClass->getEndTime();
    $typeTag  = htmlspecialchars(strtolower($gymClass->type));
    ?>
    <article class="upcoming-class">
        <div class="upcoming-class__date">
            <span class="upcoming-class__day"><?= $dayLabel ?></span>
            <span class="upcoming-class__month"><?= htmlspecialchars($gymClass->startTime) ?></span>
        </div>
        <div class="upcoming-class__info">
            <h3 class="upcoming-class__name">
                <?= htmlspecialchars($gymClass->name) ?>
                <span class="tag tag--<?= $typeTag ?>"><?= htmlspecialchars($gymClass->type) ?></span>
            </h3>
            <div class="upcoming-class__meta">
                <span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>
                    </svg>
                    <?= htmlspecialchars($gymClass->startTime) ?> - <?= htmlspecialchars($endTime) ?>
                </span>
                <?php if ($gymClass->trainerName): ?>
                <span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                    <?= htmlspecialchars($gymClass->trainerName) ?>
                </span>
                <?php endif; ?>
            </div>
        </div>
        <div class="upcoming-class__action">
            <form method="post" action="cancel_enrollment.php">
                <input type="hidden" name="class_id" value="<?= $gymClass->id ?>">
                <button type="submit" class="btn btn--secondary">Cancelar</button>
            </form>
        </div>
    </article>
    <?php
}

function drawActivityItem(Enrollment $enrollment): void {
    $timeAgo = formatTimeAgo($enrollment->enrolledAt);
    ?>
    <div class="activity-item">
        <div class="activity-item__icon activity-item__icon--class">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                <path d="M22 4L12 14.01l-3-3"/>
            </svg>
        </div>
        <div class="activity-item__content">
            <p class="activity-item__text">
                Inscreveu-se em <strong><?= htmlspecialchars($enrollment->className ?? 'Aula') ?></strong>
            </p>
            <span class="activity-item__time"><?= $timeAgo ?></span>
        </div>
    </div>
    <?php
}

function formatTimeAgo(string $datetime): string {
    $then = new DateTime($datetime);
    $now  = new DateTime();
    $diff = $now->diff($then);

    if ($diff->days === 0) {
        return 'hoje';
    } elseif ($diff->days === 1) {
        return 'ontem';
    } elseif ($diff->days < 7) {
        return 'há ' . $diff->days . ' dias';
    } elseif ($diff->days < 30) {
        $weeks = (int)($diff->days / 7);
        return 'há ' . $weeks . ' semana' . ($weeks > 1 ? 's' : '');
    } else {
        return $then->format('d/m/Y');
    }
}
?>