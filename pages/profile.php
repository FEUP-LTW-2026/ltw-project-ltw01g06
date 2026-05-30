<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/session.php');
require_once(__DIR__ . '/../database/connection.db.php');
require_once(__DIR__ . '/../database/user.class.php');
require_once(__DIR__ . '/../database/enrollment.class.php');
require_once(__DIR__ . '/../database/trainer.class.php');
require_once(__DIR__ . '/../templates/common.tpl.php');

$session = new Session();

if (!$session->isLoggedIn()) {
    $session->addMessage('error', 'Precisas de estar autenticado para ver o perfil.');
    header('Location: login.php');
    exit;
}

$db          = getDatabaseConnection();
$user        = User::getUser($db, $session->getId());
$enrollments = Enrollment::getUserEnrollments($db, $session->getId());

if (!$user) {
    $session->addMessage('error', 'Utilizador não encontrado.');
    header('Location: dashboard.php');
    exit;
}

$parts    = explode(' ', $user->fullName);
$initials = mb_substr(
    implode('', array_map(fn($p) => strtoupper(mb_substr($p, 0, 1)), $parts)),
    0, 2
);

$hasPhoto = $user->photo && $user->photo !== 'default_profile.png'
    && file_exists(__DIR__ . '/../docs/uploads/' . $user->photo);

$trainerProfile = null;
if ($user->role === 'trainer') {
    $trainerProfile = TrainerProfile::getProfile($db, $user->id);
}

drawHeader($session, 'Editar Perfil', 'javascript/profile.js', 'css/pages/profile.css');
?>

