<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/api_bootstrap.php');
  require_once(__DIR__ . '/../database/trainer.class.php');

  $trainerId = isset($_GET['id']) ? (int)$_GET['id'] : null;

  if ($trainerId) {
    $trainer = TrainerProfile::getProfile($db, $trainerId);

    if ($trainer) {
      echo json_encode([
        'success' => true,
        'data' => [
          'user_id'         => $trainer->userId,
          'full_name'       => $trainer->fullName,
          'username'        => $trainer->username,
          'email'           => $trainer->email,
          'bio'             => $trainer->bio,
          'photo'           => $trainer->photo,
          'specializations' => $trainer->getSpecializationsArray(),
          'certifications'  => $trainer->getCertificationsArray(),
        ]
      ]);
    } else {
      http_response_code(404);
      echo json_encode(['success' => false, 'error' => 'Treinador não encontrado']);
    }

  } else {
    $trainers = TrainerProfile::getAllProfiles($db);

    $result = array_map(function($t) {
      return [
        'user_id'         => $t->userId,
        'full_name'       => $t->fullName,
        'username'        => $t->username,
        'email'           => $t->email,
        'bio'             => $t->bio,
        'photo'           => $t->photo,
        'specializations' => $t->getSpecializationsArray(),
        'certifications'  => $t->getCertificationsArray(),
      ];
    }, $trainers);

    echo json_encode(['success' => true, 'data' => $result, 'count' => count($result)]);
  }
?>
