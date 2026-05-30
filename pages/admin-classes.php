<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/session.php');
require_once(__DIR__ . '/../database/connection.db.php');
require_once(__DIR__ . '/../database/class.class.php');
require_once(__DIR__ . '/../database/enrollment.class.php');
require_once(__DIR__ . '/../database/user.class.php');
require_once(__DIR__ . '/../database/equipment.class.php');
require_once(__DIR__ . '/../templates/common.tpl.php');
require_once(__DIR__ . '/../templates/admin.tpl.php');

$session = new Session();
if (!$session->isLoggedIn() || !$session->isAdmin()) {
    $session->addMessage('error', 'Acesso restrito a administradores.');
    header('Location: login.php'); exit;
}

$db      = getDatabaseConnection();
$classes = GymClass::getAllClasses($db);

$counts = [
    'members'   => count(User::getAllUsers($db)),
    'trainers'  => count(User::getAllUsers($db, 'trainer')),
    'classes'   => count($classes),
    'equipment' => count(Equipment::getAll($db)),
];

$dayLabels = ['monday'=>'Seg','tuesday'=>'Ter','wednesday'=>'Qua',
              'thursday'=>'Qui','friday'=>'Sex','saturday'=>'Sáb','sunday'=>'Dom'];

// Pré-carregar inscritos por aula
$enrollmentsByClass = [];
foreach ($classes as $class) {
    $enrollmentsByClass[$class->id] = Enrollment::getClassEnrollments($db, $class->id);
}

