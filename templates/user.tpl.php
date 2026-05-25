<?php
declare(strict_types=1);

function drawProfileForm(object $user): void {
    ?>
    <form action="../actions/action_edit_profile.php" method="post" class="auth-form">
        <div class="form-group">
            <label for="full_name">Nome Completo</label>
            <input type="text" id="full_name" name="full_name"
                   value="<?= htmlspecialchars($user->fullName) ?>" required />
        </div>

        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username"
                   value="<?= htmlspecialchars($user->username) ?>" required />
        </div>

        <div class="form-group">
            <label for="bio">Bio</label>
            <textarea id="bio" name="bio" rows="3"
                      placeholder="Fala um pouco sobre ti..."><?= htmlspecialchars($user->bio ?? '') ?></textarea>
        </div>

        <hr style="margin: 1.5rem 0; border-color: var(--light-color);" />
        <p style="color: var(--gray-color); font-size:.875rem; margin-bottom:1rem;">
            Deixa em branco para não alterar a password.
        </p>

        <div class="form-group">
            <label for="current_password">Password Atual</label>
            <input type="password" id="current_password" name="current_password"
                   placeholder="Necessária para alterar a password" />
        </div>

        <div class="form-group">
            <label for="new_password">Nova Password</label>
            <input type="password" id="new_password" name="new_password"
                   placeholder="Mínimo 8 caracteres" minlength="8" />
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirmar Nova Password</label>
            <input type="password" id="confirm_password" name="confirm_password"
                   placeholder="Repete a nova password" />
        </div>

        <button type="submit" class="btn btn-primary btn-block">
            <i class="fas fa-save"></i> Guardar Alterações
        </button>
    </form>
    <?php
}

function drawUserEnrollments(array $enrollments): void {
    ?>
    <div class="enrollments-list">
        <h3><i class="fas fa-calendar-check"></i> As Minhas Inscrições</h3>
        <?php if (empty($enrollments)): ?>
            <p class="no-enrollments">Ainda não tens aulas inscritas.</p>
            <a href="../pages/schedule.php" class="btn btn-primary btn-small">Ver Horário</a>
        <?php else: ?>
            <ul>
                <?php foreach ($enrollments as $enrollment): ?>
                    <li>
                        <div>
                            <span class="class-name"><?= htmlspecialchars($enrollment->className ?? 'Aula') ?></span>
                            <br />
                            <span class="enrolled-date">
                                Inscrito em <?= htmlspecialchars(date('d/m/Y', strtotime($enrollment->enrolledAt))) ?>
                            </span>
                        </div>
                        <form action="../actions/action_enroll.php" method="post">
                            <input type="hidden" name="class_id" value="<?= $enrollment->classId ?>" />
                            <input type="hidden" name="action" value="cancel" />
                            <button type="submit" class="btn btn-danger btn-small"
                                    onclick="return confirm('Cancelar inscrição?')">
                                <i class="fas fa-times"></i> Cancelar
                            </button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    <?php
}
?>
