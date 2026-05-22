<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/session.php');
  require_once(__DIR__ . '/../database/connection.db.php');

  $session = new Session();
  $db = getDatabaseConnection();
?>
