<?php
declare(strict_types=1);
require_once(__DIR__ . '/../utils/session.php');
require_once(__DIR__ . '/../database/connection.db.php');
require_once(__DIR__ . '/../database/user.class.php');
require_once(__DIR__ . '/../database/class.class.php');
require_once(__DIR__ . '/../database/equipment.class.php');
require_once(__DIR__ . '/../database/review.class.php');
require_once(__DIR__ . '/../templates/common.tpl.php');
require_once(__DIR__ . '/../templates/admin.tpl.php');

$session = new Session();
if (!$session->isLoggedIn() || !$session->isAdmin()) { header('Location: login.php'); exit; }

$db = getDatabaseConnection();

$counts = [
    'members'   => count(User::getAllUsers($db)),
    'trainers'  => count(User::getAllUsers($db, 'trainer')),
    'classes'   => count(GymClass::getAllClasses($db)),
    'equipment' => count(Equipment::getAll($db)),
];

$days      = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
$dayLabels = ['Seg','Ter','Qua','Qui','Sex','Sáb','Dom'];
$byDay = [];
foreach ($days as $day) {
    $cls = GymClass::getClassesByDay($db, $day);
    $byDay[] = array_sum(array_map(fn($c) => $c->currentEnrollment, $cls));
}
$maxDay = max($byDay ?: [1]);

$allClasses = GymClass::getAllClasses($db);
usort($allClasses, fn($a,$b) => $b->currentEnrollment - $a->currentEnrollment);
$topClasses = array_slice($allClasses, 0, 5);

$typeDistrib = [];
foreach ($allClasses as $c) {
    $typeDistrib[$c->type] = ($typeDistrib[$c->type] ?? 0) + $c->currentEnrollment;
}
arsort($typeDistrib);
$totalDistrib = array_sum($typeDistrib) ?: 1;

$totalEnrollments = array_sum($byDay);
$nClasses         = count($allClasses) ?: 1;
$avgPerClass      = round($totalEnrollments / $nClasses, 1);
$maxCap           = array_sum(array_map(fn($c) => $c->capacity, $allClasses)) ?: 1;
$occupancy        = round($totalEnrollments / $maxCap * 100);

$trainers = User::getAllUsers($db, 'trainer');
$trainerRatings = [];
foreach ($trainers as $t) {
    $tClasses = GymClass::getClassesByTrainer($db, $t->id);
    $totalR = 0; $totalN = 0;
    foreach ($tClasses as $tc) {
        $s = Review::getClassStats($db, $tc->id);
        if ($s['total'] > 0) { $totalR += $s['average'] * $s['total']; $totalN += $s['total']; }
    }
    $trainerRatings[] = ['name' => $t->fullName, 'rating' => $totalN > 0 ? round($totalR/$totalN,1) : 0, 'classes' => count($tClasses)];
}
usort($trainerRatings, fn($a,$b) => $b['rating'] <=> $a['rating']);

$typeColors = ['Yoga'=>'var(--color-primary)','Spinning'=>'#16a34a','HIIT'=>'#2563eb','Pilates'=>'#d97706','Weights'=>'#7c3aed','Zumba'=>'#db2777'];

