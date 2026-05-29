<?php
declare(strict_types=1);
require_once(__DIR__ . '/../utils/bootstrap.php');
require_once(__DIR__ . '/../utils/csrf.php');
require_once(__DIR__ . '/../database/user.class.php');

if (!$session->isLoggedIn() || !$session->isAdmin()) { header('Location: ../pages/login.php'); exit; }

$fullName = trim($_POST['full_name'] ?? '');
$username = trim($_POST['username'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$role     = $_POST['role'] ?? 'member';
$bio      = trim($_POST['bio'] ?? '') ?: null;

if (!$fullName || !$username || !$email || !$password) {
    $session->addMessage('error', 'Preenche todos os campos obrigatórios.');
    header('Location: ../pages/admin-user-create.php'); exit;
validateCsrfOrDie($session);
}
if (strlen($password) < 8) {
    $session->addMessage('error', 'A palavra-passe deve ter pelo menos 8 caracteres.');
    header('Location: ../pages/admin-user-create.php'); exit;
}
if (!in_array($role, ['member','trainer','admin'])) {
    $session->addMessage('error', 'Papel inválido.');
    header('Location: ../pages/admin-user-create.php'); exit;
}
if (User::emailExists($db, $email)) {
    $session->addMessage('error', 'Este email já está em uso.');
    header('Location: ../pages/admin-user-create.php'); exit;
}
if (User::usernameExists($db, $username)) {
    $session->addMessage('error', 'Este username já está em uso.');
    header('Location: ../pages/admin-user-create.php'); exit;
}

User::createUser($db, $fullName, $username, $email, $password, $role, $bio);
$session->addMessage('success', 'Utilizador criado com sucesso!');
header('Location: ../pages/admin-members.php');
?>