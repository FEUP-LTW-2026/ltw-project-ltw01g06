<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/bootstrap.php');
require_once(__DIR__ . '/../utils/csrf.php');
require_once(__DIR__ . '/../database/user.class.php');

if (!$session->isLoggedIn() || !$session->isAdmin()) {
    header('Location: ../pages/login.php');
    exit;
validateCsrfOrDie($session);
}

$userId = (int)($_POST['user_id'] ?? 0);

if ($userId <= 0 || $userId === $session->getId()) {
    $session->addMessage('error', 'Operação inválida.');
    header('Location: ../pages/admin-members.php');
    exit;
}

$user = User::getUser($db, $userId);
if (!$user) {
    $session->addMessage('error', 'Utilizador não encontrado.');
    header('Location: ../pages/admin-members.php');
    exit;
}

$newState = $user->isActive ? 0 : 1;
$stmt = $db->prepare('UPDATE users SET is_active = ? WHERE id = ?');
$stmt->execute(array($newState, $userId));

$label = $newState ? 'ativado' : 'desativado';
$session->addMessage('success', "Utilizador {$user->fullName} {$label} com sucesso.");
header('Location: ../pages/admin-members.php');
?>