drawHeader($session, 'Analytics', '', 'css/pages/admin.css');
?>
<link rel="stylesheet" href="../css/pages/analytics.css" />
<?php
?>
<div class="admin-layout">
    <?php drawAdminSidebar('analytics', $counts); ?>
    <main class="admin-content">
        <header class="admin-header">
            <div class="admin-header__info"><h1>Analytics</h1><p>Métricas e insights do ginásio</p></div>
        </header>

        <section class="analytics-grid">

            <div class="chart-card chart-card--full">
                <div class="chart-card__header">
                    <div><h3 class="chart-card__title">Inscrições por Dia da Semana</h3>
                    <p class="chart-card__subtitle">Total de inscrições ativas por dia</p></div>
                </div>
                <div class="bar-chart">
                    <?php foreach ($byDay as $i => $count):
                        $h = $maxDay > 0 ? max(5, round($count/$maxDay*100)) : 5; ?>
                        <div class="bar-chart__bar" style="height:<?= $h ?>%">
                            <span class="bar-chart__value"><?= $count ?></span>
                            <span class="bar-chart__label"><?= $dayLabels[$i] ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="chart-card">
                <div class="chart-card__header">
                    <div><h3 class="chart-card__title">Popularidade por Tipo</h3>
                    <p class="chart-card__subtitle">Distribuição de inscrições</p></div>
                </div>
                <div class="donut-chart">
                    <div class="donut-chart__visual">
                        <div class="donut-chart__center">
                            <span class="donut-chart__center-value"><?= $totalDistrib ?></span>
                            <span class="donut-chart__center-label">Total</span>
                        </div>
                    </div>
                    <div class="donut-chart__legend">
                        <?php foreach ($typeDistrib as $type => $count):
                            $pct = round($count/$totalDistrib*100);
                            $color = $typeColors[$type] ?? '#888'; ?>
                            <div class="legend-row">
                                <span class="legend-row__color" style="background:<?= $color ?>"></span>
                                <span class="legend-row__label"><?= htmlspecialchars($type) ?></span>
                                <span class="legend-row__value"><?= $count ?></span>
                                <span class="legend-row__percent"><?= $pct ?>%</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="chart-card">
                <div class="chart-card__header">
                    <div><h3 class="chart-card__title">Métricas Chave</h3></div>
                </div>
                <div class="metrics-grid">
                    <div class="metric-card">
                        <span class="metric-card__label">Total Inscrições</span>
                        <span class="metric-card__value"><?= $totalEnrollments ?></span>
                    </div>
                    <div class="metric-card">
                        <span class="metric-card__label">Média por Aula</span>
                        <span class="metric-card__value"><?= $avgPerClass ?></span>
                    </div>
                    <div class="metric-card">
                        <span class="metric-card__label">Ocupação</span>
                        <span class="metric-card__value"><?= $occupancy ?>%</span>
                    </div>
                    <div class="metric-card">
                        <span class="metric-card__label">Tipos de Aulas</span>
                        <span class="metric-card__value"><?= count($typeDistrib) ?></span>
                    </div>
                </div>
            </div>

            <div class="chart-card">
                <div class="chart-card__header">
                    <div><h3 class="chart-card__title">Aulas Mais Populares</h3></div>
                </div>
                <div class="top-list">
                    <?php foreach ($topClasses as $i => $c):
                        $rankClass = $i < 3 ? 'top-list__rank--'.($i+1) : ''; ?>
                        <div class="top-list__item">
                            <span class="top-list__rank <?= $rankClass ?>"><?= $i+1 ?></span>
                            <div class="top-list__info">
                                <span class="top-list__name"><?= htmlspecialchars($c->name) ?></span>
                                <span class="top-list__meta"><?= htmlspecialchars($c->trainerName ?? '—') ?></span>
                            </div>
                            <span class="top-list__value"><?= $c->currentEnrollment ?> inscritos</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="chart-card">
                <div class="chart-card__header">
                    <div><h3 class="chart-card__title">Treinadores em Destaque</h3></div>
                </div>
                <div class="top-list">
                    <?php foreach (array_slice($trainerRatings, 0, 5) as $i => $t):
                        $rankClass = $i < 3 ? 'top-list__rank--'.($i+1) : ''; ?>
                        <div class="top-list__item">
                            <span class="top-list__rank <?= $rankClass ?>"><?= $i+1 ?></span>
                            <div class="top-list__info">
                                <span class="top-list__name"><?= htmlspecialchars($t['name']) ?></span>
                                <span class="top-list__meta"><?= $t['classes'] ?> aulas</span>
                            </div>
                            <span class="top-list__value"><?= $t['rating'] > 0 ? $t['rating'].' ★' : '—' ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </section>
    </main>
</div>
<?php drawFooter(); ?>