drawHeader($session, 'Gestão de Aulas', 'javascript/admin.js', 'css/pages/admin.css');
?>
<div class="admin-layout">
    <?php drawAdminSidebar('classes', $counts); ?>
    <main class="admin-content">
        <header class="admin-header">
            <div class="admin-header__info">
                <h1>Aulas</h1>
                <p>Gerir o catálogo de aulas</p>
            </div>
            <a href="admin-class-create.php" class="btn btn--primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
                Nova Aula
            </a>
        </header>

        <section class="admin-table-card">
            <header class="admin-table-card__header">
                <h2 class="admin-table-card__title"><?= count($classes) ?> aulas</h2>
            </header>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Aula</th>
                        <th>Tipo</th>
                        <th>Treinador</th>
                        <th>Dia / Hora</th>
                        <th>Vagas</th>
                        <th>Estado</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($classes as $class):
                        $typeSlug    = strtolower(str_replace(' ', '-', $class->type));
                        $dayLabel    = $dayLabels[$class->dayOfWeek] ?? ucfirst($class->dayOfWeek);
                        $statusClass = $class->isActive ? 'active' : 'inactive';
                        $statusLabel = $class->isActive ? 'Ativa' : 'Inativa';
                        $pct         = $class->capacity > 0 ? round($class->currentEnrollment / $class->capacity * 100) : 0;
                        $enrolled    = $enrollmentsByClass[$class->id] ?? [];
                    ?>
                        <!-- Linha da aula -->
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:.5rem">
                                    <button class="table-action-btn"
                                            onclick="toggleEnrollments(<?= $class->id ?>)"
                                            title="Ver inscritos"
                                            id="toggle-btn-<?= $class->id ?>">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M6 9l6 6 6-6"/>
                                        </svg>
                                    </button>
                                    <span style="font-weight:600"><?= htmlspecialchars($class->name) ?></span>
                                </div>
                            </td>
                            <td><span class="tag tag--<?= $typeSlug ?>"><?= htmlspecialchars($class->type) ?></span></td>
                            <td><?= htmlspecialchars($class->trainerName ?? '—') ?></td>
                            <td><?= $dayLabel ?> · <?= htmlspecialchars($class->startTime) ?></td>
                            <td>
                                <div style="display:flex;align-items:center;gap:.5rem">
                                    <div style="width:60px;height:6px;background:var(--color-border);border-radius:3px;overflow:hidden">
                                        <div style="width:<?= $pct ?>%;height:100%;background:var(--color-primary);border-radius:3px"></div>
                                    </div>
                                    <span style="font-size:.85rem;color:var(--color-text-muted)" id="enroll-count-<?= $class->id ?>">
                                        <?= $class->currentEnrollment ?>/<?= $class->capacity ?>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <span class="status-pill status-pill--<?= $statusClass ?>"
                                      id="class-status-<?= $class->id ?>">
                                    <?= $statusLabel ?>
                                </span>
                            </td>
                            <td>
                                <div class="admin-table__actions">
                                    <button class="table-action-btn"
                                            title="<?= $class->isActive ? 'Desativar' : 'Ativar' ?>"
                                            onclick="toggleClass(<?= $class->id ?>, this)">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <?php if ($class->isActive): ?>
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

                        <!-- Linha expandível dos inscritos -->
                        <tr id="enrollments-<?= $class->id ?>" style="display:none">
                            <td colspan="7" style="padding:0;background:var(--color-bg)">
                                <div style="padding:var(--space-lg);border-top:1px solid var(--color-border)">
                                    <?php if (empty($enrolled)): ?>
                                        <p style="color:var(--color-text-muted);font-size:.875rem;margin:0 0 .75rem">
                                            Nenhum inscrito nesta aula.
                                        </p>
                                    <?php else: ?>
                                        <p style="font-size:.8rem;font-weight:600;color:var(--color-text-muted);
                                                  text-transform:uppercase;letter-spacing:.5px;margin:0 0 .75rem">
                                            <?= count($enrolled) ?> inscritos
                                        </p>
                                        <div style="display:flex;flex-wrap:wrap;gap:.5rem;margin-bottom:.75rem" id="chips-<?= $class->id ?>">
                                            <?php foreach ($enrolled as $e):
                                                $u = User::getUser($db, $e->userId);
                                                if (!$u) continue;
                                                $parts = explode(' ', $u->fullName);
                                                $ini   = mb_substr(implode('', array_map(fn($p) => strtoupper(mb_substr($p,0,1)), $parts)), 0, 2);
                                            ?>
                                                <div style="display:flex;align-items:center;gap:.5rem;
                                                            padding:.5rem .75rem;background:var(--color-surface);
                                                            border:1px solid var(--color-border);border-radius:var(--radius-md);
                                                            font-size:.85rem">
                                                    <div class="admin-table__user-avatar" style="width:28px;height:28px;font-size:.7rem">
                                                        <?= htmlspecialchars($ini) ?>
                                                    </div>
                                                    <span><?= htmlspecialchars($u->fullName) ?></span>
                                                    <button onclick="removeEnrollment(<?= $e->id ?>, <?= $class->id ?>, this)"
                                                            style="background:none;border:none;cursor:pointer;
                                                                   color:var(--color-text-muted);padding:0;line-height:1"
                                                            title="Remover inscrição">
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                             stroke-width="2" width="14" height="14">
                                                            <path d="M18 6L6 18M6 6l12 12"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php
                                    $allMembers  = User::getAllUsers($db, 'member');
                                    $enrolledIds = array_map(fn($e) => $e->userId, $enrolled);
                                    $notEnrolled = array_values(array_filter($allMembers, fn($m) => !in_array($m->id, $enrolledIds)));
                                    ?>
                                    <?php if (!empty($notEnrolled)): ?>
                                    <div style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap">
                                        <select id="add-member-<?= $class->id ?>"
                                                class="form-input form-select"
                                                style="width:220px;padding:6px 12px;font-size:.85rem">
                                            <option value="">— Adicionar membro —</option>
                                            <?php foreach ($notEnrolled as $m): ?>
                                                <option value="<?= $m->id ?>"><?= htmlspecialchars($m->fullName) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button onclick="addEnrollment(<?= $class->id ?>, this)"
                                                class="btn btn--primary"
                                                style="padding:6px 14px;font-size:.85rem">
                                            + Adicionar
                                        </button>
                                    </div>
                                    <?php endif; ?>
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