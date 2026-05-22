<?php
  declare(strict_types = 1);

  // Database Setup Script
  // Run this file once to create the database and insert sample data

  $dbPath = __DIR__ . '/database/database.db';
  $sqlPath = __DIR__ . '/database/database.sql';

  echo "<h1>Equilibrio Gym - Database Setup</h1>";

  if (file_exists($dbPath)) {
    echo "<p style='color: orange;'>Base de dados ja existe. A apagar para recriar...</p>";
    unlink($dbPath);
  }

  try {
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = file_get_contents($sqlPath);

    if ($sql === false) {
      throw new Exception("Nao foi possivel ler o ficheiro SQL.");
    }

    $db->exec($sql);

    echo "<p style='color: green;'>Base de dados criada com sucesso!</p>";
    echo "<h2>Utilizadores de teste:</h2>";
    echo "<ul>";
    echo "<li><strong>Admin:</strong> admin@equilibrio.pt / password123</li>";
    echo "<li><strong>Treinador:</strong> joao.trainer@equilibrio.pt / password123</li>";
    echo "<li><strong>Membro:</strong> maria@exemplo.pt / password123</li>";
    echo "</ul>";
    echo "<p><a href='pages/index.php'>Ir para o site</a></p>";

  } catch (Exception $e) {
    echo "<p style='color: red;'>Erro: " . htmlspecialchars($e->getMessage()) . "</p>";
  }
?>
