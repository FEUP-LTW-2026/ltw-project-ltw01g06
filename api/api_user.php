<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/api_bootstrap.php');
  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../database/user.class.php');

  $session = new Session();

  if (!$session->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Não autenticado']);
    exit;
  }

  $user = User::getUser($db, $session->getId());

  if ($user) {
    echo json_encode([
      'success' => true,
      'data' => [
        'id'         => $user->id,
        'full_name'  => $user->fullName,
        'username'   => $user->username,
        'email'      => $user->email,
        'role'       => $user->role,
        'photo'      => $user->photo,
        'bio'        => $user->bio,
        'is_active'  => $user->isActive,
        'created_at' => $user->createdAt,
      ]
    ]);
  } else {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Utilizador não encontrado']);
  }
?>
