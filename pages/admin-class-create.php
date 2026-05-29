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

$db       = getDatabaseConnection();
$trainers = User::getAllUsers($db, 'trainer');
$counts = [
    'members'   => count(User::getAllUsers($db)),
    'trainers'  => count($trainers),
    'classes'   => count(GymClass::getAllClasses($db)),
    'equipment' => count(Equipment::getAll($db)),
];
drawHeader($session, 'Nova Aula', '', 'css/pages/admin.css');
?>
<div class="admin-layout">
    <?php drawAdminSidebar('classes', $counts); ?>
    <main class="admin-content">
        <header class="admin-header">
            <div class="admin-header__info"><h1>Nova Aula</h1><p>Criar uma nova aula no horário</p></div>
            <a href="admin-classes.php" class="btn btn--secondary">← Voltar</a>
        </header>
        <div class="admin-table-card" style="max-width:640px">
            <div class="admin-table-card__header"><h2 class="admin-table-card__title">Detalhes da Aula</h2></div>
            <form method="post" action="../actions/action_admin_create_class.php"
                  style="padding:var(--space-xl);display:flex;flex-direction:column;gap:var(--space-lg)">
                <?= csrfInput($session) ?>
                <div class="form-group">
                    <label class="form-label">Nome *</label>
                    <input type="text" name="name" class="form-input" placeholder="ex: Yoga Flow" required />
                </div>
                <div class="form-group">
                    <label class="form-label">Descrição</label>
                    <textarea name="description" class="form-input" rows="3" placeholder="Descrição da aula..."></textarea>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-lg)">
                    <div class="form-group">
                        <label class="form-label">Tipo *</label>
                        <input type="text" name="type" class="form-input" placeholder="ex: Yoga, HIIT, Pilates" required />
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nível *</label>
                        <select name="level" class="form-input form-select" required>
                            <option value="beginner">Iniciante</option>
                            <option value="intermediate">Intermédio</option>
                            <option value="advanced">Avançado</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Treinador</label>
                    <select name="trainer_id" class="form-input form-select">
                        <option value="">— Sem treinador —</option>
                        <?php foreach ($trainers as $t): ?>
                            <option value="<?= $t->id ?>"><?= htmlspecialchars($t->fullName) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-lg)">
                    <div class="form-group">
                        <label class="form-label">Dia da Semana *</label>
                        <select name="day_of_week" class="form-input form-select" required>
                            <option value="monday">Segunda-feira</option>
                            <option value="tuesday">Terça-feira</option>
                            <option value="wednesday">Quarta-feira</option>
                            <option value="thursday">Quinta-feira</option>
                            <option value="friday">Sexta-feira</option>
                            <option value="saturday">Sábado</option>
                            <option value="sunday">Domingo</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Hora de Início *</label>
                        <input type="time" name="start_time" class="form-input" required />
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-lg)">
                    <div class="form-group">
                        <label class="form-label">Duração (minutos) *</label>
                        <input type="number" name="duration_min" class="form-input" value="60" min="15" max="240" required />
                    </div>
                    <div class="form-group">
                        <label class="form-label">Capacidade *</label>
                        <input type="number" name="capacity" class="form-input" value="20" min="1" max="200" required />
                    </div>
                </div>
                <div style="display:flex;gap:var(--space-md);justify-content:flex-end">
                    <a href="admin-classes.php" class="btn btn--secondary">Cancelar</a>
                    <button type="submit" class="btn btn--primary">Criar Aula</button>
                </div>
            </form>
        </div>
    </main>
</div>
<?php drawFooter(); ?>