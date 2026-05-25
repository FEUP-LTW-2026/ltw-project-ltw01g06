<?php
declare(strict_types=1);

/**
 * Gera o horário semanal no formato calendar-grid + schedule-list
 * usando as classes CSS do schedule.html original.
 */
function drawScheduleTable(array $classes, Session $session, PDO $db): void {
    require_once(__DIR__ . '/../database/enrollment.class.php');
    require_once(__DIR__ . '/../database/waitlist.class.php');

    $userId = $session->isLoggedIn() ? $session->getId() : null;

    $days = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
    $dayLabels = ['Seg','Ter','Qua','Qui','Sex','Sáb','Dom'];
    $dayFull   = ['Segunda-feira','Terça-feira','Quarta-feira','Quinta-feira','Sexta-feira','Sábado','Domingo'];

    // Agrupar aulas por dia
    $byDay = array_fill_keys($days, []);
    foreach ($classes as $class) {
        $byDay[$class->dayOfWeek][] = $class;
    }

    // Recolher horas únicas ordenadas
    $times = [];
    foreach ($classes as $class) {
        $times[$class->startTime] = true;
    }
    ksort($times);
    $times = array_keys($times);
    ?>

    <!-- ── Filtros ──────────────────────────────────────────── -->
    <section class="schedule-filters">
        <div class="schedule-filters__row">
            <div class="filter-group">
                <span class="filter-group__label">Tipo de Aula</span>
                <select class="filter-select" id="filter-type">
                    <option value="">Todas as Aulas</option>
                    <?php
                    $types = array_unique(array_map(fn($c) => $c->type, $classes));
                    sort($types);
                    foreach ($types as $t): ?>
                        <option value="<?= htmlspecialchars(strtolower($t)) ?>"><?= htmlspecialchars($t) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <span class="filter-group__label">Nível</span>
                <div class="filter-chips">
                    <button class="filter-chip filter-chip--active" data-level="">Todos</button>
                    <button class="filter-chip" data-level="beginner">Iniciante</button>
                    <button class="filter-chip" data-level="intermediate">Intermédio</button>
                    <button class="filter-chip" data-level="advanced">Avançado</button>
                </div>
            </div>
        </div>
    </section>

    <!-- ── Vista Calendário (desktop) ───────────────────────── -->
    <section class="schedule-calendar" id="view-calendar">
        <header class="calendar-header">
            <h2 class="calendar-header__title">Horário Semanal</h2>
            <div class="calendar-view-toggle">
                <button class="calendar-view-btn calendar-view-btn--active" onclick="switchView('calendar')">Semana</button>
                <button class="calendar-view-btn" onclick="switchView('list')">Lista</button>
            </div>
        </header>

        <div class="calendar-grid">
            <!-- Header row -->
            <div class="calendar-grid__header">
                <div class="calendar-grid__day-header"></div>
                <?php foreach ($dayLabels as $label): ?>
                    <div class="calendar-grid__day-header">
                        <span class="calendar-grid__day-name"><?= $label ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Body rows por hora -->
            <div class="calendar-grid__body">
                <?php if (empty($times)): ?>
                    <div style="grid-column:1/-1;padding:2rem;text-align:center;color:var(--color-text-muted);">
                        Nenhuma aula disponível.
                    </div>
                <?php endif; ?>
                <?php foreach ($times as $time): ?>
                    <div class="calendar-grid__row">
                        <div class="calendar-grid__time-header"><?= htmlspecialchars($time) ?></div>
                        <?php foreach ($days as $day): ?>
                            <div class="calendar-grid__cell">
                                <?php foreach ($byDay[$day] as $class):
                                    if ($class->startTime !== $time) continue;
                                    $typeClass = 'class-card--' . strtolower($class->type);
                                    $isFull = !$class->hasAvailableSpots();
                                    $spotsClass = $isFull ? 'class-card__spots--full'
                                        : ($class->getAvailableSpots() <= 3 ? 'class-card__spots--low' : '');
                                ?>
                                    <a href="../pages/class-detail.php?id=<?= $class->id ?>"
                                       class="class-card <?= $typeClass ?>"
                                       style="display:flex;flex-direction:column;text-decoration:none;"
                                       data-type="<?= htmlspecialchars(strtolower($class->type)) ?>"
                                       data-level="<?= htmlspecialchars($class->level) ?>">
                                        <span class="class-card__time">
                                            <?= htmlspecialchars($class->startTime) ?> – <?= htmlspecialchars($class->getEndTime()) ?>
                                        </span>
                                        <span class="class-card__name"><?= htmlspecialchars($class->name) ?></span>
                                        <span class="class-card__trainer"><?= htmlspecialchars($class->trainerName ?? '—') ?></span>
                                        <span class="class-card__spots <?= $spotsClass ?>">
                                            <?= $class->currentEnrollment ?>/<?= $class->capacity ?> vagas
                                        </span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ── Vista Lista (mobile / toggle) ────────────────────── -->
    <section class="schedule-list" id="view-list" style="display:none;">
        <?php foreach ($days as $i => $day):
            if (empty($byDay[$day])) continue;
            usort($byDay[$day], fn($a, $b) => strcmp($a->startTime, $b->startTime));
        ?>
            <article class="schedule-list__day" data-day="<?= $day ?>">
                <header class="schedule-list__day-header">
                    <h3 class="schedule-list__day-title"><?= $dayFull[$i] ?></h3>
                </header>
                <div class="schedule-list__classes">
                    <?php foreach ($byDay[$day] as $class):
                        $isFull   = !$class->hasAvailableSpots();
                        $spots    = $class->getAvailableSpots();
                        $pct      = $class->capacity > 0
                            ? round($class->currentEnrollment / $class->capacity * 100) : 0;
                        $fillClass = $isFull ? 'spots-indicator__fill--full'
                            : ($spots <= 3 ? 'spots-indicator__fill--low' : '');

                        $isEnrolled   = $userId ? Enrollment::isUserEnrolled($db, $userId, $class->id) : false;
                        $waitlistPos  = $userId ? Waitlist::getUserPosition($db, $userId, $class->id) : null;
                    ?>
                        <div class="class-list-item"
                             data-type="<?= htmlspecialchars(strtolower($class->type)) ?>"
                             data-level="<?= htmlspecialchars($class->level) ?>">

                            <div class="class-list-item__time">
                                <span class="class-list-item__time-start"><?= htmlspecialchars($class->startTime) ?></span><br>
                                <span class="class-list-item__time-end"><?= htmlspecialchars($class->getEndTime()) ?></span>
                            </div>

                            <div class="class-list-item__info">
                                <h4 class="class-list-item__name">
                                    <a href="../pages/class-detail.php?id=<?= $class->id ?>" style="color:inherit;">
                                        <?= htmlspecialchars($class->name) ?>
                                    </a>
                                    <span class="tag tag--<?= htmlspecialchars(strtolower($class->type)) ?>">
                                        <?= htmlspecialchars($class->type) ?>
                                    </span>
                                    <span class="tag tag--<?= htmlspecialchars($class->level) ?>">
                                        <?= match($class->level) {
                                            'beginner'     => 'Iniciante',
                                            'intermediate' => 'Intermédio',
                                            'advanced'     => 'Avançado',
                                            default        => ucfirst($class->level)
                                        } ?>
                                    </span>
                                </h4>
                                <div class="class-list-item__meta">
                                    <?php if ($class->trainerName): ?>
                                    <span>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                            <circle cx="12" cy="7" r="4"/>
                                        </svg>
                                        <?= htmlspecialchars($class->trainerName) ?>
                                    </span>
                                    <?php endif; ?>
                                    <span>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>
                                        </svg>
                                        <?= $class->durationMin ?> min
                                    </span>
                                </div>
                            </div>

                            <div class="class-list-item__spots">
                                <div class="spots-indicator">
                                    <div class="spots-indicator__bar">
                                        <div class="spots-indicator__fill <?= $fillClass ?>"
                                             style="width:<?= $pct ?>%"></div>
                                    </div>
                                    <span class="spots-indicator__text">
                                        <?= $class->currentEnrollment ?>/<?= $class->capacity ?> vagas
                                    </span>
                                </div>

                                <?php if ($isEnrolled): ?>
                                    <form method="post" action="../actions/action_enroll.php">
                                        <input type="hidden" name="class_id" value="<?= $class->id ?>">
                                        <input type="hidden" name="action" value="cancel">
                                        <button type="submit" class="btn btn--secondary"
                                                onclick="return confirm('Cancelar inscrição?')">
                                            Cancelar
                                        </button>
                                    </form>
                                <?php elseif ($waitlistPos !== null): ?>
                                    <span style="font-size:.8rem;color:var(--color-text-muted);">
                                        Lista de espera #<?= $waitlistPos ?>
                                    </span>
                                <?php elseif (!$isFull): ?>
                                    <form method="post" action="../actions/action_enroll.php">
                                        <input type="hidden" name="class_id" value="<?= $class->id ?>">
                                        <input type="hidden" name="action" value="enroll">
                                        <button type="submit" class="btn btn--primary">Inscrever</button>
                                    </form>
                                <?php else: ?>
                                    <form method="post" action="../actions/action_enroll.php">
                                        <input type="hidden" name="class_id" value="<?= $class->id ?>">
                                        <input type="hidden" name="action" value="waitlist">
                                        <button type="submit" class="btn btn--ghost">Lista de Espera</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </article>
        <?php endforeach; ?>

        <?php if (array_sum(array_map('count', $byDay)) === 0): ?>
            <p style="text-align:center;color:var(--color-text-muted);padding:3rem;">
                Nenhuma aula disponível.
            </p>
        <?php endif; ?>
    </section>

    <?php
}