<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/trainer.class.php');
  require_once(__DIR__ . '/../templates/common.tpl.php');
  require_once(__DIR__ . '/../templates/trainer.tpl.php');

  $session = new Session();
  $db = getDatabaseConnection();

  $trainers = TrainerProfile::getAllProfiles($db);

  drawHeader($session, 'Treinadores');
?>

<section class="page-header">
  <h1><i class="fas fa-users"></i> Os Nossos Treinadores</h1>
  <p>Conhece a equipa de profissionais que te vai ajudar a atingir os teus objetivos.</p>
</section>

<section class="trainers-section">
  <?php drawTrainersList($trainers); ?>
</section>

<?php
  drawFooter();
?>
