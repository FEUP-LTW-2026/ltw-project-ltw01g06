<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../templates/common.tpl.php');

  $session = new Session();

  drawHeader($session, 'Home');
?>

<section class="hero">
  <div class="hero-content">
    <h1>Bem-vindo ao Equilibrio Gym</h1>
    <p>Transforma o teu corpo. Transforma a tua vida.</p>
    <?php if (!$session->isLoggedIn()) { ?>
      <div class="hero-buttons">
        <a href="register.php" class="btn btn-primary btn-large">Comecar Agora</a>
        <a href="schedule.php" class="btn btn-secondary btn-large">Ver Horarios</a>
      </div>
    <?php } else { ?>
      <div class="hero-buttons">
        <a href="schedule.php" class="btn btn-primary btn-large">Ver Horarios</a>
        <a href="profile.php" class="btn btn-secondary btn-large">O Meu Perfil</a>
      </div>
    <?php } ?>
  </div>
</section>

<section class="features">
  <h2>Porque Escolher-nos?</h2>
  <div class="features-grid">
    <article class="feature-card">
      <i class="fas fa-dumbbell"></i>
      <h3>Equipamento Moderno</h3>
      <p>Dispomos dos melhores equipamentos do mercado para treinos eficazes.</p>
    </article>
    <article class="feature-card">
      <i class="fas fa-users"></i>
      <h3>Treinadores Certificados</h3>
      <p>Equipa de profissionais qualificados para te guiar no teu percurso.</p>
    </article>
    <article class="feature-card">
      <i class="fas fa-calendar-alt"></i>
      <h3>Aulas Variadas</h3>
      <p>Grande variedade de aulas de grupo para todos os niveis e gostos.</p>
    </article>
    <article class="feature-card">
      <i class="fas fa-clock"></i>
      <h3>Horarios Flexiveis</h3>
      <p>Abertos 7 dias por semana com horarios alargados.</p>
    </article>
  </div>
</section>

<section class="cta">
  <h2>Pronto para Comecar?</h2>
  <p>Junta-te a nos e descobre o melhor de ti.</p>
  <?php if (!$session->isLoggedIn()) { ?>
    <a href="register.php" class="btn btn-primary btn-large">Registar Agora</a>
  <?php } else { ?>
    <a href="schedule.php" class="btn btn-primary btn-large">Explorar Aulas</a>
  <?php } ?>
</section>

<?php
  drawFooter();
?>
