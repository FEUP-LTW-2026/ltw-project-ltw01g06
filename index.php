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
        <h1 class="hero__title">
        Encontre o seu <span>Equilíbrio</span> perfeito
        </h1>
        <p class="hero__subtitle">
            Transforme a sua vida com aulas de fitness personalizadas, treinadores especializados
            e um ambiente motivador. A sua jornada para uma vida mais saudável começa aqui.
        </p>
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
                <h3 class="feature-card__title">Reserva de Aulas</h3>
                <p class="feature-card__desc">
                    Reserve as suas aulas favoritas com apenas alguns cliques. Veja horários,
                    treinadores e vagas disponíveis em tempo real.
                </p>
            </div>
            <div class="feature-card">
                <h3 class="feature-card__title">Treinadores Certificados</h3>
                <p class="feature-card__desc">
                    A nossa equipa de treinadores certificados está pronta para o ajudar
                    a alcançar os seus objetivos de forma segura e eficaz.
                </p>
            </div>
            <div class="feature-card">
                <h3 class="feature-card__title">Equipamento Moderno</h3>
                <p class="feature-card__desc">
                    Acesso a equipamentos de última geração para cardio, musculação
                    e treino funcional. Sempre limpo e bem mantido.
                </p>
            </div>
            <div class="feature-card">
                <h3 class="feature-card__title">Acompanhamento de Progresso</h3>
                <p class="feature-card__desc">
                    Monitore a sua evolução com estatísticas detalhadas de aulas,
                    streak de dias ativos e objetivos alcançados.
                </p>
            </div>
            <div class="feature-card">
                <h3 class="feature-card__title">Comunidade Motivadora</h3>
                <p class="feature-card__desc">
                    Faça parte de uma comunidade que se apoia mutuamente.
                    Partilhe conquistas e motive-se com outros membros.
                </p>
            </div>
            <div class="feature-card">
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
            $typeKey = strtolower(str_replace(' ', '', $class->type));
            $iconPath = $icons[$typeKey] ?? $icons['default'];
            ?>
                <article class="class-card">
                    <div class="class-card__image">
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
                        <?php
                        $tPhoto = $trainer->photo ?? null;
                        $tHasPhoto = $tPhoto && $tPhoto !== 'default_profile.png'
                            && file_exists(__DIR__ . '/docs/uploads/' . $tPhoto);
                        ?>
                        <?php if ($tHasPhoto): ?>
                            <div class="trainer-card__avatar" style="overflow:hidden;padding:0">
                                <img src="docs/uploads/<?= htmlspecialchars($tPhoto) ?>"
                                     alt="<?= htmlspecialchars($trainer->fullName ?? '') ?>"
                                     style="width:100%;height:100%;object-fit:cover;border-radius:50%" />
                            </div>
                        <?php else: ?>
                            <div class="trainer-card__avatar"><?= htmlspecialchars($initials) ?></div>
                        <?php endif; ?>
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
                    <a href="pages/login.php#register" class="btn btn--primary btn--large">
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