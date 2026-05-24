<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/session.php');
require_once(__DIR__ . '/../database/connection.db.php');
require_once(__DIR__ . '/../database/equipment.class.php');
require_once(__DIR__ . '/../templates/common.tpl.php');

$session   = new Session();
$db        = getDatabaseConnection();
$equipment = Equipment::getAll($db);

// Agrupar por categoria
$byCategory = [];
foreach ($equipment as $item) {
    $cat = $item->category ?? 'Outro';
    $byCategory[$cat][] = $item;
}

drawHeader($session, 'Equipamento');
?>

<section class="page-header">
    <h1><i class="fas fa-dumbbell"></i> Equipamento</h1>
    <p>Disponibilidade atual do equipamento do ginásio.</p>
</section>

<?php if (empty($equipment)): ?>
    <p style="text-align:center;color:var(--gray-color);">Nenhum equipamento registado.</p>
<?php else: ?>
    <?php foreach ($byCategory as $category => $items): ?>
        <section style="margin-bottom:2rem;">
            <h2 style="color:var(--secondary-color);margin-bottom:1rem;border-bottom:2px solid var(--light-color);padding-bottom:.5rem;">
                <?= htmlspecialchars($category) ?>
            </h2>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:1rem;">
                <?php foreach ($items as $item): ?>
                    <div class="feature-card" style="text-align:left;">
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:.75rem;">
                            <h3 style="font-size:1rem;"><?= htmlspecialchars($item->name) ?></h3>
                            <?php $cls = match($item->status) {
                                'available'   => 'active',
                                'maintenance' => 'inactive',
                                default       => 'inactive'
                            }; ?>
                            <span class="status-pill status-pill--<?= $cls ?>" style="font-size:.7rem;">
                                <?= htmlspecialchars($item->getStatusLabel()) ?>
                            </span>
                        </div>

                        <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.5rem;">
                            <span style="font-size:1.5rem;font-weight:700;color:<?= $item->available > 0 ? 'var(--success-color)' : 'var(--error-color)' ?>">
                                <?= $item->available ?>
                            </span>
                            <span style="color:var(--gray-color);font-size:.875rem;">
                                disponível<?= $item->available !== 1 ? 'is' : '' ?> de <?= $item->quantity ?>
                            </span>
                        </div>

                        <!-- Barra de disponibilidade -->
                        <?php $pct = $item->quantity > 0 ? round($item->available / $item->quantity * 100) : 0; ?>
                        <div style="height:6px;background:#e9ecef;border-radius:3px;">
                            <div style="height:6px;width:<?= $pct ?>%;background:<?= $pct > 50 ? 'var(--success-color)' : ($pct > 0 ? 'var(--warning-color)' : 'var(--error-color)') ?>;border-radius:3px;transition:width .3s;"></div>
                        </div>

                        <?php if ($item->notes): ?>
                            <p style="margin-top:.5rem;font-size:.8rem;color:var(--gray-color);">
                                <?= htmlspecialchars($item->notes) ?>
                            </p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>
<?php endif; ?>

<?php drawFooter(); ?>
