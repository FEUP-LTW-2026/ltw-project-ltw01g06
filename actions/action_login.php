<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/bootstrap.php');
  require_once(__DIR__ . '/../database/user.class.php');

  $login    = trim($_POST['login'] ?? '');    // aceita email ou username
  $password = $_POST['password'] ?? '';

  if (empty($login) || empty($password)) {
    $session->addMessage('error', 'Por favor preencha todos os campos.');
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
  }

  $user = User::getUserWithPassword($db, $login, $password);

  if ($user) {
    $session->setId($user->id);
    $session->setName($user->fullName);
    $session->setEmail($user->email);
    $session->setRole($user->role);
    $session->addMessage('success', 'Bem-vindo, ' . $user->fullName . '!');

    if ($user->role === 'admin') {
      header('Location: ../pages/admin.php');
    } elseif ($user->role === 'trainer') {
      header('Location: ../pages/dashboard.php');
    } else {
      header('Location: ../pages/dashboard.php');
    }
  } else {
    $session->addMessage('error', 'Credenciais incorretas ou conta inativa.');
    header('Location: ' . $_SERVER['HTTP_REFERER']);
  }
?>

