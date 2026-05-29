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

$db = getDatabaseConnection();
$counts = [
    'members'   => count(User::getAllUsers($db)),
    'trainers'  => count(User::getAllUsers($db, 'trainer')),
    'classes'   => count(GymClass::getAllClasses($db)),
    'equipment' => count(Equipment::getAll($db)),
];
drawHeader($session, 'Criar Utilizador', '', 'css/pages/admin.css');
?>
<div class="admin-layout">
    <?php drawAdminSidebar('members', $counts); ?>
    <main class="admin-content">
        <header class="admin-header">
            <div class="admin-header__info"><h1>Criar Utilizador</h1><p>Adicionar novo membro, treinador ou admin</p></div>
            <a href="admin-members.php" class="btn btn--secondary">← Voltar</a>
        </header>
        <div class="admin-table-card" style="max-width:600px">
            <div class="admin-table-card__header"><h2 class="admin-table-card__title">Novo Utilizador</h2></div>
            <form method="post" action="../actions/action_admin_create_user.php"
                  style="padding:var(--space-xl);display:flex;flex-direction:column;gap:var(--space-lg)">
                <?= csrfInput($session) ?>
                <div class="form-group">
                    <label class="form-label">Nome Completo *</label>
                    <input type="text" name="full_name" class="form-input" placeholder="João Silva" required />
                </div>
                <div class="form-group">
                    <label class="form-label">Username *</label>
                    <input type="text" name="username" class="form-input" placeholder="joaosilva" required />
                </div>
                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-input" placeholder="joao@exemplo.com" required />
                </div>
                <div class="form-group">
                    <label class="form-label">Palavra-passe *</label>
                    <input type="password" name="password" class="form-input" placeholder="Mínimo 8 caracteres" minlength="8" required />
                </div>
                <div class="form-group">
                    <label class="form-label">Papel *</label>
                    <select name="role" class="form-input form-select" required>
                        <option value="member">Membro</option>
                        <option value="trainer">Treinador</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Bio</label>
                    <textarea name="bio" class="form-input" rows="3" placeholder="Descrição opcional..."></textarea>
                </div>
                <div style="display:flex;gap:var(--space-md);justify-content:flex-end">
                    <a href="admin-members.php" class="btn btn--secondary">Cancelar</a>
                    <button type="submit" class="btn btn--primary">Criar Utilizador</button>
                </div>
            </form>
        </div>
    </main>
</div>
<?php drawFooter(); ?>