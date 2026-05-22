<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/bootstrap.php');
  require_once(__DIR__ . '/../database/user.class.php');

  $fullName        = trim($_POST['full_name'] ?? '');
  $username        = trim($_POST['username'] ?? '');
  $email           = trim($_POST['email'] ?? '');
  $password        = $_POST['password'] ?? '';
  $confirmPassword = $_POST['confirm_password'] ?? '';

  if (empty($fullName) || empty($username) || empty($email) || empty($password)) {
    $session->addMessage('error', 'Por favor preencha todos os campos obrigatórios.');
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
  }

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $session->addMessage('error', 'Por favor introduza um email válido.');
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
  }

  if (strlen($password) < 8) {
    $session->addMessage('error', 'A password deve ter pelo menos 8 caracteres.');
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
  }

  if ($password !== $confirmPassword) {
    $session->addMessage('error', 'As passwords não coincidem.');
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
  }

  if (User::emailExists($db, $email)) {
    $session->addMessage('error', 'Este email já está registado.');
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
  }

  if (User::usernameExists($db, $username)) {
    $session->addMessage('error', 'Este username já está em uso.');
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
  }

  $user = User::createUser($db, $fullName, $username, $email, $password);

  if ($user) {
    $session->setId($user->id);
    $session->setName($user->fullName);
    $session->setEmail($user->email);
    $session->setRole($user->role);
    $session->addMessage('success', 'Conta criada com sucesso! Bem-vindo ao Equilíbrio Gym.');
    header('Location: ../pages/dashboard.php');
  } else {
    $session->addMessage('error', 'Erro ao criar conta. Por favor tente novamente.');
    header('Location: ' . $_SERVER['HTTP_REFERER']);
  }
?>
