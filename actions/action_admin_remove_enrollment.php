<?php
declare(strict_types=1);
require_once(__DIR__ . '/../utils/bootstrap.php');
require_once(__DIR__ . '/../database/enrollment.class.php');

if (!$session->isLoggedIn() || !$session->isAdmin()) {
    if (isset($_POST['ajax'])) { header('Content-Type: application/json'); echo json_encode(['ok'=>false]); exit; }
    header('Location: ../pages/login.php'); exit;
}

$enrollmentId = (int)($_POST['enrollment_id'] ?? 0);
$classId      = (int)($_POST['class_id'] ?? 0);
$userId       = null;

if ($enrollmentId > 0) {
    $enrollment = Enrollment::getEnrollment($db, $enrollmentId);
    $userId     = $enrollment ? $enrollment->userId : null;
    Enrollment::deleteById($db, $enrollmentId);
}

if (isset($_POST['ajax'])) {
    header('Content-Type: application/json');
    echo json_encode(['ok' => true, 'userId' => $userId]);
    exit;
}
$session->addMessage('success', 'Inscrição removida.');
header('Location: ../pages/admin-classes.php');
?>