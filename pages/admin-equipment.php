<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/session.php');
require_once(__DIR__ . '/../database/connection.db.php');
require_once(__DIR__ . '/../database/equipment.class.php');
require_once(__DIR__ . '/../database/user.class.php');
require_once(__DIR__ . '/../database/class.class.php');
require_once(__DIR__ . '/../templates/common.tpl.php');
require_once(__DIR__ . '/../templates/admin.tpl.php');

$session = new Session();

if (!$session->isLoggedIn() || !$session->isAdmin()) {
    $session->addMessage('error', 'Acesso restrito a administradores.');
    header('Location: login.php');
    exit;
}

$db        = getDatabaseConnection();
$equipment = Equipment::getAll($db);

$counts = [
    'members'   => count(User::getAllUsers($db)),
    'trainers'  => count(User::getAllUsers($db, 'trainer')),
    'classes'   => count(GymClass::getAllClasses($db)),
    'equipment' => count($equipment),
];

drawHeader($session, 'Gestão de Equipamento', '', 'css/pages/admin.css');
?>

<div class="admin-layout">
    <?php drawAdminSidebar('equipment', $counts); ?>

    <main class="admin-content">
        <header class="admin-header">
            <div class="admin-header__info">
                <h1>Equipamento</h1>
                <p>Gerir o equipamento do ginásio</p>
            </div>
            <a href="admin-equipment-edit.php" class="btn btn--primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
                Novo Equipamento
            </a>
        </header>

        <section class="admin-table-card">
            <header class="admin-table-card__header">
                <h2 class="admin-table-card__title"><?= count($equipment) ?> equipamentos</h2>
            </header>
            <?php if (empty($equipment)): ?>
                <p style="padding:2rem;text-align:center;color:var(--color-text-muted)">Nenhum equipamento registado.</p>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Categoria</th>
                            <th>Quantidade</th>
                            <th>Disponíveis</th>
                            <th>Estado</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($equipment as $item):
                            $statusClass = match($item->status) { 'available' => 'active', 'maintenance' => 'inactive', default => 'pending' };
                            $statusLabel = match($item->status) { 'available' => 'Disponível', 'maintenance' => 'Manutenção', 'retired' => 'Retirado', default => ucfirst($item->status) };
                        ?>
                            <tr>
                                <td>
                                    <div>
                                        <span style="font-weight:600"><?= htmlspecialchars($item->name) ?></span>
                                        <?php if ($item->notes): ?>
                                            <br><span style="font-size:.8rem;color:var(--color-text-muted)"><?= htmlspecialchars($item->notes) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($item->category ?? '—') ?></td>
                                <td><?= $item->quantity ?></td>
                                <td>
                                    <span style="font-weight:600;color:<?= $item->available > 0 ? '#16a34a' : '#dc2626' ?>">
                                        <?= $item->available ?>
                                    </span>
                                </td>
                                <td><span class="status-pill status-pill--<?= $statusClass ?>"><?= $statusLabel ?></span></td>
                                <td>
                                    <div class="admin-table__actions">
                                        <a href="admin-equipment-edit.php?id=<?= $item->id ?>" class="table-action-btn" title="Editar">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    </main>
</div>

<?php drawFooter(); ?>