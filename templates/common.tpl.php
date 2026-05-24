<?php
declare(strict_types=1);

function drawHeader(object $session, string $title = 'Equilibrio Gym'): void {
    $messages = $session->getMessages();
    ?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($title) ?> — Equilibrio Gym</title>
    <link rel="stylesheet" href="/css/reset.css" />
    <link rel="stylesheet" href="/css/style.css" />
    <link rel="stylesheet" href="/css/components.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body>

<header class="main-header">
    <div class="header-container">
        <div class="logo">
            <a href="/index.php">
                <i class="fas fa-dumbbell"></i>
                Equilibrio
            </a>
        </div>

        <nav class="main-nav">
            <a href="/pages/schedule.php">Horário</a>
            <a href="/pages/trainers.php">Treinadores</a>
            <?php if ($session->isLoggedIn()): ?>
                <a href="/pages/dashboard.php">Dashboard</a>
                <?php if ($session->isAdmin()): ?>
                    <a href="/pages/admin.php">Admin</a>
                <?php endif; ?>
            <?php endif; ?>
        </nav>

        <div class="auth-section">
            <?php if ($session->isLoggedIn()): ?>
                <div class="user-menu">
                    <span class="user-name">
                        <i class="fas fa-user-circle"></i>
                        <?= htmlspecialchars($session->getName()) ?>
                    </span>
                    <a href="/pages/profile.php" class="btn btn-secondary btn-small">Perfil</a>
                    <form action="/actions/action_logout.php" method="post" style="display:inline">
                        <button type="submit" class="btn btn-outline btn-small">Sair</button>
                    </form>
                </div>
            <?php else: ?>
                <a href="/pages/login.php" class="btn btn-outline btn-small">Entrar</a>
                <a href="/pages/register.php" class="btn btn-primary btn-small">Registar</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<?php if (!empty($messages)): ?>
    <div id="messages">
        <?php foreach ($messages as $msg): ?>
            <div class="message <?= htmlspecialchars($msg['type']) ?>">
                <i class="fas <?= $msg['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
                <?= htmlspecialchars($msg['text']) ?>
                <button class="close-message" data-close-message aria-label="Fechar">×</button>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<main>
    <?php
}

function drawFooter(): void {
    ?>
</main>

<footer class="main-footer">
    <div class="footer-container">
        <div class="footer-section">
            <h4><i class="fas fa-dumbbell"></i> Equilibrio Gym</h4>
            <p>A sua jornada para uma vida mais saudável começa aqui.</p>
        </div>
        <div class="footer-section">
            <h4>Links</h4>
            <p><a href="/pages/schedule.php" style="color:rgba(255,255,255,.8)">Horário de Aulas</a></p>
            <p><a href="/pages/trainers.php" style="color:rgba(255,255,255,.8)">Treinadores</a></p>
            <p><a href="/pages/register.php" style="color:rgba(255,255,255,.8)">Registar</a></p>
        </div>
        <div class="footer-section">
            <h4>Contacto</h4>
            <p><i class="fas fa-map-marker-alt"></i> Rua do Fitness, 123, Porto</p>
            <p><i class="fas fa-phone"></i> +351 220 000 000</p>
            <p><i class="fas fa-envelope"></i> info@equilibrio.pt</p>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> Equilibrio Gym. Todos os direitos reservados.</p>
    </div>
</footer>

<script src="/javascript/script.js"></script>
</body>
</html>
    <?php
}
?>
