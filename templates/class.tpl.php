<?php 
  declare(strict_types = 1);
?>

<?php function drawClassCard(GymClass $class, Session $session, PDO $db) { 
  require_once(__DIR__ . '/../database/enrollment.class.php');
  $isEnrolled = $session->isLoggedIn() ? Enrollment::isUserEnrolled($db, $session->getId(), $class->id) : false;
?>
  <article class="class-card <?= $class->difficulty ?>">
    <div class="class-header">
      <h3><?= htmlspecialchars($class->name) ?></h3>
      <span class="difficulty-badge"><?= htmlspecialchars(ucfirst($class->difficulty)) ?></span>
    </div>
    <div class="class-info">
      <p class="class-time">
        <i class="fas fa-clock"></i>
        <?= htmlspecialchars($class->startTime) ?> - <?= htmlspecialchars($class->endTime) ?>
      </p>
      <p class="class-day">
        <i class="fas fa-calendar"></i>
        <?= htmlspecialchars($class->dayOfWeek) ?>
      </p>
      <p class="class-trainer">
        <i class="fas fa-user"></i>
        <?= htmlspecialchars($class->trainerName ?? 'A definir') ?>
      </p>
      <p class="class-room">
        <i class="fas fa-door-open"></i>
        <?= htmlspecialchars($class->room) ?>
      </p>
      <p class="class-capacity">
        <i class="fas fa-users"></i>
        <?= $class->currentEnrollment ?>/<?= $class->maxCapacity ?> inscritos
      </p>
    </div>
    <?php if (!empty($class->description)) { ?>
      <p class="class-description"><?= htmlspecialchars($class->description) ?></p>
    <?php } ?>
    
    <?php if ($session->isLoggedIn() && $session->isMember()) { ?>
      <form action="../actions/action_enroll.php" method="post" class="enroll-form">
        <input type="hidden" name="class_id" value="<?= $class->id ?>">
        <?php if ($isEnrolled) { ?>
          <input type="hidden" name="action" value="cancel">
          <button type="submit" class="btn btn-danger">
            <i class="fas fa-times"></i> Cancelar Inscricao
          </button>
        <?php } else if ($class->hasAvailableSpots()) { ?>
          <input type="hidden" name="action" value="enroll">
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-plus"></i> Inscrever
          </button>
        <?php } else { ?>
          <button type="button" class="btn btn-disabled" disabled>
            <i class="fas fa-ban"></i> Aula Cheia
          </button>
        <?php } ?>
      </form>
    <?php } ?>
  </article>
<?php } ?>

<?php function drawScheduleTable(array $classes, Session $session, PDO $db) { 
  $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
  $dayNames = ['Segunda', 'Terca', 'Quarta', 'Quinta', 'Sexta', 'Sabado', 'Domingo'];
  
  $classesByDay = [];
  foreach ($days as $day) {
    $classesByDay[$day] = [];
  }
  foreach ($classes as $class) {
    $classesByDay[$class->dayOfWeek][] = $class;
  }
?>
  <div class="schedule-container">
    <?php foreach ($days as $index => $day) { ?>
      <div class="day-column">
        <h3 class="day-header"><?= $dayNames[$index] ?></h3>
        <div class="day-classes">
          <?php if (empty($classesByDay[$day])) { ?>
            <p class="no-classes">Sem aulas</p>
          <?php } else { ?>
            <?php foreach ($classesByDay[$day] as $class) { 
              drawClassCard($class, $session, $db);
            } ?>
          <?php } ?>
        </div>
      </div>
    <?php } ?>
  </div>
<?php } ?>
