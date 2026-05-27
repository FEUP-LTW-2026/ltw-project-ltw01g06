<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/bootstrap.php');

  $session->logout();
  $session->addMessage('success', 'Logout efetuado com sucesso!');

  header('Location: ../index.php');
?>
