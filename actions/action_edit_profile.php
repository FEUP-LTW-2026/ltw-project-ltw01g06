<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/bootstrap.php');
  require_once(__DIR__ . '/../database/user.class.php');

  if (!$session->isLoggedIn()) {
    $session->addMessage('error', 'Tem de estar autenticado para atualizar o perfil.');
    header('Location: ../pages/index.php');
    exit;
  }

  $fullName        = trim($_POST['full_name'] ?? '');
  $username        = trim($_POST['username'] ?? '');
  $bio             = trim($_POST['bio'] ?? '');
  $currentPassword = $_POST['current_password'] ?? '';
  $newPassword     = $_POST['new_password'] ?? '';
  $confirmPassword = $_POST['confirm_password'] ?? '';

  $user = User::getUser($db, $session->getId());

  if (!$user) {
    $session->addMessage('error', 'Utilizador não encontrado.');
    header('Location: ../pages/profile.php');
    exit;
  }

  // Atualizar dados de perfil
  if (!empty($fullName)) {
    // Verificar se username já existe noutro utilizador
    if ($username !== $user->username && User::usernameExists($db, $username)) {
      $session->addMessage('error', 'Este username já está em uso.');
      header('Location: ../pages/profile.php');
      exit;
    }

    $user->fullName = $fullName;
    $user->username = $username ?: $user->username;
    $user->bio      = $bio ?: null;
    $user->save($db);
    $session->setName($user->fullName);
    $session->addMessage('success', 'Perfil atualizado com sucesso!');
  }

  // Atualizar password se fornecida
  if (!empty($newPassword)) {
    if (strlen($newPassword) < 8) {
      $session->addMessage('error', 'A nova password deve ter pelo menos 8 caracteres.');
      header('Location: ../pages/profile.php');
      exit;
    }

    if ($newPassword !== $confirmPassword) {
      $session->addMessage('error', 'As novas passwords não coincidem.');
      header('Location: ../pages/profile.php');
      exit;
    }

    $verifyUser = User::getUserWithPassword($db, $user->email, $currentPassword);
    if (!$verifyUser) {
      $session->addMessage('error', 'Password atual incorreta.');
      header('Location: ../pages/profile.php');
      exit;
    }

    $user->updatePassword($db, $newPassword);
    $session->addMessage('success', 'Password atualizada com sucesso!');
  }

  header('Location: ../pages/profile.php');
?>
