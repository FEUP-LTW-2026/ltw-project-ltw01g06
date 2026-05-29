<?php
declare(strict_types=1);

/**
 * common.tpl.php
 * drawHeader() — gera <head>, navbar e mensagens flash.
 * drawFooter() — fecha wrapper, gera footer, carrega scripts JS.
 *
 * @param object $session
 * @param string $title      Título da página
 * @param string $pageJs     Caminho relativo ao JS específico (ex: 'javascript/schedule.js')
 * @param string $pageCss    Caminho relativo ao CSS específico (ex: 'css/pages/schedule.css')
 * @param bool   $homeLinks  Se true, navbar mostra âncoras de scroll em vez de links de páginas
 */
function drawHeader(
    object $session,
    string $title    = 'Equilibrio',
    string $pageJs   = '',
    string $pageCss  = '',
    bool   $homeLinks = false
): void {
    $messages = $session->getMessages();
    $inPages  = str_contains(str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME'] ?? ''), '/pages/');
    $root     = $inPages ? '../' : '';
    $currentPage = basename($_SERVER['SCRIPT_FILENAME'] ?? '');
    ?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Equilibrio — <?= htmlspecialchars($title) ?></title>
    <link rel="stylesheet" href="<?= $root ?>css/reset.css" />
    <link rel="stylesheet" href="<?= $root ?>css/components.css" />
    <?php if ($pageCss): ?>
    <link rel="stylesheet" href="<?= $root ?><?= htmlspecialchars(ltrim($pageCss, '/')) ?>" />
    <?php endif; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" />
</head>
<body>

<nav class="navbar">
    <a href="<?= $root ?>index.php" class="navbar__brand">
        <div class="navbar__logo">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M6 4v6M6 14v6M18 4v6M18 14v6M3 7h6M15 7h6M3 17h6M15 17h6"/>
            </svg>
        </div>
        <span>Equilibrio</span>
        <?php if ($session->isAdmin()): ?>
            <span class="tag tag--advanced" style="margin-left:8px">Admin</span>
        <?php endif; ?>
    </a>
    
    <ul class="navbar__links">
        <?php if ($homeLinks): ?>
            <li><a href="#features" class="navbar__link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                </svg>
                Funcionalidades
            </a></li>
            <li><a href="#classes" class="navbar__link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect x="3" y="4" width="18" height="18" rx="2"/>
                    <path d="M3 9h18M8 4v5M16 4v5"/>
                </svg>
                Aulas
            </a></li>
            <li><a href="#trainers" class="navbar__link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="12" cy="8" r="4"/>
                    <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                </svg>
                Treinadores
            </a></li>
        <?php else: ?>
            <li><a href="<?= $root ?>pages/schedule.php"
                   class="navbar__link <?= $currentPage === 'schedule.php' ? 'navbar__link--active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2"/>
                    <path d="M16 2v4M8 2v4M3 10h18"/>
                </svg>
                Horário
            </a></li>
            <li><a href="<?= $root ?>pages/trainers.php"
                   class="navbar__link <?= $currentPage === 'trainers.php' ? 'navbar__link--active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                Treinadores
            </a></li>
            <li><a href="<?= $root ?>pages/equipment.php"
                   class="navbar__link <?= $currentPage === 'equipment.php' ? 'navbar__link--active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M6 4v6M6 14v6M18 4v6M18 14v6M3 7h6M15 7h6M3 17h6M15 17h6"/>
                </svg>
                Equipamento
            </a></li>
            <?php if ($session->isLoggedIn()): ?>
                <li><a href="<?= $root ?>pages/dashboard.php"
                       class="navbar__link <?= $currentPage === 'dashboard.php' ? 'navbar__link--active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="9"/>
                        <rect x="14" y="3" width="7" height="5"/>
                        <rect x="14" y="12" width="7" height="9"/>
                        <rect x="3" y="16" width="7" height="5"/>
                    </svg>
                    Dashboard
                </a></li>
            <?php endif; ?>
        <?php endif; ?>
    </ul>

    <div class="navbar__actions">
        <?php if ($session->isAdmin() && !$homeLinks): ?>
            <a href="<?= $root ?>pages/admin.php" class="btn btn--ghost">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                    <rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/>
                    <rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/>
                </svg>
                Admin
            </a>
            <form action="<?= $root ?>actions/action_logout.php" method="post" style="display:inline">
                <button type="submit" class="btn btn--secondary">Sair</button>
            </form>
        <?php elseif ($session->isLoggedIn()): ?>
            <?php
            // Buscar foto diretamente da BD — não depende da sessão
            $navPhoto = null;
            if ($session->getId()) {
                try {
                    $navDb   = getDatabaseConnection();
                    $navStmt = $navDb->prepare('SELECT photo FROM users WHERE id = ?');
                    $navStmt->execute([$session->getId()]);
                    $navRow  = $navStmt->fetch();
                    $navPhotoFile = $navRow['photo'] ?? null;
                    if ($navPhotoFile && $navPhotoFile !== 'default_profile.png') {
                        $navPhoto = $root . 'docs/uploads/' . htmlspecialchars($navPhotoFile);
                    }
                } catch (\Exception $e) {
                    // Silencioso — mostra ícone por defeito
                }
            }
            ?>
            <a href="<?= $root ?>pages/profile.php" class="btn btn--ghost"
               style="display:flex;align-items:center;gap:.5rem">
                <?php if ($navPhoto): ?>
                    <img src="<?= $navPhoto ?>" alt="Foto de perfil"
                         style="width:28px;height:28px;border-radius:50%;object-fit:cover;" />
                <?php else: ?>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                         width="18" height="18">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                <?php endif; ?>
                <?= htmlspecialchars($session->getName()) ?>
            </a>
            <form action="<?= $root ?>actions/action_logout.php" method="post" style="display:inline">
                <button type="submit" class="btn btn--secondary">Sair</button>
            </form>
        <?php else: ?>
            <a href="<?= $root ?>pages/login.php" class="btn btn--ghost">Entrar</a>
            <a href="<?= $root ?>pages/login.php#register" class="btn btn--primary">Começar Agora</a>
        <?php endif; ?>
    </div>
</nav>

<?php if (!empty($messages)): ?>
    <div id="flash-messages" style="max-width:1200px;margin:80px auto 0;padding:0 var(--space-xl);">
        <?php foreach ($messages as $msg): ?>
            <div class="message" style="display:flex;align-items:center;gap:.75rem;
                padding:.875rem 1.25rem;margin-bottom:.75rem;border-radius:var(--radius-md);
                font-size:.875rem;font-weight:500;
                background:<?= $msg['type'] === 'success' ? '#f0fdf4' : '#fef2f2' ?>;
                color:<?= $msg['type'] === 'success' ? '#166534' : '#991b1b' ?>;
                border:1px solid <?= $msg['type'] === 'success' ? '#bbf7d0' : '#fecaca' ?>;">
                <?= htmlspecialchars($msg['text']) ?>
                <button data-close-message aria-label="Fechar"
                        style="margin-left:auto;background:none;border:none;
                               cursor:pointer;font-size:1.25rem;line-height:1;
                               color:inherit;opacity:.6;">×</button>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div style="padding-top:70px">
<?php
}

function drawFooter(string $pageJs = ''): void {
    $inPages = str_contains(str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME'] ?? ''), '/pages/');
    $root    = $inPages ? '../' : '';
    ?>
</div>

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
                    <li><a href="<?= $root ?>pages/schedule.php"  class="footer__link">Horário</a></li>
                    <li><a href="<?= $root ?>pages/trainers.php"  class="footer__link">Treinadores</a></li>
                    <li><a href="<?= $root ?>pages/equipment.php" class="footer__link">Equipamento</a></li>
                    <li><a href="<?= $root ?>pages/login.php"     class="footer__link">Entrar</a></li>
                </ul>
            </div>
            <div class="footer__column">
    <h4>Aulas</h4>
    <ul class="footer__links">
        <li><a href="<?= $root ?>pages/schedule.php?type=yoga" class="footer__link">Yoga</a></li>
        <li><a href="<?= $root ?>pages/schedule.php?type=spinning" class="footer__link">Spinning</a></li>
        <li><a href="<?= $root ?>pages/schedule.php?type=hiit" class="footer__link">HIIT</a></li>
        <li><a href="<?= $root ?>pages/schedule.php?type=pilates" class="footer__link">Pilates</a></li>
    </ul>
</div>
            <div class="footer__column">
                <h4>Contacto</h4>
                <ul class="footer__links">
                    <li><a href="mailto:info@equilibrio.pt" class="footer__link">info@equilibrio.pt</a></li>
                    <li><a href="tel:+351912345678"         class="footer__link">+351 912 345 678</a></li>
                    <li><span class="footer__link">Lisboa, Portugal</span></li>
                </ul>
            </div>
        </div>
        <div class="footer__bottom">
            <p class="footer__copy">&copy; <?= date('Y') ?> Equilibrio. Todos os direitos reservados.</p>
        </div>
    </div>
</footer>

<script src="<?= $root ?>javascript/script.js"></script>
<?php if ($pageJs): ?>
<script src="<?= $root ?><?= htmlspecialchars(ltrim($pageJs, '/')) ?>"></script>
<?php endif; ?>
</body>
</html>
<?php
}