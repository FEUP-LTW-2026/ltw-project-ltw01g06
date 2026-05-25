<?php
declare(strict_types=1);

require_once(__DIR__ . '/utils/session.php');
require_once(__DIR__ . '/database/connection.db.php');
require_once(__DIR__ . '/database/class.class.php');
require_once(__DIR__ . '/database/trainer.class.php');
require_once(__DIR__ . '/templates/common.tpl.php');

$session  = new Session();
$db       = getDatabaseConnection();
$classes  = array_slice(GymClass::getAllClasses($db), 0, 4);
$trainers = TrainerProfile::getAllProfiles($db);
$total    = count(GymClass::getAllClasses($db));

drawHeader($session, 'Fitness & Wellness', '', 'css/pages/home.css', true);
?>

<!-- ── Hero ── -->
<section class="hero">
    <div class="hero__container">
        <div class="hero__content">
            <div class="hero__badge">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
                </svg>
                Novo: Aulas de Yoga ao ar livre
            </div>
            <h1 class="hero__title">
                Encontre o seu <span>Equilíbrio</span> perfeito
            </h1>
            <p class="hero__subtitle">
                Transforme a sua vida com aulas de fitness personalizadas, treinadores especializados
                e um ambiente motivador. A sua jornada para uma vida mais saudável começa aqui.
            </p>
            <div class="hero__actions">
                <?php if ($session->isLoggedIn()): ?>
                    <a href="pages/schedule.php" class="btn btn--primary btn--large">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                        Ver Horário
                    </a>
                    <a href="pages/dashboard.php" class="btn btn--secondary btn--large">Dashboard</a>
                <?php else: ?>
                    <a href="pages/login.php" class="btn btn--primary btn--large">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                        Começar Agora
                    </a>
                    <a href="#features" class="btn btn--secondary btn--large">Saber Mais</a>
                <?php endif; ?>
            </div>
            <div class="hero__stats">
                <div class="hero__stat">
                    <span class="hero__stat-value">500+</span>
                    <span class="hero__stat-label">Membros Ativos</span>
                </div>
                <div class="hero__stat">
                    <span class="hero__stat-value"><?= $total ?>+</span>
                    <span class="hero__stat-label">Aulas Semanais</span>
                </div>
                <div class="hero__stat">
                    <span class="hero__stat-value"><?= count($trainers) ?></span>
                    <span class="hero__stat-label">Treinadores Expert</span>
                </div>
            </div>
        </div>
        <div class="hero__visual">
            <div class="hero__image-container">
                <div class="hero__image" aria-label="Pessoa a fazer exercício"></div>
            </div>
            <div class="hero__card hero__card--classes">
                <div class="hero__card-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2"/>
                        <path d="M3 9h18M8 4v5M16 4v5"/>
                    </svg>
                </div>
                <div class="hero__card-text">
                    <span class="hero__card-value"><?= $total ?></span>
                    <span class="hero__card-label">Aulas Hoje</span>
                </div>
            </div>
            <div class="hero__card hero__card--streak">
                <div class="hero__card-icon" style="margin:0 auto var(--space-sm)">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
                    </svg>
                </div>
                <span class="hero__card-value">7 dias</span>
                <span class="hero__card-label">Streak médio</span>
            </div>
        </div>
    </div>
</section>