<main class="profile-container">

    <header class="profile-header">
        <h1 class="profile-header__title">Editar Perfil</h1>
        <p class="profile-header__subtitle">Atualize as suas informações pessoais</p>
    </header>

    <!-- Avatar -->
    <section class="avatar-upload">
        <div class="avatar-upload__preview" id="avatar-preview">
            <?php if ($hasPhoto): ?>
                <img src="../docs/uploads/<?= htmlspecialchars($user->photo) ?>"
                     alt="Foto de perfil"
                     style="width:100%;height:100%;object-fit:cover;border-radius:inherit" />
            <?php else: ?>
                <span><?= htmlspecialchars($initials) ?></span>
            <?php endif; ?>
        </div>
        <div class="avatar-upload__info">
            <h3 class="avatar-upload__title">Foto de Perfil</h3>
            <p class="avatar-upload__desc">JPG, PNG ou GIF. Tamanho máximo de 2MB.</p>
            <div class="avatar-upload__actions">
                <label class="btn btn--secondary" style="cursor:pointer">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                         width="16" height="16">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12"/>
                    </svg>
                    Carregar
                    <input type="file" id="photo-input" accept="image/*" style="display:none" />
                </label>
                <?php if ($hasPhoto): ?>
                    <form method="post" action="../actions/action_edit_profile.php"
                          style="display:inline">
                <?= csrfInput($session) ?>
                        <input type="hidden" name="remove_photo" value="1" />
                        <button type="submit" class="btn btn--ghost"
                                onclick="return confirm('Remover foto de perfil?')">
                            Remover
                        </button>
                    </form>
                <?php else: ?>
                    <button class="btn btn--ghost" disabled>Remover</button>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Formulário principal (com enctype para upload) -->
    <form class="profile-form" method="post" action="../actions/action_edit_profile.php"
          enctype="multipart/form-data" id="profile-form">
                <?= csrfInput($session) ?>

        <!-- Campo de foto oculto dentro do form principal -->
        <input type="file" name="photo" id="photo-file" accept="image/*" style="display:none" />

        <!-- Informações Pessoais -->
        <section class="profile-form__section">
            <h2 class="profile-form__section-title">Informações Pessoais</h2>
            <div class="profile-form__grid">
                <div class="profile-form__group">
                    <label class="profile-form__label" for="full-name">Nome Completo</label>
                    <input class="profile-form__input" type="text" id="full-name" name="full_name"
                           value="<?= htmlspecialchars($user->fullName) ?>" required />
                </div>
                <div class="profile-form__group">
                    <label class="profile-form__label" for="username">Nome de Utilizador</label>
                    <input class="profile-form__input" type="text" id="username" name="username"
                           value="<?= htmlspecialchars($user->username) ?>" required />
                </div>
                <div class="profile-form__group profile-form__group--full">
                    <label class="profile-form__label" for="email">Email</label>
                    <input class="profile-form__input" type="email" id="email"
                           value="<?= htmlspecialchars($user->email) ?>" disabled
                           title="O email não pode ser alterado" />
                </div>
                <div class="profile-form__group profile-form__group--full">
                    <label class="profile-form__label" for="bio">Bio</label>
                    <textarea class="profile-form__input profile-form__textarea"
                              id="bio" name="bio"
                              placeholder="Conte-nos um pouco sobre si..."><?= htmlspecialchars($user->bio ?? '') ?></textarea>
                </div>
            </div>
        </section>


        <!-- Perfil de Treinador -->
        <?php if ($user->role === 'trainer'): ?>
        <section class="profile-form__section">
            <h2 class="profile-form__section-title">Perfil de Treinador</h2>
            <div class="profile-form__grid">
                <div class="profile-form__group profile-form__group--full">
                    <label class="profile-form__label" for="specializations">Especializações</label>
                    <input class="profile-form__input" type="text" id="specializations"
                           name="specializations"
                           value="<?= htmlspecialchars($trainerProfile?->specializations ?? '') ?>"
                           placeholder="ex: HIIT, Yoga, Pilates (separados por vírgula)" />
                    <span style="font-size:.8rem;color:var(--color-text-muted);margin-top:.25rem">
                        Separa as especializações por vírgula
                    </span>
                </div>
                <div class="profile-form__group profile-form__group--full">
                    <label class="profile-form__label" for="certifications">Certificações</label>
                    <input class="profile-form__input" type="text" id="certifications"
                           name="certifications"
                           value="<?= htmlspecialchars($trainerProfile?->certifications ?? '') ?>"
                           placeholder="ex: NASM CPT, CrossFit Level 2 (separados por vírgula)" />
                    <span style="font-size:.8rem;color:var(--color-text-muted);margin-top:.25rem">
                        Separa as certificações por vírgula
                    </span>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- Alterar Password -->
        <section class="profile-form__section">
            <h2 class="profile-form__section-title">Alterar Palavra-passe</h2>
            <div class="profile-form__grid">
                <div class="profile-form__group profile-form__group--full">
                    <label class="profile-form__label" for="current-password">Palavra-passe Atual</label>
                    <input class="profile-form__input" type="password" id="current-password"
                           name="current_password"
                           placeholder="Necessária para alterar a palavra-passe" />
                </div>
                <div class="profile-form__group">
                    <label class="profile-form__label" for="new-password">Nova Palavra-passe</label>
                    <input class="profile-form__input" type="password" id="new-password"
                           name="new_password" placeholder="Mínimo 8 caracteres" minlength="8" />
                </div>
                <div class="profile-form__group">
                    <label class="profile-form__label" for="confirm-password">Confirmar Palavra-passe</label>
                    <input class="profile-form__input" type="password" id="confirm-password"
                           name="confirm_password"
                           placeholder="Repita a nova palavra-passe" />
                </div>
            </div>
            <div class="password-requirements">
                <h4 class="password-requirements__title">Requisitos da palavra-passe:</h4>
                <ul class="password-requirements__list">
                    <li class="password-requirements__item" id="req-length">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                        </svg>
                        Mínimo de 8 caracteres
                    </li>
                    <li class="password-requirements__item" id="req-upper">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                        </svg>
                        Pelo menos uma letra maiúscula
                    </li>
                    <li class="password-requirements__item" id="req-number">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                        </svg>
                        Pelo menos um número
                    </li>
                </ul>
            </div>
        </section>

        <!-- Ações -->
        <div class="profile-form__actions">
            <a href="dashboard.php" class="btn btn--secondary">Cancelar</a>
            <button type="submit" class="btn btn--primary">Guardar Alterações</button>
        </div>

    </form>

    <!-- Inscrições ativas -->
    <?php if (!empty($enrollments)): ?>
        <section class="profile-form__section"
                 style="background:var(--color-surface);border:1px solid var(--color-border);
                        border-radius:var(--radius-xl);padding:var(--space-xl);margin-top:var(--space-xl)">
            <h2 class="profile-form__section-title">As Minhas Inscrições</h2>
            <div style="display:flex;flex-direction:column;gap:.75rem">
                <?php foreach ($enrollments as $e): ?>
                    <div style="display:flex;align-items:center;justify-content:space-between;
                                padding:1rem 1.25rem;background:var(--color-bg);
                                border:1px solid var(--color-border);border-radius:var(--radius-md)">
                        <div>
                            <strong><?= htmlspecialchars($e->className ?? 'Aula') ?></strong>
                            <span style="margin-left:.75rem;font-size:.8rem;color:var(--color-text-muted)">
                                inscrito em <?= date('d/m/Y', strtotime($e->enrolledAt)) ?>
                            </span>
                        </div>
                        <form method="post" action="../actions/action_enroll.php">
                <?= csrfInput($session) ?>
                            <input type="hidden" name="class_id" value="<?= $e->classId ?>">
                            <input type="hidden" name="action" value="cancel">
                            <button type="submit" class="btn btn--ghost"
                                    onclick="return confirm('Cancelar inscrição?')"
                                    style="color:#dc2626">
                                Cancelar
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- Zona de Perigo -->
    <section class="danger-zone">
        <h3 class="danger-zone__title">Eliminar Conta</h3>
        <p class="danger-zone__desc">
            Uma vez eliminada, a sua conta não pode ser recuperada.
            Todos os seus dados serão permanentemente removidos.
        </p>
        <button class="btn btn--danger" onclick="alert('Funcionalidade não disponível.')">
            Eliminar Conta
        </button>
    </section>

</main>

<?php drawFooter('javascript/profile.js'); ?>