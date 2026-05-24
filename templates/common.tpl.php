<?php
declare(strict_types=1);

function drawHeader(object $session, string $title = 'Equilibrio', string $extraCss = ''): void {
    $messages = $session->getMessages();
    $isPages  = str_contains($_SERVER['SCRIPT_FILENAME'] ?? '', '/pages/');
    $root     = $isPages ? '../' : '';
    ?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Equilibrio — <?= htmlspecialchars($title) ?></title>
    <link rel="stylesheet" href="<?= $root ?>css/reset.css" />
    <link rel="stylesheet" href="<?= $root ?>css/components.css" />
    <?php if ($extraCss): ?>
    <link rel="stylesheet" href="<?= htmlspecialchars($extraCss) ?>" />
    <?php endif; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<nav class="navbar">
    <a href="/index.php" class="navbar__brand">
        <div class="navbar__logo">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M6 4v6M6 14v6M18 4v6M18 14v6M3 7h6M15 7h6M3 17h6M15 17h6"/>
            </svg>
        </div>
        <span>Equilibrio</span>
    </a>

    <div class="navbar__links">
        <a href="/pages/schedule.php" class="navbar__link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2"/>
                <path d="M16 2v4M8 2v4M3 10h18"/>
            </svg>
            Horário
        </a>
        <a href="/pages/trainers.php" class="navbar__link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            Treinadores
        </a>
        <a href="/pages/equipment.php" class="navbar__link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M6 4v6M6 14v6M18 4v6M18 14v6M3 7h6M15 7h6M3 17h6M15 17h6"/>
            </svg>
            Equipamento
        </a>
        <?php if ($session->isLoggedIn()): ?>
            <a href="/pages/dashboard.php" class="navbar__link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/>
                    <rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/>
                </svg>
                Dashboard
            </a>
            <?php if ($session->isAdmin()): ?>
                <a href="/pages/admin.php" class="navbar__link">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="3"/>
                        <path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83"/>
                    </svg>
                    Admin
                </a>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <div class="navbar__actions">
        <?php if ($session->isLoggedIn()): ?>
            <a href="/pages/profile.php" class="btn btn--ghost">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                <?= htmlspecialchars($session->getName()) ?>
            </a>
            <form action="/actions/action_logout.php" method="post" style="display:inline">
                <button type="submit" class="btn btn--secondary">Sair</button>
            </form>
        <?php else: ?>
            <a href="/pages/login.php" class="btn btn--ghost">Entrar</a>
            <a href="/pages/register.php" class="btn btn--primary">Começar Agora</a>
        <?php endif; ?>
    </div>
</nav>

<?php if (!empty($messages)): ?>
    <div style="max-width:1200px;margin:80px auto 0;padding:0 var(--space-xl);">
        <?php foreach ($messages as $msg): ?>
            <div style="display:flex;align-items:center;gap:.75rem;padding:.875rem 1.25rem;
                margin-bottom:.75rem;border-radius:var(--radius-md);font-size:.875rem;font-weight:500;
                background:<?= $msg['type'] === 'success' ? '#f0fdf4' : '#fef2f2' ?>;
                color:<?= $msg['type'] === 'success' ? '#166534' : '#991b1b' ?>;
                border:1px solid <?= $msg['type'] === 'success' ? '#bbf7d0' : '#fecaca' ?>;">
                <?= htmlspecialchars($msg['text']) ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div style="padding-top: 70px;">
    <?php
}

function drawFooter(): void {
    ?>
</div><!-- end padding-top wrapper -->

<footer class="footer">
    <div class="footer__inner">
        <div class="footer__top">
            <div class="footer__brand">
                <div class="footer__brand-header">
                    <div class="footer__logo">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M6 4v6M6 14v6M18 4v6M18 14v6M3 7h6M15 7h6M3 17h6M15 17h6"/>
                        </svg>
                    </div>
                    <span class="footer__name">Equilibrio</span>
                </div>
                <p class="footer__desc">
                    A sua jornada para uma vida mais saudável e equilibrada começa aqui.
                    Fitness, bem-estar e comunidade.
                </p>
            </div>
            <div class="footer__column">
                <h4>Navegação</h4>
                <ul class="footer__links">
                    <li><a href="/pages/schedule.php" class="footer__link">Horário</a></li>
                    <li><a href="/pages/trainers.php" class="footer__link">Treinadores</a></li>
                    <li><a href="/pages/equipment.php" class="footer__link">Equipamento</a></li>
                    <li><a href="/pages/login.php" class="footer__link">Entrar</a></li>
                </ul>
            </div>
            <div class="footer__column">
                <h4>Aulas</h4>
                <ul class="footer__links">
                    <li><a href="/pages/schedule.php" class="footer__link">Yoga</a></li>
                    <li><a href="/pages/schedule.php" class="footer__link">Spinning</a></li>
                    <li><a href="/pages/schedule.php" class="footer__link">HIIT</a></li>
                    <li><a href="/pages/schedule.php" class="footer__link">Pilates</a></li>
                </ul>
            </div>
            <div class="footer__column">
                <h4>Contacto</h4>
                <ul class="footer__links">
                    <li><a href="mailto:info@equilibrio.pt" class="footer__link">info@equilibrio.pt</a></li>
                    <li><a href="tel:+351912345678" class="footer__link">+351 912 345 678</a></li>
                    <li><span class="footer__link">Lisboa, Portugal</span></li>
                </ul>
            </div>
        </div>
        <div class="footer__bottom">
            <p class="footer__copy">&copy; <?= date('Y') ?> Equilibrio. Todos os direitos reservados.</p>
            <div class="footer__socials">
                <a href="#" class="footer__social" aria-label="Instagram">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="2" width="20" height="20" rx="5"/>
                        <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/>
                        <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/>
                    </svg>
                </a>
                <a href="#" class="footer__social" aria-label="Facebook">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</footer>

<script src="/javascript/script.js"></script>
</body>
</html>
    <?php
}
?>