<!-- ── Features ── -->
<section class="features" id="features">
    <div class="features__container">
        <header class="section-header">
            <span class="section-header__badge">Funcionalidades</span>
            <h2 class="section-header__title">Tudo o que precisa num só lugar</h2>
            <p class="section-header__subtitle">
                O Equilibrio oferece uma experiência completa de fitness, desde a reserva de aulas
                até ao acompanhamento do seu progresso.
            </p>
        </header>
        <div class="features__grid">
            <div class="feature-card">
                <div class="feature-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2"/>
                        <path d="M3 9h18M8 4v5M16 4v5"/>
                    </svg>
                </div>
                <h3 class="feature-card__title">Reserva de Aulas</h3>
                <p class="feature-card__desc">
                    Reserve as suas aulas favoritas com apenas alguns cliques. Veja horários,
                    treinadores e vagas disponíveis em tempo real.
                </p>
            </div>
            <div class="feature-card">
                <div class="feature-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="8" r="4"/>
                        <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                    </svg>
                </div>
                <h3 class="feature-card__title">Treinadores Certificados</h3>
                <p class="feature-card__desc">
                    A nossa equipa de treinadores certificados está pronta para o ajudar
                    a alcançar os seus objetivos de forma segura e eficaz.
                </p>
            </div>
            <div class="feature-card">
                <div class="feature-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0
                                 l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91
                                 a2.12 2.12 0 0 1-3-3l6.91-6.91
                                 a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                    </svg>
                </div>
                <h3 class="feature-card__title">Equipamento Moderno</h3>
                <p class="feature-card__desc">
                    Acesso a equipamentos de última geração para cardio, musculação
                    e treino funcional. Sempre limpo e bem mantido.
                </p>
            </div>
            <div class="feature-card">
                <div class="feature-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
                        <polyline points="17 6 23 6 23 12"/>
                    </svg>
                </div>
                <h3 class="feature-card__title">Acompanhamento de Progresso</h3>
                <p class="feature-card__desc">
                    Monitore a sua evolução com estatísticas detalhadas de aulas,
                    streak de dias ativos e objetivos alcançados.
                </p>
            </div>
            <div class="feature-card">
                <div class="feature-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
                <h3 class="feature-card__title">Comunidade Motivadora</h3>
                <p class="feature-card__desc">
                    Faça parte de uma comunidade que se apoia mutuamente.
                    Partilhe conquistas e motive-se com outros membros.
                </p>
            </div>
            <div class="feature-card">
                <div class="feature-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="3" width="20" height="14" rx="2"/>
                        <path d="M8 21h8M12 17v4"/>
                    </svg>
                </div>
                <h3 class="feature-card__title">Dashboard Personalizado</h3>
                <p class="feature-card__desc">
                    Tenha uma visão completa das suas atividades, próximas aulas
                    e recomendações personalizadas no seu dashboard.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- ── Classes Preview (dados reais) ── -->
<section class="classes-preview" id="classes">
    <div class="classes-preview__container">
        <header class="section-header">
            <span class="section-header__badge">Aulas Populares</span>
            <h2 class="section-header__title">Experimente as nossas aulas</h2>
            <p class="section-header__subtitle">
                De Yoga relaxante a HIIT intenso, temos aulas para todos os níveis e objetivos.
            </p>
        </header>
        <div class="classes-preview__grid">
            <?php foreach ($classes as $class):
                $typeSlug   = strtolower(str_replace(' ', '-', $class->type));
                $levelLabel = match($class->level) {
                    'beginner'     => 'Iniciante',
                    'intermediate' => 'Intermédio',
                    'advanced'     => 'Avançado',
                    default        => ucfirst($class->level)
                };
            // Ícone SVG por tipo de aula
            $icons = [
                'yoga'     => '<path d="M12 2a4 4 0 1 0 0 8 4 4 0 0 0 0-8zm0 14c-4.418 0-8 1.79-8 4v1h16v-1c0-2.21-3.582-4-8-4z"/><path d="M6 12c0-3 2.686-5 6-5s6 2 6 5"/>',
                'hiit'     => '<path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>',
                'spinning' => '<circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2"/>',
                'pilates'  => '<ellipse cx="12" cy="12" rx="10" ry="4"/><path d="M12 8c-2 0-3.5.9-3.5 2S10 12 12 12s3.5-.9 3.5-2S14 8 12 8z"/>',
                'zumba'    => '<path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/>',
                'weights'  => '<path d="M6 4v6M6 14v6M18 4v6M18 14v6M3 7h6M15 7h6M3 17h6M15 17h6"/>',
                'default'  => '<path d="M6 4v6M6 14v6M18 4v6M18 14v6M3 7h6M15 7h6M3 17h6M15 17h6"/>',
            ];
            $typeKey = strtolower(str_replace(' ', '', $class->type));
            $iconPath = $icons[$typeKey] ?? $icons['default'];
            ?>
                <article class="class-card">
                    <div class="class-card__image">
                        <svg class="class-card__image-icon" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="1.2"
                             stroke-linecap="round" stroke-linejoin="round">
                            <?= $iconPath ?>
                        </svg>
                        <div class="class-card__image-tags">
                            <span class="tag tag--<?= $typeSlug ?>"><?= htmlspecialchars($class->type) ?></span>
                            <span class="tag tag--<?= htmlspecialchars($class->level) ?>"><?= $levelLabel ?></span>
                        </div>
                    </div>
                    <div class="class-card__content">
                        <h3 class="class-card__title"><?= htmlspecialchars($class->name) ?></h3>
                        <div class="class-card__meta">
                            <span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/>
                                    <polyline points="12 6 12 12 16 14"/>
                                </svg>
                                <?= htmlspecialchars($class->startTime) ?> – <?= htmlspecialchars($class->getEndTime()) ?>
                            </span>
                            <?php if ($class->trainerName): ?>
                                <span>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                        <circle cx="9" cy="7" r="4"/>
                                    </svg>
                                    <?= htmlspecialchars($class->trainerName) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ── Trainers (dados reais) ── -->
