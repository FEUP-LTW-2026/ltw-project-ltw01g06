<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/bootstrap.php');

if (!$session->isLoggedIn() || !$session->isAdmin()) {
    header('Location: ../pages/login.php');
    exit;
}

$classId = (int)($_POST['class_id'] ?? 0);
if ($classId <= 0) {
    $session->addMessage('error', 'Aula inválida.');
    header('Location: ../pages/admin-classes.php');
    exit;
}

$stmt = $db->prepare('SELECT is_active, name FROM classes WHERE id = ?');
$stmt->execute(array($classId));
$class = $stmt->fetch();

if (!$class) {
    $session->addMessage('error', 'Aula não encontrada.');
    header('Location: ../pages/admin-classes.php');
    exit;
}

$newState = $class['is_active'] ? 0 : 1;
$db->prepare('UPDATE classes SET is_active = ? WHERE id = ?')->execute(array($newState, $classId));

$label = $newState ? 'ativada' : 'desativada';
$session->addMessage('success', "Aula \"{$class['name']}\" {$label}.");
header('Location: ../pages/admin-classes.php');
?>
