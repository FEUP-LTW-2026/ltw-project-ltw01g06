<?php
declare(strict_types=1);
require_once(__DIR__ . '/../utils/bootstrap.php');
require_once(__DIR__ . '/../database/class.class.php');

if (!$session->isLoggedIn() || !$session->isAdmin()) { header('Location: ../pages/login.php'); exit; }

$name        = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '') ?: null;
$type        = trim($_POST['type'] ?? '');
$level       = $_POST['level'] ?? 'beginner';
$trainerId   = (int)($_POST['trainer_id'] ?? 0) ?: null;
$dayOfWeek   = $_POST['day_of_week'] ?? '';
$startTime   = $_POST['start_time'] ?? '';
$durationMin = (int)($_POST['duration_min'] ?? 60);
$capacity    = (int)($_POST['capacity'] ?? 20);

$validDays   = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
$validLevels = ['beginner','intermediate','advanced'];

if (!$name || !$type || !$dayOfWeek || !$startTime) {
    $session->addMessage('error', 'Preenche todos os campos obrigatórios.');
    header('Location: ../pages/admin-class-create.php'); exit;
}
if (!in_array($dayOfWeek, $validDays) || !in_array($level, $validLevels)) {
    $session->addMessage('error', 'Dados inválidos.');
    header('Location: ../pages/admin-class-create.php'); exit;
}

GymClass::create($db, $name, $description, $type, $level, $trainerId, $dayOfWeek, $startTime, $durationMin, $capacity);
$session->addMessage('success', 'Aula criada com sucesso!');
header('Location: ../pages/admin-classes.php');
?>