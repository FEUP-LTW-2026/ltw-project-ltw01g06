<?php
declare(strict_types=1);

function drawEquipmentTable(array $equipment): void {
    ?>
    <section class="admin-table-card">
        <header class="admin-table-card__header">
            <h2 class="admin-table-card__title"><?= count($equipment) ?> item<?= count($equipment) !== 1 ? 's' : '' ?></h2>
        </header>

        <?php if (empty($equipment)): ?>
            <p style="padding:2rem;text-align:center;color:var(--gray-color);">Nenhum equipamento registado.</p>
        <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Equipamento</th>
                    <th>Categoria</th>
                    <th>Quantidade</th>
                    <th>Disponível</th>
                    <th>Estado</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($equipment as $item): ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($item->name) ?></strong>
                        <?php if ($item->notes): ?>
                            <br/><small style="color:var(--gray-color)"><?= htmlspecialchars($item->notes) ?></small>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($item->category ?? '—') ?></td>
                    <td><?= $item->quantity ?></td>
                    <td>
                        <?= $item->available ?>/<?= $item->quantity ?>
                        <?php if ($item->available === 0): ?>
                            <span style="color:var(--error-color);font-size:.75rem;"> (esgotado)</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php $cls = match($item->status) {
                            'available'   => 'active',
                            'maintenance' => 'inactive',
                            default       => 'inactive'
                        }; ?>
                        <span class="status-pill status-pill--<?= $cls ?>">
                            <?= htmlspecialchars($item->getStatusLabel()) ?>
                        </span>
                    </td>
                    <td>
                        <div class="admin-table__actions">
                            <button class="table-action-btn" title="Editar"
                                    onclick="openEditEquipment(<?= htmlspecialchars(json_encode([
                                        'id'        => $item->id,
                                        'name'      => $item->name,
                                        'category'  => $item->category,
                                        'quantity'  => $item->quantity,
                                        'available' => $item->available,
                                        'status'    => $item->status,
                                        'notes'     => $item->notes,
                                    ])) ?>)">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                            </button>
                            <form method="post" action="../actions/action_admin_equipment.php" style="display:inline"
                                  onsubmit="return confirm('Eliminar este equipamento?')">
                                <input type="hidden" name="action" value="delete" />
                                <input type="hidden" name="id" value="<?= $item->id ?>" />
                                <button type="submit" class="table-action-btn table-action-btn--danger" title="Eliminar">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                        <polyline points="3 6 5 6 21 6"/>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
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

    <!-- Modal editar equipamento -->
    <div id="modal-edit-equipment" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;">
        <div style="background:#fff;border-radius:12px;padding:2rem;width:100%;max-width:440px;">
            <h2 style="margin-bottom:1.5rem">Editar Equipamento</h2>
            <form method="post" action="../actions/action_admin_equipment.php" class="auth-form">
                <input type="hidden" name="action" value="edit" />
                <input type="hidden" name="id" id="edit-eq-id" />
                <div class="form-group">
                    <label>Nome *</label>
                    <input type="text" name="name" id="edit-eq-name" required />
                </div>
                <div class="form-group">
                    <label>Categoria</label>
                    <input type="text" name="category" id="edit-eq-category" />
                </div>
                <div class="form-group">
                    <label>Quantidade total *</label>
                    <input type="number" name="quantity" id="edit-eq-quantity" min="0" required />
                </div>
                <div class="form-group">
                    <label>Disponível *</label>
                    <input type="number" name="available" id="edit-eq-available" min="0" required />
                </div>
                <div class="form-group">
                    <label>Estado *</label>
                    <select name="status" id="edit-eq-status" required>
                        <option value="available">Disponível</option>
                        <option value="maintenance">Manutenção</option>
                        <option value="retired">Retirado</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Notas</label>
                    <textarea name="notes" id="edit-eq-notes" rows="2"></textarea>
                </div>
                <div style="display:flex;gap:1rem;margin-top:1rem;">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <button type="button" class="btn btn-secondary"
                            onclick="document.getElementById('modal-edit-equipment').style.display='none'">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php
}

function drawNewEquipmentModal(): void {
    ?>
    <div id="modal-new-equipment" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;">
        <div style="background:#fff;border-radius:12px;padding:2rem;width:100%;max-width:440px;">
            <h2 style="margin-bottom:1.5rem">Novo Equipamento</h2>
            <form method="post" action="../actions/action_admin_equipment.php" class="auth-form">
                <input type="hidden" name="action" value="create" />
                <div class="form-group">
                    <label>Nome *</label>
                    <input type="text" name="name" required />
                </div>
                <div class="form-group">
                    <label>Categoria</label>
                    <input type="text" name="category" placeholder="ex: Cardio, Weights" />
                </div>
                <div class="form-group">
                    <label>Quantidade total *</label>
                    <input type="number" name="quantity" value="1" min="1" required />
                </div>
                <div class="form-group">
                    <label>Disponível *</label>
                    <input type="number" name="available" value="1" min="0" required />
                </div>
                <div class="form-group">
                    <label>Estado *</label>
                    <select name="status" required>
                        <option value="available">Disponível</option>
                        <option value="maintenance">Manutenção</option>
                        <option value="retired">Retirado</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Notas</label>
                    <textarea name="notes" rows="2"></textarea>
                </div>
                <div style="display:flex;gap:1rem;margin-top:1rem;">
                    <button type="submit" class="btn btn-primary">Criar</button>
                    <button type="button" class="btn btn-secondary"
                            onclick="document.getElementById('modal-new-equipment').style.display='none'">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php
}
?>
