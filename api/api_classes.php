<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/api_bootstrap.php');
  require_once(__DIR__ . '/../database/class.class.php');

  $day       = $_GET['day']        ?? null;
  $trainerId = isset($_GET['trainer_id']) ? (int)$_GET['trainer_id'] : null;

  if ($day) {
    $classes = GymClass::getClassesByDay($db, $day);
  } elseif ($trainerId) {
    $classes = GymClass::getClassesByTrainer($db, $trainerId);
  } else {
    $classes = GymClass::getAllClasses($db);
  }

  $result = array_map(function($c) {
    return [
      'id'                 => $c->id,
      'name'               => $c->name,
      'description'        => $c->description,
      'type'               => $c->type,
      'level'              => $c->level,
      'trainer_id'         => $c->trainerId,
      'trainer_name'       => $c->trainerName,
      'day_of_week'        => $c->dayOfWeek,
      'start_time'         => $c->startTime,
      'end_time'           => $c->getEndTime(),
      'duration_min'       => $c->durationMin,
      'capacity'           => $c->capacity,
      'current_enrollment' => $c->currentEnrollment,
      'available_spots'    => $c->getAvailableSpots(),
      'is_active'          => $c->isActive,
    ];
  }, $classes);

  echo json_encode(['success' => true, 'data' => $result, 'count' => count($result)]);
?>
