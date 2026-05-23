<?php
declare(strict_types=1);

function drawEnrollmentsByDayChart(array $enrollmentsByDay, array $dayOrder, int $maxVal): void {
    ?>
    <div class="chart-card chart-card--full">
        <div class="chart-card__header">
            <div>
                <h3 class="chart-card__title">Inscrições por Dia da Semana</h3>
                <p class="chart-card__subtitle">Número de participantes inscritos em aulas por dia</p>
            </div>
        </div>
        <div class="bar-chart">
            <?php foreach ($enrollmentsByDay as $day => $count):
                $percent = $maxVal > 0 ? round(($count / $maxVal) * 100) : 0;
                $label   = $dayOrder[$day];
            ?>
                <div class="bar-chart__bar" style="height: <?= max($percent, 4) ?>%">
                    <span class="bar-chart__value"><?= $count ?></span>
                    <span class="bar-chart__label"><?= $label ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}

function drawClassTypeDonut(array $typeStats, int $total): void {
    // Paleta de cores para os tipos
    $colors = [
        '#6366f1', // primary (índigo)
        '#16a34a', // verde
        '#2563eb', // azul
        '#d97706', // âmbar
        '#dc2626', // vermelho
        '#9333ea', // roxo
        '#0891b2', // ciano
    ];
    $colorIndex = 0;
    ?>
    <div class="chart-card">
        <div class="chart-card__header">
            <div>
                <h3 class="chart-card__title">Popularidade das Aulas</h3>
                <p class="chart-card__subtitle">Distribuição por tipo</p>
            </div>
        </div>
        <div class="donut-chart">
            <div class="donut-chart__visual">
                <div class="donut-chart__center">
                    <span class="donut-chart__center-value"><?= number_format($total) ?></span>
                    <span class="donut-chart__center-label">Total</span>
                </div>
            </div>
            <div class="donut-chart__legend">
                <?php foreach ($typeStats as $type => $count):
                    $percent = $total > 0 ? round(($count / $total) * 100) : 0;
                    $color   = $colors[$colorIndex % count($colors)];
                    $colorIndex++;
                ?>
                    <div class="legend-row">
                        <span class="legend-row__color" style="background: <?= $color ?>"></span>
                        <span class="legend-row__label"><?= htmlspecialchars(ucfirst($type)) ?></span>
                        <span class="legend-row__value"><?= $count ?></span>
                        <span class="legend-row__percent"><?= $percent ?>%</span>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($typeStats)): ?>
                    <p style="color: var(--color-text-muted); font-size: 0.875rem;">Sem dados disponíveis.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
}

function drawKeyMetrics(int $activeMembers, int $totalMembers, float $avgEnrollments): void {
    $retentionRate = $totalMembers > 0 ? round(($activeMembers / $totalMembers) * 100) : 0;
    ?>
    <div class="chart-card">
        <div class="chart-card__header">
            <div>
                <h3 class="chart-card__title">Métricas Chave</h3>
                <p class="chart-card__subtitle">Dados atuais do ginásio</p>
            </div>
        </div>
        <div class="metrics-grid">
            <div class="metric-card">
                <span class="metric-card__label">Membros Ativos</span>
                <span class="metric-card__value"><?= $activeMembers ?></span>
                <span class="metric-card__change metric-card__change--up">
                    de <?= $totalMembers ?> registados
                </span>
            </div>
            <div class="metric-card">
                <span class="metric-card__label">Taxa de Atividade</span>
                <span class="metric-card__value"><?= $retentionRate ?>%</span>
                <span class="metric-card__change <?= $retentionRate >= 70 ? 'metric-card__change--up' : 'metric-card__change--down' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <?= $retentionRate >= 70
                            ? '<path d="M18 15l-6-6-6 6"/>'
                            : '<path d="M6 9l6 6 6-6"/>' ?>
                    </svg>
                    membros com conta ativa
                </span>
            </div>
            <div class="metric-card">
                <span class="metric-card__label">Média Inscrições/Membro</span>
                <span class="metric-card__value"><?= number_format($avgEnrollments, 1) ?></span>
                <span class="metric-card__change metric-card__change--up">
                    aulas por membro
                </span>
            </div>
            <div class="metric-card">
                <span class="metric-card__label">Total de Membros</span>
                <span class="metric-card__value"><?= $totalMembers ?></span>
                <span class="metric-card__change metric-card__change--up">
                    utilizadores registados
                </span>
            </div>
        </div>
    </div>
    <?php
}

function drawTopClasses(array $topClasses): void {
    ?>
    <div class="chart-card">
        <div class="chart-card__header">
            <div>
                <h3 class="chart-card__title">Aulas Mais Populares</h3>
                <p class="chart-card__subtitle">Por número de inscrições ativas</p>
            </div>
        </div>
        <div class="top-list">
            <?php if (empty($topClasses)): ?>
                <p style="color: var(--color-text-muted); font-size: 0.875rem;">Sem aulas disponíveis.</p>
            <?php else: ?>
                <?php foreach ($topClasses as $i => $class):
                    $rank    = $i + 1;
                    $rankMod = $rank <= 3 ? " top-list__rank--{$rank}" : '';
                    $dayLabels = [
                        'monday'=>'Seg','tuesday'=>'Ter','wednesday'=>'Qua',
                        'thursday'=>'Qui','friday'=>'Sex','saturday'=>'Sáb','sunday'=>'Dom'
                    ];
                    $dayLabel = $dayLabels[$class->dayOfWeek] ?? ucfirst($class->dayOfWeek);
                ?>
                    <div class="top-list__item">
                        <span class="top-list__rank<?= $rankMod ?>"><?= $rank ?></span>
                        <div class="top-list__info">
                            <span class="top-list__name"><?= htmlspecialchars($class->name) ?></span>
                            <span class="top-list__meta">
                                <?= htmlspecialchars($class->trainerName ?? 'A definir') ?>
                                — <?= $dayLabel ?>
                                <?= htmlspecialchars($class->startTime) ?>
                            </span>
                        </div>
                        <span class="top-list__value"><?= $class->currentEnrollment ?> inscritos</span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

function drawTopTrainers(array $topTrainers): void {
    ?>
    <div class="chart-card">
        <div class="chart-card__header">
            <div>
                <h3 class="chart-card__title">Treinadores Destaque</h3>
                <p class="chart-card__subtitle">Por avaliação média das aulas</p>
            </div>
        </div>
        <div class="top-list">
            <?php if (empty($topTrainers)): ?>
                <p style="color: var(--color-text-muted); font-size: 0.875rem;">Sem dados de avaliações disponíveis.</p>
            <?php else: ?>
                <?php foreach ($topTrainers as $i => $entry):
                    $rank    = $i + 1;
                    $rankMod = $rank <= 3 ? " top-list__rank--{$rank}" : '';
                    $trainer = $entry['trainer'];
                    $rating  = $entry['avgRating'];
                    $types   = $entry['types'];
                ?>
                    <div class="top-list__item">
                        <span class="top-list__rank<?= $rankMod ?>"><?= $rank ?></span>
                        <div class="top-list__info">
                            <span class="top-list__name"><?= htmlspecialchars($trainer->fullName) ?></span>
                            <span class="top-list__meta">
                                <?= htmlspecialchars($types ?: 'Sem aulas atribuídas') ?>
                            </span>
                        </div>
                        <span class="top-list__value">
                            <?= $rating > 0 ? number_format($rating, 1) . ' ★' : 'Sem reviews' ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <?php
}
?>