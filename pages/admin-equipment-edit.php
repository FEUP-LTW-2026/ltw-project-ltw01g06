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
if (!$session->isLoggedIn() || !$session->isAdmin()) { header('Location: login.php'); exit; }

$db   = getDatabaseConnection();
$id   = (int)($_GET['id'] ?? 0);
$item = $id > 0 ? Equipment::getById($db, $id) : null;

// Se não tem id, é criação
$isNew = !$item;

$counts = [
    'members'   => count(User::getAllUsers($db)),
    'trainers'  => count(User::getAllUsers($db, 'trainer')),
    'classes'   => count(GymClass::getAllClasses($db)),
    'equipment' => count(Equipment::getAll($db)),
];

$title = $isNew ? 'Novo Equipamento' : 'Editar Equipamento';
drawHeader($session, $title, '', 'css/pages/admin.css');
?>
<div class="admin-layout">
    <?php drawAdminSidebar('equipment', $counts); ?>
    <main class="admin-content">
        <header class="admin-header">
            <div class="admin-header__info">
                <h1><?= $title ?></h1>
                <p><?= $isNew ? 'Adicionar novo equipamento' : htmlspecialchars($item->name) ?></p>
            </div>
            <a href="admin-equipment.php" class="btn btn--secondary">← Voltar</a>
        </header>

        <div class="admin-table-card" style="max-width:600px">
            <div class="admin-table-card__header">
                <h2 class="admin-table-card__title"><?= $title ?></h2>
            </div>
            <form method="post"
                  action="<?= $isNew ? '../actions/action_admin_create_equipment.php' : '../actions/action_admin_edit_equipment.php' ?>"
                  <?= csrfInput($session) ?>"
                  style="padding:var(--space-xl);display:flex;flex-direction:column;gap:var(--space-lg)">

                <?php if (!$isNew): ?>
                    <input type="hidden" name="equipment_id" value="<?= $item->id ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label class="form-label">Nome *</label>
                    <input type="text" name="name" class="form-input"
                           value="<?= htmlspecialchars($item->name ?? '') ?>" required />
                </div>
                <div class="form-group">
                    <label class="form-label">Categoria</label>
                    <input type="text" name="category" class="form-input"
                           placeholder="ex: Cardio, Weights, Yoga"
                           value="<?= htmlspecialchars($item->category ?? '') ?>" />
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-lg)">
                    <div class="form-group">
                        <label class="form-label">Quantidade Total *</label>
                        <input type="number" name="quantity" class="form-input"
                               value="<?= $item->quantity ?? 1 ?>" min="0" required />
                    </div>
                    <div class="form-group">
                        <label class="form-label">Disponíveis *</label>
                        <input type="number" name="available" class="form-input"
                               value="<?= $item->available ?? 1 ?>" min="0" required />
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Estado *</label>
                    <select name="status" class="form-input form-select" required>
                        <option value="available"   <?= ($item->status ?? '') === 'available'   ? 'selected' : '' ?>>Disponível</option>
                        <option value="maintenance" <?= ($item->status ?? '') === 'maintenance' ? 'selected' : '' ?>>Manutenção</option>
                        <option value="retired"     <?= ($item->status ?? '') === 'retired'     ? 'selected' : '' ?>>Retirado</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Notas</label>
                    <textarea name="notes" class="form-input" rows="3"
                              placeholder="Observações opcionais..."><?= htmlspecialchars($item->notes ?? '') ?></textarea>
                </div>

                <div style="display:flex;gap:var(--space-md);justify-content:flex-end">
                    <a href="admin-equipment.php" class="btn btn--secondary">Cancelar</a>
                    <button type="submit" class="btn btn--primary">
                        <?= $isNew ? 'Criar Equipamento' : 'Guardar' ?>
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>
<?php drawFooter(); ?>