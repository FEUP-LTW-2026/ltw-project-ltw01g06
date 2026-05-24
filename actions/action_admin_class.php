<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/bootstrap.php');

if (!$session->isLoggedIn() || !$session->isAdmin()) {
    header('Location: ../pages/login.php');
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'create') {
    $name       = trim($_POST['name'] ?? '');
    $desc       = trim($_POST['description'] ?? '') ?: null;
    $type       = trim($_POST['type'] ?? '');
    $level      = $_POST['level'] ?? 'beginner';
    $trainerId  = (int)($_POST['trainer_id'] ?? 0) ?: null;
    $day        = $_POST['day_of_week'] ?? '';
    $startTime  = $_POST['start_time'] ?? '';
    $duration   = (int)($_POST['duration_min'] ?? 60);
    $capacity   = (int)($_POST['capacity'] ?? 20);

    if (!$name || !$type || !$day || !$startTime) {
        $session->addMessage('error', 'Preenche todos os campos obrigatórios.');
        header('Location: ../pages/admin-classes.php');
        exit;
    }

    $stmt = $db->prepare('
        INSERT INTO classes (name, description, type, level, trainer_id, day_of_week, start_time, duration_min, capacity)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $stmt->execute(array($name, $desc, $type, $level, $trainerId, $day, $startTime, $duration, $capacity));
    $session->addMessage('success', "Aula \"{$name}\" criada com sucesso.");
}

header('Location: ../pages/admin-classes.php');
?>
