<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/session.php');
require_once(__DIR__ . '/../database/connection.db.php');
require_once(__DIR__ . '/../database/equipment.class.php');
require_once(__DIR__ . '/../templates/common.tpl.php');

$session   = new Session();
if (!$session->isLoggedIn()) {
    $session->addMessage('error', 'Precisas de estar autenticado.');
    header('Location: login.php');
    exit;
}
$db        = getDatabaseConnection();
$equipment = Equipment::getAll($db);

$totalAvailable   = 0;
$totalInUse       = 0;
$totalMaintenance = 0;
$totalQty         = 0;

foreach ($equipment as $item) {
    $totalAvailable   += $item->available;
    $totalInUse       += max(0, $item->quantity - $item->available);
    $totalMaintenance += ($item->status === 'maintenance' ? 1 : 0);
    $totalQty         += $item->quantity;
}

$categories = array_values(array_unique(array_filter(array_map(fn($e) => $e->category, $equipment))));
sort($categories);

drawHeader($session, 'Equipamento', 'javascript/equipment.js', 'css/pages/equipment.css');
?>

<main class="equipment-container">

    <header class="equipment-header">
        <div class="equipment-header__info">
            <h1>Equipamento Disponível</h1>
            <p>Verifique a disponibilidade do equipamento na área de musculação</p>
        </div>
        <a href="equipment.php" class="btn btn--secondary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                 width="18" height="18">
                <path d="M23 4v6h-6M1 20v-6h6"/>
                <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>
            </svg>
            Atualizar
        </a>
    </header>

    <!-- Resumo -->
    <section class="equipment-summary">
        <div class="summary-card">
            <div class="summary-card__icon summary-card__icon--available">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <path d="M22 4L12 14.01l-3-3"/>
                </svg>
            </div>
            <div class="summary-card__info">
                <span class="summary-card__value"><?= $totalAvailable ?></span>
                <span class="summary-card__label">Disponíveis</span>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-card__icon summary-card__icon--occupied">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 6v6l4 2"/>
                </svg>
            </div>
            <div class="summary-card__info">
                <span class="summary-card__value"><?= $totalInUse ?></span>
                <span class="summary-card__label">Em Uso</span>
            </div>
        </div>
        <div class="summary-card summary-card--alert <?= $totalMaintenance > 0 ? 'summary-card--has-issues' : '' ?>">
            <div class="summary-card__icon summary-card__icon--maintenance">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0
                             l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91
                             a2.12 2.12 0 0 1-3-3l6.91-6.91
                             a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                </svg>
            </div>
            <div class="summary-card__info">
                <span class="summary-card__value"><?= $totalMaintenance ?></span>
                <span class="summary-card__label">Em Manutenção</span>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-card__icon summary-card__icon--total">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M6 4v6M6 14v6M18 4v6M18 14v6M3 7h6M15 7h6M3 17h6M15 17h6"/>
                </svg>
            </div>
            <div class="summary-card__info">
                <span class="summary-card__value"><?= $totalQty ?></span>
                <span class="summary-card__label">Total</span>
            </div>
        </div>
    </section>

    <!-- Filtros -->
    <section class="equipment-filters">
        <div class="equipment-search">
            <svg class="equipment-search__icon" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/>
                <path d="M21 21l-4.35-4.35"/>
            </svg>
            <input type="search" class="equipment-search__input" id="eq-search"
                   placeholder="Pesquisar equipamento..." autocomplete="off" />
        </div>
        <select class="filter-select" id="eq-category">
            <option value="">Todas as Categorias</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= htmlspecialchars(strtolower($cat)) ?>">
                    <?= htmlspecialchars($cat) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select class="filter-select" id="eq-status">
            <option value="">Todos os Estados</option>
            <option value="available">Disponível</option>
            <option value="maintenance">Manutenção</option>
            <option value="retired">Retirado</option>
        </select>
    </section>

    <!-- Grid -->
    <section class="equipment-grid" id="equipment-grid">
        <?php foreach ($equipment as $item):
            $inUse = max(0, $item->quantity - $item->available);
            $availPct = $item->quantity > 0
                ? round(($item->available / $item->quantity) * 100)
                : 0;

            $statusClass = match($item->status) {
                'available'   => 'available',
                'maintenance' => 'maintenance',
                'retired'     => 'retired',
                default       => 'maintenance'
            };
            $statusLabel = match($item->status) {
                'available'   => 'Disponível',
                'maintenance' => 'Manutenção',
                'retired'     => 'Retirado',
                default       => ucfirst($item->status)
            };

            $barClass = match(true) {
                $availPct >= 70 => 'progress-bar--good',
                $availPct >= 30 => 'progress-bar--warn',
                default         => 'progress-bar--low',
            };

            $categorySlug = strtolower($item->category ?? 'geral');
        ?>
            <article class="equipment-card <?= $item->status === 'maintenance' ? 'equipment-card--maintenance' : '' ?>"
                     data-name="<?= htmlspecialchars(strtolower($item->name)) ?>"
                     data-category="<?= htmlspecialchars($categorySlug) ?>"
                     data-status="<?= htmlspecialchars($item->status) ?>">
                <div class="equipment-card__content">
                    <div class="equipment-card__header">
                        <h3 class="equipment-card__name"><?= htmlspecialchars($item->name) ?></h3>
                        <span class="status-badge status-badge--<?= $statusClass ?>">
                            <span class="status-badge__dot"></span>
                            <?= $statusLabel ?>
                        </span>
                    </div>

                    <span class="equipment-card__category equipment-card__category--<?= $categorySlug ?>">
                        <?= htmlspecialchars($item->category ?? 'Geral') ?>
                    </span>

                    <div class="equipment-card__progress">
                        <div class="progress-track">
                            <div class="progress-bar <?= $barClass ?>"
                                 style="width: <?= $availPct ?>%"></div>
                        </div>
                    </div>

                    <div class="equipment-card__details">
                        <span class="equipment-card__count">
                            <strong><?= $item->available ?></strong>/<?= $item->quantity ?> disponíveis
                        </span>
                        <?php if ($inUse > 0): ?>
                            <span class="equipment-card__in-use">
                                <?= $inUse ?> em uso
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </section>

    <!-- Mensagem de sem resultados (controlada por JS) -->
    <p class="equipment-empty" id="eq-empty" style="display:none">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="32" height="32">
            <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
        </svg>
        Nenhum equipamento encontrado com os filtros aplicados.
    </p>

    <?php if (empty($equipment)): ?>
        <p class="equipment-empty">
            Nenhum equipamento registado.
        </p>
    <?php endif; ?>

</main>

<?php drawFooter('javascript/equipment.js'); ?>