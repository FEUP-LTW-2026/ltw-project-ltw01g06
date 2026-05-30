<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/session.php');
require_once(__DIR__ . '/../utils/csrf.php');

$session = new Session();

if ($session->isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$messages = $session->getMessages();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Equilibrio — Entrar</title>
    <link rel="stylesheet" href="../css/reset.css" />
    <link rel="stylesheet" href="../css/components.css" />
    <link rel="stylesheet" href="../css/pages/auth.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="auth-page">

<main class="auth-container">

    <a href="../index.php" class="back-link">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M19 12H5M12 19l-7-7 7-7"/>
        </svg>
        Voltar à página inicial
    </a>

    <?php if (!empty($messages)): ?>
        <?php foreach ($messages as $msg): ?>
            <div style="width:100%;padding:.75rem 1rem;border-radius:8px;font-size:.875rem;
                background:<?= $msg['type'] === 'success' ? '#d4edda' : '#f8d7da' ?>;
                color:<?= $msg['type'] === 'success' ? '#2d6a4f' : '#d62828' ?>;
                border-left:4px solid <?= $msg['type'] === 'success' ? '#2d6a4f' : '#d62828' ?>;">
                <?= htmlspecialchars($msg['text']) ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <header class="brand">
        <div class="brand__logo" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M6 4v6M6 14v6M18 4v6M18 14v6M3 7h6M15 7h6M3 17h6M15 17h6"/>
            </svg>
        </div>
        <h1 class="brand__name">Equilibrio</h1>
        <p class="brand__tagline">A sua jornada para uma vida mais saudável começa aqui</p>
    </header>

    <nav class="auth-tabs" role="tablist" aria-label="Opções de autenticação">
        <button class="auth-tabs__tab auth-tabs__tab--active"
                role="tab" aria-selected="true" data-tab="login">
            Entrar
        </button>
        <button class="auth-tabs__tab"
                role="tab" aria-selected="false" data-tab="register">
            Registar
        </button>
    </nav>

    <!-- Login Panel -->
    <section class="auth-card" id="panel-login" role="tabpanel">
        <div class="auth-card__header">
            <h2 class="auth-card__title">Bem-vindo de volta</h2>
            <p class="auth-card__subtitle">Inicie sessão na sua conta para continuar</p>
        </div>

        <form class="form" method="post" action="../actions/action_login.php">
                <?= '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($session->getCsrfToken()) . '" />' ?>
            <div class="form__group">
                <label class="form__label" for="login-field">Email ou Username</label>
                <input class="form__input" type="text" id="login-field" name="login"
                       placeholder="email@exemplo.com ou username" autocomplete="username" required />
            </div>

            <div class="form__group">
                <label class="form__label" for="login-password">Palavra-passe</label>
                <input class="form__input" type="password" id="login-password" name="password"
                       placeholder="••••••••" autocomplete="current-password" required />
            </div>

            <button class="btn btn--primary btn--full" type="submit">Entrar</button>
        </form>
    </section>

    <!-- Register Panel -->
    <section class="auth-card auth-card--hidden" id="panel-register" role="tabpanel">
        <div class="auth-card__header">
            <h2 class="auth-card__title">Criar Conta</h2>
            <p class="auth-card__subtitle">Junte-se à nossa comunidade fitness hoje</p>
        </div>

        <form class="form" method="post" action="../actions/action_register.php">
                <?= '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($session->getCsrfToken()) . '" />' ?>
            <div class="form__group">
                <label class="form__label" for="reg-name">Nome Completo</label>
                <input class="form__input" type="text" id="reg-name" name="full_name"
                       placeholder="João Silva" autocomplete="name" required />
            </div>
            <div class="form__group">
                <label class="form__label" for="reg-username">Username</label>
                <input class="form__input" type="text" id="reg-username" name="username"
                       placeholder="joaosilva" autocomplete="username" required />
            </div>
            <div class="form__group">
                <label class="form__label" for="reg-email">Email</label>
                <input class="form__input" type="email" id="reg-email" name="email"
                       placeholder="o-seu-email@exemplo.com" autocomplete="email" required />
            </div>
            <div class="form__group">
                <label class="form__label" for="reg-password">Palavra-passe</label>
                <input class="form__input" type="password" id="reg-password" name="password"
                       placeholder="••••••••" minlength="8" autocomplete="new-password" required />
            </div>
            <div class="form__group">
                <label class="form__label" for="reg-confirm">Confirmar Palavra-passe</label>
                <input class="form__input" type="password" id="reg-confirm" name="confirm_password"
                       placeholder="••••••••" autocomplete="new-password" required />
            </div>
            <button class="btn btn--primary btn--full" type="submit">Criar Conta</button>
        </form>
    </section>
</main>

<button class="help-btn" aria-label="Ajuda">?</button>

<script src="../javascript/auth.js" defer></script>
</body>
</html>