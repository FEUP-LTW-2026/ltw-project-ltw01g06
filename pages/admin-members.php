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
if (!$session->isLoggedIn() || !$session->isAdmin()) {
    $session->addMessage('error', 'Acesso restrito a administradores.');
    header('Location: login.php'); exit;
}

$db = getDatabaseConnection();

$counts = [
    'members'   => count(User::getAllUsers($db)),
    'trainers'  => count(User::getAllUsers($db, 'trainer')),
    'classes'   => count(GymClass::getAllClasses($db)),
    'equipment' => count(Equipment::getAll($db)),
];

$allUsers = User::getAllUsers($db);

drawHeader($session, 'Gestão de Membros', 'javascript/admin.js', 'css/pages/admin.css');
?>
<div class="admin-layout">
    <?php drawAdminSidebar('members', $counts); ?>
    <main class="admin-content">
        <header class="admin-header">
            <div class="admin-header__info">
                <h1>Utilizadores</h1>
                <p>Gerir membros, treinadores e admins</p>
            </div>
            <a href="admin-user-create.php" class="btn btn--primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
                Novo Utilizador
            </a>
        </header>

        <!-- Filtros (JS) -->
        <div class="admin-table-card" style="padding:var(--space-lg);margin-bottom:var(--space-xl);
             display:flex;gap:var(--space-md);flex-wrap:wrap;align-items:center">
            <div style="position:relative;flex:1;min-width:200px">
                <svg style="position:absolute;left:12px;top:50%;transform:translateY(-50%);
                            width:16px;height:16px;color:var(--color-text-muted)"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
                </svg>
                <input type="search" id="member-search"
                       placeholder="Pesquisar por nome, email ou username..."
                       class="form-input" style="padding-left:40px"
                       oninput="filterMembers()" />
            </div>
            <select id="member-role" class="form-input form-select"
                    style="width:180px" onchange="filterMembers()">
                <option value="">Todos os papéis</option>
                <option value="member">Membros</option>
                <option value="trainer">Treinadores</option>
                <option value="admin">Admins</option>
            </select>
            <select id="member-status" class="form-input form-select"
                    style="width:140px" onchange="filterMembers()">
                <option value="">Todos os estados</option>
                <option value="active">Ativos</option>
                <option value="inactive">Inativos</option>
            </select>
            <span id="member-count" style="font-size:.85rem;color:var(--color-text-muted)">
                <?= count($allUsers) ?> utilizadores
            </span>
        </div>

        <!-- Tabela -->
        <section class="admin-table-card">
            <table class="admin-table" id="members-table">
                <thead>
                    <tr>
                        <th>Utilizador</th>
                        <th>Papel</th>
                        <th>Username</th>
                        <th>Data Registo</th>
                        <th>Estado</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allUsers as $u):
                        $parts    = explode(' ', $u->fullName);
                        $initials = mb_substr(implode('', array_map(fn($p) => strtoupper(mb_substr($p, 0, 1)), $parts)), 0, 2);
                        $roleLabel   = match($u->role) { 'admin' => 'Admin', 'trainer' => 'Treinador', default => 'Membro' };
                        $statusClass = $u->isActive ? 'active' : 'inactive';
                        $statusLabel = $u->isActive ? 'Ativo' : 'Inativo';
                    ?>
                        <tr data-name="<?= htmlspecialchars(strtolower($u->fullName)) ?>"
                            data-email="<?= htmlspecialchars(strtolower($u->email)) ?>"
                            data-username="<?= htmlspecialchars(strtolower($u->username)) ?>"
                            data-role="<?= htmlspecialchars($u->role) ?>"
                            data-status="<?= $u->isActive ? 'active' : 'inactive' ?>">
                            <td>
                                <div class="admin-table__user">
                                    <div class="admin-table__user-avatar"><?= htmlspecialchars($initials) ?></div>
                                    <div class="admin-table__user-info">
                                        <span class="admin-table__user-name"><?= htmlspecialchars($u->fullName) ?></span>
                                        <span class="admin-table__user-email"><?= htmlspecialchars($u->email) ?></span>
                                    </div>
                                </div>
                            </td>
                            <td><?= $roleLabel ?></td>
                            <td><?= htmlspecialchars($u->username) ?></td>
                            <td><?= date('d M Y', strtotime($u->createdAt)) ?></td>
                            <td>
                                <span class="status-pill status-pill--<?= $statusClass ?>"
                                      id="status-<?= $u->id ?>">
                                    <?= $statusLabel ?>
                                </span>
                            </td>
                            <td>
                                <div class="admin-table__actions">
                                    <a href="admin-user-edit.php?id=<?= $u->id ?>"
                                       class="table-action-btn" title="Editar">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                        </svg>
                                    </a>
                                    <button class="table-action-btn"
                                            title="<?= $u->isActive ? 'Desativar' : 'Ativar' ?>"
                                            onclick="toggleUser(<?= $u->id ?>, this)">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <?php if ($u->isActive): ?>
                                                <path d="M17 7 7 17M7 7l10 10"/>
                                            <?php else: ?>
                                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                                <path d="M22 4L12 14.01l-3-3"/>
                                            <?php endif; ?>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>
</div>
<?php drawFooter('javascript/admin.js'); ?>