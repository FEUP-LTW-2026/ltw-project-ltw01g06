<?php
declare(strict_types=1);

require_once(__DIR__ . '/utils/session.php');
require_once(__DIR__ . '/templates/common.tpl.php');

$session = new Session();

drawHeader($session, 'Fitness & Wellness', '/css/pages/home.css');
?>

<section class="hero">
    <div class="hero__container">
        <div class="hero__content">
            <div class="hero__badge">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
                </svg>
                Bem-vindo ao Equilibrio Gym
            </div>
            <h1 class="hero__title">
                Encontre o seu <span>Equilíbrio</span> perfeito
            </h1>
            <p class="hero__subtitle">
                Transforme a sua vida com aulas de fitness personalizadas, treinadores especializados
                e um ambiente motivador. A sua jornada para uma vida mais saudável começa aqui.
            </p>
            <div class="hero__actions">
                <?php if (!$session->isLoggedIn()): ?>
                    <a href="/pages/register.php" class="btn btn--primary btn--large">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                        Começar Agora
                    </a>
                    <a href="/pages/schedule.php" class="btn btn--ghost btn--large">Ver Horário</a>
                <?php else: ?>
                    <a href="/pages/dashboard.php" class="btn btn--primary btn--large">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                        Ir para o Dashboard
                    </a>
                    <a href="/pages/schedule.php" class="btn btn--ghost btn--large">Ver Horário</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php drawFooter(); ?>