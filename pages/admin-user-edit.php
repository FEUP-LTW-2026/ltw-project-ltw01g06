<?php
declare(strict_types=1);
require_once(__DIR__ . '/../utils/session.php');
require_once(__DIR__ . '/../database/connection.db.php');
require_once(__DIR__ . '/../database/user.class.php');
require_once(__DIR__ . '/../database/class.class.php');
require_once(__DIR__ . '/../database/equipment.class.php');
require_once(__DIR__ . '/../templates/common.tpl.php');
require_once(__DIR__ . '/../templates/admin.tpl.php');

$session = new Session();
if (!$session->isLoggedIn() || !$session->isAdmin()) { header('Location: login.php'); exit; }

$db     = getDatabaseConnection();
$userId = (int)($_GET['id'] ?? 0);
$user   = $userId > 0 ? User::getUser($db, $userId) : null;
if (!$user) { $session->addMessage('error', 'Utilizador não encontrado.'); header('Location: admin-members.php'); exit; }

$counts = [
    'members'   => count(User::getAllUsers($db)),
    'trainers'  => count(User::getAllUsers($db, 'trainer')),
    'classes'   => count(GymClass::getAllClasses($db)),
    'equipment' => count(Equipment::getAll($db)),
];
drawHeader($session, 'Editar Utilizador', '', 'css/pages/admin.css');
?>
<div class="admin-layout">
    <?php drawAdminSidebar('members', $counts); ?>
    <main class="admin-content">
        <header class="admin-header">
            <div class="admin-header__info"><h1>Editar Utilizador</h1><p><?= htmlspecialchars($user->fullName) ?></p></div>
            <a href="admin-members.php" class="btn btn--secondary">← Voltar</a>
        </header>
        <div class="admin-table-card" style="max-width:600px">
            <div class="admin-table-card__header"><h2 class="admin-table-card__title">Informações</h2></div>
            <form method="post" action="../actions/action_admin_edit_user.php"
                  style="padding:var(--space-xl);display:flex;flex-direction:column;gap:var(--space-lg)">
                <input type="hidden" name="user_id" value="<?= $user->id ?>">
                <div class="form-group">
                    <label class="form-label">Nome Completo</label>
                    <input type="text" name="full_name" class="form-input" value="<?= htmlspecialchars($user->fullName) ?>" required />
                </div>
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-input" value="<?= htmlspecialchars($user->username) ?>" required />
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-input" value="<?= htmlspecialchars($user->email) ?>" disabled />
                </div>
                <div class="form-group">
                    <label class="form-label">Papel</label>
                    <select name="role" class="form-input form-select">
                        <option value="member"  <?= $user->role==='member'  ? 'selected':'' ?>>Membro</option>
                        <option value="trainer" <?= $user->role==='trainer' ? 'selected':'' ?>>Treinador</option>
                        <option value="admin"   <?= $user->role==='admin'   ? 'selected':'' ?>>Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Estado</label>
                    <select name="is_active" class="form-input form-select">
                        <option value="1" <?= $user->isActive  ? 'selected':'' ?>>Ativo</option>
                        <option value="0" <?= !$user->isActive ? 'selected':'' ?>>Inativo</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Bio</label>
                    <textarea name="bio" class="form-input" rows="3"><?= htmlspecialchars($user->bio ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Nova Palavra-passe (deixar vazio para não alterar)</label>
                    <input type="password" name="new_password" class="form-input" placeholder="Mínimo 8 caracteres" minlength="8" />
                </div>
                <div style="display:flex;gap:var(--space-md);justify-content:flex-end">
                    <a href="admin-members.php" class="btn btn--secondary">Cancelar</a>
                    <button type="submit" class="btn btn--primary">Guardar</button>
                </div>
            </form>
        </div>
    </main>
</div>
<?php drawFooter(); ?>