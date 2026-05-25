<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/session.php');
require_once(__DIR__ . '/../database/connection.db.php');
require_once(__DIR__ . '/../database/user.class.php');
require_once(__DIR__ . '/../database/enrollment.class.php');
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

// Iniciais para o avatar
$parts    = explode(' ', $user->fullName);
$initials = implode('', array_map(fn($p) => strtoupper(mb_substr($p, 0, 1)), $parts));
$initials = mb_substr($initials, 0, 2);

drawHeader($session, 'Editar Perfil', 'javascript/profile.js', 'css/pages/profile.css');
?>

<main class="profile-container">
    <header class="profile-header">
        <h1 class="profile-header__title">Editar Perfil</h1>
        <p class="profile-header__subtitle">Atualize as suas informações pessoais</p>
    </header>

    <!-- Avatar -->
    <section class="avatar-upload">
        <div class="avatar-upload__preview">
            <span><?= htmlspecialchars($initials) ?></span>
        </div>
        <div class="avatar-upload__info">
            <h3 class="avatar-upload__title">Foto de Perfil</h3>
            <p class="avatar-upload__desc">JPG, PNG ou GIF. Tamanho máximo de 2MB.</p>
            <div class="avatar-upload__actions">
                <button class="btn btn--secondary" disabled>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12"/>
                    </svg>
                    Carregar
                </button>
            </div>
        </div>
    </section>

    <!-- Formulário de perfil -->
    <form class="profile-form" method="post" action="../actions/action_edit_profile.php">

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
                    <textarea class="profile-form__input profile-form__textarea" id="bio" name="bio"
                              placeholder="Conte-nos um pouco sobre si..."><?= htmlspecialchars($user->bio ?? '') ?></textarea>
                </div>
            </div>
        </section>

        <!-- Alterar Password -->
        <section class="profile-form__section">
            <h2 class="profile-form__section-title">Alterar Palavra-passe</h2>
            <div class="profile-form__grid">
                <div class="profile-form__group profile-form__group--full">
                    <label class="profile-form__label" for="current-password">Palavra-passe Atual</label>
                    <input class="profile-form__input" type="password" id="current-password"
                           name="current_password" placeholder="Necessária para alterar a palavra-passe" />
                </div>
                <div class="profile-form__group">
                    <label class="profile-form__label" for="new-password">Nova Palavra-passe</label>
                    <input class="profile-form__input" type="password" id="new-password"
                           name="new_password" placeholder="Mínimo 8 caracteres" minlength="8" />
                </div>
                <div class="profile-form__group">
                    <label class="profile-form__label" for="confirm-password">Confirmar Palavra-passe</label>
                    <input class="profile-form__input" type="password" id="confirm-password"
                           name="confirm_password" placeholder="Repita a nova palavra-passe" />
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
    <section class="profile-form__section" style="margin-top:var(--space-2xl);">
        <h2 class="profile-form__section-title">As Minhas Inscrições</h2>
        <div style="display:flex;flex-direction:column;gap:.75rem;">
            <?php foreach ($enrollments as $enrollment): ?>
                <div style="display:flex;align-items:center;justify-content:space-between;
                    padding:1rem 1.25rem;background:var(--color-surface);
                    border:1px solid var(--color-border);border-radius:var(--radius-md);">
                    <span style="font-weight:500;"><?= htmlspecialchars($enrollment->className ?? 'Aula') ?></span>
                    <form method="post" action="../actions/action_enroll.php">
                        <input type="hidden" name="class_id" value="<?= $enrollment->classId ?>">
                        <input type="hidden" name="action" value="cancel">
                        <button type="submit" class="btn btn--ghost"
                                onclick="return confirm('Cancelar inscrição?')"
                                style="color:var(--color-error,#dc2626);">
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
        <h3 class="danger-zone__title">Zona de Perigo</h3>
        <p class="danger-zone__desc">Uma vez eliminada, a sua conta não pode ser recuperada. Todos os seus dados serão permanentemente removidos.</p>
        <button class="btn btn--danger" onclick="alert('Funcionalidade não disponível.')">
            Eliminar Conta
        </button>
    </section>
</main>


<?php drawFooter(); ?>