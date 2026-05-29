<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/bootstrap.php');
require_once(__DIR__ . '/../utils/csrf.php');
require_once(__DIR__ . '/../database/enrollment.class.php');
require_once(__DIR__ . '/../database/class.class.php');

if (!$session->isLoggedIn() || !$session->isAdmin()) {
    header('Content-Type: application/json');
    echo json_encode(['ok' => false, 'error' => 'Sem permissões.']);
    exit;
validateCsrfOrDie($session);
}

$classId = (int)($_POST['class_id'] ?? 0);
$userId  = (int)($_POST['user_id']  ?? 0);

if (!$classId || !$userId) {
    header('Content-Type: application/json');
    echo json_encode(['ok' => false, 'error' => 'Dados inválidos.']);
    exit;
}

$class = GymClass::getClass($db, $classId);
if (!$class || !$class->hasAvailableSpots()) {
    header('Content-Type: application/json');
    echo json_encode(['ok' => false, 'error' => 'Aula cheia ou não encontrada.']);
    exit;
}

$enrollment = Enrollment::enroll($db, $userId, $classId);
if (!$enrollment) {
    header('Content-Type: application/json');
    echo json_encode(['ok' => false, 'error' => 'Utilizador já inscrito.']);
    exit;
}

header('Content-Type: application/json');
echo json_encode(['ok' => true, 'enrollmentId' => $enrollment->id]);
?>