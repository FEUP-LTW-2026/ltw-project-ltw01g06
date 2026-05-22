<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/class.class.php');
  require_once(__DIR__ . '/../templates/common.tpl.php');
  require_once(__DIR__ . '/../templates/class.tpl.php');

  $session = new Session();
  $db = getDatabaseConnection();

  $classes = GymClass::getAllClasses($db);

  drawHeader($session, 'Horarios');
?>

<section class="page-header">
  <h1><i class="fas fa-calendar-alt"></i> Horario de Aulas</h1>
  <p>Descobre todas as nossas aulas e inscreve-te nas tuas favoritas.</p>
</section>

<section class="schedule-section">
  <?php drawScheduleTable($classes, $session, $db); ?>
</section>

<?php if (!$session->isLoggedIn()) { ?>
<section class="login-prompt">
  <p><i class="fas fa-info-circle"></i> Para te inscreveres numa aula, precisas de ter conta.</p>
  <a href="register.php" class="btn btn-primary">Criar Conta</a>
  <a href="login.php" class="btn btn-secondary">Entrar</a>
</section>
<?php } ?>

<?php
  drawFooter();
?>
