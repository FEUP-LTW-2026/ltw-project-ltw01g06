<?php
declare(strict_types=1);

function drawUsersFilter(string $roleFilter, string $search): void {
    ?>
    <form method="get" class="admin-filter-bar" style="display:flex;gap:1rem;margin-bottom:1.5rem;flex-wrap:wrap;">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
               placeholder="Pesquisar por nome, email ou username..."
               style="flex:1;min-width:200px;padding:.625rem 1rem;border:2px solid #e9ecef;border-radius:8px;font-size:.875rem;" />
        <select name="role" style="padding:.625rem 1rem;border:2px solid #e9ecef;border-radius:8px;font-size:.875rem;">
            <option value="all"     <?= $roleFilter === 'all'     ? 'selected' : '' ?>>Todos</option>
            <option value="member"  <?= $roleFilter === 'member'  ? 'selected' : '' ?>>Membros</option>
            <option value="trainer" <?= $roleFilter === 'trainer' ? 'selected' : '' ?>>Treinadores</option>
            <option value="admin"   <?= $roleFilter === 'admin'   ? 'selected' : '' ?>>Admins</option>
        </select>
        <button type="submit" class="btn btn--primary">Filtrar</button>
        <?php if ($search || $roleFilter !== 'all'): ?>
            <a href="admin-members.php" class="btn btn--ghost">Limpar</a>
        <?php endif; ?>
    </form>
    <?php
}

function drawUsersTable(array $users, int $currentPage, int $totalPages, int $total, int $perPage): void {
    $start = (($currentPage - 1) * $perPage) + 1;
    $end   = min($currentPage * $perPage, $total);
    ?>
    <section class="admin-table-card">
        <header class="admin-table-card__header">
            <h2 class="admin-table-card__title">
                <?= $total ?> utilizador<?= $total !== 1 ? 'es' : '' ?>
            </h2>
        </header>

        <?php if (empty($users)): ?>
            <p style="padding:2rem;text-align:center;color:var(--gray-color);">Nenhum utilizador encontrado.</p>
        <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Utilizador</th>
                    <th>Username</th>
                    <th>Papel</th>
                    <th>Registo</th>
                    <th>Estado</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <?php drawUserRow($user); ?>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($totalPages > 1): ?>
        <div class="admin-pagination">
            <span class="admin-pagination__info">
                A mostrar <?= $start ?>–<?= $end ?> de <?= $total ?>
            </span>
            <div class="admin-pagination__controls">
                <?php if ($currentPage > 1): ?>
                    <a href="?page=<?= $currentPage - 1 ?>" class="pagination-btn">‹</a>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?= $i ?>"
                       class="pagination-btn <?= $i === $currentPage ? 'pagination-btn--active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
                <?php if ($currentPage < $totalPages): ?>
                    <a href="?page=<?= $currentPage + 1 ?>" class="pagination-btn">›</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </section>
    <?php
}

function drawUserRow(User $user): void {
    $initials    = strtoupper(mb_substr($user->fullName, 0, 1));
    $statusClass = $user->isActive ? 'active' : 'inactive';
    $statusLabel = $user->isActive ? 'Ativo' : 'Inativo';
    $roleLabel   = match($user->role) {
        'admin'   => 'Admin',
        'trainer' => 'Treinador',
        default   => 'Membro',
    };
    ?>
    <tr>
        <td>
            <div class="admin-table__user">
                <div class="admin-table__user-avatar"><?= htmlspecialchars($initials) ?></div>
                <div class="admin-table__user-info">
                    <span class="admin-table__user-name"><?= htmlspecialchars($user->fullName) ?></span>
                    <span class="admin-table__user-email"><?= htmlspecialchars($user->email) ?></span>
                </div>
            </div>
        </td>
        <td style="color:var(--gray-color);">@<?= htmlspecialchars($user->username) ?></td>
        <td><?= htmlspecialchars($roleLabel) ?></td>
        <td><?= htmlspecialchars(date('d M Y', strtotime($user->createdAt))) ?></td>
        <td><span class="status-pill status-pill--<?= $statusClass ?>"><?= $statusLabel ?></span></td>
        <td>
            <div class="admin-table__actions">
                <a href="admin-user-edit.php?id=<?= $user->id ?>" class="table-action-btn" title="Editar">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                </a>
                <form method="post" action="../actions/action_admin_toggle_user.php" style="display:inline">
                    <input type="hidden" name="user_id" value="<?= $user->id ?>" />
                    <button type="submit" class="table-action-btn" title="<?= $user->isActive ? 'Desativar' : 'Ativar' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                            <?php if ($user->isActive): ?>
                                <path d="M17 7 7 17M7 7l10 10"/>
                            <?php else: ?>
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                <path d="M22 4L12 14.01l-3-3"/>
                            <?php endif; ?>
                        </svg>
                    </button>
                </form>
            </div>
        </td>
    </tr>
    <?php
}
?>