<section class="trainers" id="trainers">
    <div class="trainers__container">
        <header class="section-header">
            <span class="section-header__badge">A Nossa Equipa</span>
            <h2 class="section-header__title">Treinadores Especializados</h2>
            <p class="section-header__subtitle">
                Profissionais certificados e apaixonados por fitness, prontos para o guiar na sua jornada.
            </p>
        </header>
        <div class="trainers__grid">
            <?php foreach ($trainers as $trainer):
                $parts    = explode(' ', $trainer->fullName ?? '');
                $initials = mb_substr(
                    implode('', array_map(fn($p) => strtoupper(mb_substr($p, 0, 1)), $parts)),
                    0, 2
                );
                $specs = $trainer->getSpecializationsArray();
            ?>
                <div class="trainer-card">
                    <div class="trainer-card__image">
                        <div class="trainer-card__avatar"><?= htmlspecialchars($initials) ?></div>
                    </div>
                    <h3 class="trainer-card__name"><?= htmlspecialchars($trainer->fullName ?? '') ?></h3>
                    <?php if (!empty($specs)): ?>
                        <p class="trainer-card__specialty"><?= htmlspecialchars($specs[0]) ?></p>
                        <div class="trainer-card__tags">
                            <?php foreach (array_slice($specs, 0, 2) as $spec): ?>
                                <span class="tag tag--<?= htmlspecialchars(strtolower(str_replace(' ', '-', $spec))) ?>">
                                    <?= htmlspecialchars($spec) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif ($trainer->bio): ?>
                        <p class="trainer-card__specialty">
                            <?= htmlspecialchars(mb_substr($trainer->bio, 0, 50)) ?>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ── CTA ── -->
<section class="cta">
    <div class="cta__container">
        <div class="cta__card">
            <div class="cta__icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
                </svg>
            </div>
            <h2 class="cta__title">Pronto para começar?</h2>
            <p class="cta__subtitle">
                Junte-se a mais de 500 membros que já transformaram as suas vidas com o Equilibrio.
                A sua primeira semana é por nossa conta!
            </p>
            <div class="cta__actions">
                <?php if ($session->isLoggedIn()): ?>
                    <a href="pages/schedule.php" class="btn btn--primary btn--large">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2"/>
                            <path d="M3 9h18M8 4v5M16 4v5"/>
                        </svg>
                        Ver Horário de Aulas
                    </a>
                <?php else: ?>
                    <a href="pages/register.php" class="btn btn--primary btn--large">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="8.5" cy="7" r="4"/>
                            <path d="M20 8v6M23 11h-6"/>
                        </svg>
                        Criar Conta Grátis
                    </a>
                    <a href="#features" class="btn btn--secondary btn--large">Ver Funcionalidades</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php drawFooter(); ?>