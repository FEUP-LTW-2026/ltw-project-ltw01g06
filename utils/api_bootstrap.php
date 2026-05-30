<?php
  declare(strict_types = 1);

  header('Content-Type: application/json');
  header('Access-Control-Allow-Origin: *');
  header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
  header('Access-Control-Allow-Headers: Content-Type');

  if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
  }

  require_once(__DIR__ . '/../database/connection.db.php');
  $db = getDatabaseConnection();
?>
