<?php
declare(strict_types=1);
require_once(__DIR__ . '/../utils/bootstrap.php');
require_once(__DIR__ . '/../utils/csrf.php');
require_once(__DIR__ . '/../database/user.class.php');

if (!$session->isLoggedIn() || !$session->isAdmin()) { header('Location: ../pages/login.php'); exit; }

$userId  = (int)($_POST['user_id'] ?? 0);
$user    = $userId > 0 ? User::getUser($db, $userId) : null;
if (!$user) { $session->addMessage('error', 'Utilizador não encontrado.'); header('Location: ../pages/admin-members.php'); exit; }

$oldRole  = $user->role;
$fullName = trim($_POST['full_name'] ?? '') ?: $user->fullName;
$username = trim($_POST['username'] ?? '') ?: $user->username;
$role     = $_POST['role'] ?? $user->role;
$isActive = (bool)(int)($_POST['is_active'] ?? 1);
$bio      = trim($_POST['bio'] ?? '') ?: null;
$newPass  = $_POST['new_password'] ?? '';

if (!in_array($role, ['member','trainer','admin'])) {
    $session->addMessage('error', 'Papel inválido.');
    header('Location: ../pages/admin-user-edit.php?id='.$userId); exit;
validateCsrfOrDie($session);
}
if ($username !== $user->username && User::usernameExists($db, $username)) {
    $session->addMessage('error', 'Este username já está em uso.');
    header('Location: ../pages/admin-user-edit.php?id='.$userId); exit;
}

$user->fullName = $fullName;
$user->username = $username;
$user->role     = $role;
$user->isActive = $isActive;
$user->bio      = $bio;
$user->save($db);

if ($role === 'trainer' && $oldRole !== 'trainer') {
    $stmt = $db->prepare('INSERT OR IGNORE INTO trainer_profiles (user_id) VALUES (?)');
    $stmt->execute([$userId]);
}

if ($newPass !== '') {
    if (strlen($newPass) < 8) {
        $session->addMessage('error', 'A nova password deve ter pelo menos 8 caracteres.');
        header('Location: ../pages/admin-user-edit.php?id='.$userId); exit;
    }
    $user->updatePassword($db, $newPass);
}

$session->addMessage('success', 'Utilizador atualizado com sucesso!');
header('Location: ../pages/admin-members.php');
?>