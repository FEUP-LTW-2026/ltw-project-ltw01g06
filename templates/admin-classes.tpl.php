<?php
declare(strict_types=1);

function drawClassesTable(array $classes): void {
    $dayLabels = [
        'monday'=>'Seg','tuesday'=>'Ter','wednesday'=>'Qua',
        'thursday'=>'Qui','friday'=>'Sex','saturday'=>'Sáb','sunday'=>'Dom'
    ];
    ?>
    <section class="admin-table-card">
        <header class="admin-table-card__header">
            <h2 class="admin-table-card__title"><?= count($classes) ?> aula<?= count($classes) !== 1 ? 's' : '' ?></h2>
        </header>

        <?php if (empty($classes)): ?>
            <p style="padding:2rem;text-align:center;color:var(--gray-color);">Nenhuma aula encontrada.</p>
        <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Aula</th>
                    <th>Tipo / Nível</th>
                    <th>Treinador</th>
                    <th>Dia / Hora</th>
                    <th>Inscrições</th>
                    <th>Estado</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($classes as $class):
                    $day = $dayLabels[$class->dayOfWeek] ?? ucfirst($class->dayOfWeek);
                    $pct = $class->capacity > 0 ? round($class->currentEnrollment / $class->capacity * 100) : 0;
                ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($class->name) ?></strong>
                        <?php if ($class->description): ?>
                            <br/><small style="color:var(--gray-color)"><?= htmlspecialchars(mb_substr($class->description, 0, 60)) ?>…</small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($class->type) ?><br/>
                        <small style="color:var(--gray-color)"><?= htmlspecialchars(ucfirst($class->level)) ?></small>
                    </td>
                    <td><?= htmlspecialchars($class->trainerName ?? '—') ?></td>
                    <td><?= $day ?> <?= htmlspecialchars($class->startTime) ?></td>
                    <td>
                        <?= $class->currentEnrollment ?>/<?= $class->capacity ?>
                        <div style="height:4px;background:#e9ecef;border-radius:2px;margin-top:4px">
                            <div style="height:4px;width:<?= $pct ?>%;background:<?= $pct >= 90 ? '#ef4444' : 'var(--accent-color)' ?>;border-radius:2px"></div>
                        </div>
                    </td>
                    <td>
                        <span class="status-pill status-pill--<?= $class->isActive ? 'active' : 'inactive' ?>">
                            <?= $class->isActive ? 'Ativa' : 'Inativa' ?>
                        </span>
                    </td>
                    <td>
                        <div class="admin-table__actions">
                            <a href="admin-class-edit.php?id=<?= $class->id ?>" class="table-action-btn" title="Editar">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                            </a>
                            <form method="post" action="../actions/action_admin_toggle_class.php" style="display:inline">
                                <input type="hidden" name="class_id" value="<?= $class->id ?>" />
                                <button type="submit" class="table-action-btn" title="<?= $class->isActive ? 'Desativar' : 'Ativar' ?>">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                        <?= $class->isActive
                                            ? '<path d="M17 7 7 17M7 7l10 10"/>'
                                            : '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/>' ?>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </section>
    <?php
}

function drawNewClassModal(array $trainers): void {
    $days = ['monday'=>'Segunda','tuesday'=>'Terça','wednesday'=>'Quarta',
             'thursday'=>'Quinta','friday'=>'Sexta','saturday'=>'Sábado','sunday'=>'Domingo'];
    ?>
    <div id="modal-new-class" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;">
        <div style="background:#fff;border-radius:12px;padding:2rem;width:100%;max-width:500px;max-height:90vh;overflow-y:auto;">
            <h2 style="margin-bottom:1.5rem">Nova Aula</h2>
            <form method="post" action="../actions/action_admin_class.php" class="auth-form">
                <input type="hidden" name="action" value="create" />

                <div class="form-group">
                    <label>Nome *</label>
                    <input type="text" name="name" required />
                </div>
                <div class="form-group">
                    <label>Descrição</label>
                    <textarea name="description" rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label>Tipo *</label>
                    <input type="text" name="type" placeholder="ex: HIIT, Yoga, Zumba" required />
                </div>
                <div class="form-group">
                    <label>Nível *</label>
                    <select name="level" required>
                        <option value="beginner">Iniciante</option>
                        <option value="intermediate" selected>Intermédio</option>
                        <option value="advanced">Avançado</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Treinador</label>
                    <select name="trainer_id">
                        <option value="">— Sem treinador —</option>
                        <?php foreach ($trainers as $t): ?>
                            <option value="<?= $t->id ?>"><?= htmlspecialchars($t->fullName) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Dia da Semana *</label>
                    <select name="day_of_week" required>
                        <?php foreach ($days as $val => $label): ?>
                            <option value="<?= $val ?>"><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Hora de Início *</label>
                    <input type="time" name="start_time" required />
                </div>
                <div class="form-group">
                    <label>Duração (minutos) *</label>
                    <input type="number" name="duration_min" value="60" min="15" max="240" required />
                </div>
                <div class="form-group">
                    <label>Capacidade *</label>
                    <input type="number" name="capacity" value="20" min="1" required />
                </div>

                <div style="display:flex;gap:1rem;margin-top:1rem;">
                    <button type="submit" class="btn btn-primary">Criar Aula</button>
                    <button type="button" class="btn btn-secondary"
                            onclick="document.getElementById('modal-new-class').style.display='none'">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php
}
?>
