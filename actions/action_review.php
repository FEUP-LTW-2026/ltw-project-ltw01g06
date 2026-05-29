<?php
declare(strict_types=1);

ini_set("display_errors", 1); error_reporting(E_ALL);
require_once(__DIR__ . '/../utils/bootstrap.php');
require_once(__DIR__ . '/../utils/csrf.php');
require_once(__DIR__ . '/../database/enrollment.class.php');
require_once(__DIR__ . '/../database/review.class.php');

if (!$session->isLoggedIn()) {
    $session->addMessage('error', 'Tens de estar autenticado para avaliar uma aula.');
    header('Location: ../pages/login.php');
    exit;
    validateCsrfOrDie($session);
}

$classId = (int)($_POST['class_id'] ?? 0);
$userId  = $session->getId();

if ($classId <= 0) {
    $session->addMessage('error', 'Aula inválida.');
    header('Location: ../pages/schedule.php');
    exit;
}

if (!Enrollment::isUserEnrolled($db, $userId, $classId)) {
    $session->addMessage('error', 'Só podes avaliar aulas em que estás inscrito.');
    header('Location: ../pages/class-detail.php?id=' . $classId);
    exit;
}

if (isset($_POST['delete'])) {
    Review::delete($db, $userId, $classId);
    $session->addMessage('success', 'Avaliação eliminada.');
    header('Location: ../pages/class-detail.php?id=' . $classId);
    exit;
}

$rating  = (int)($_POST['rating'] ?? 0);
$comment = trim($_POST['comment'] ?? '') ?: null;

if ($rating < 1 || $rating > 5) {
    $session->addMessage('error', 'Classificação inválida. Escolhe entre 1 e 5.');
    header('Location: ../pages/class-detail.php?id=' . $classId);
    exit;
}

Review::upsert($db, $userId, $classId, $rating, $comment);
$session->addMessage('success', 'Avaliação guardada com sucesso!');
header('Location: ../pages/class-detail.php?id=' . $classId);
?>