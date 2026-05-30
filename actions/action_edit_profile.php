<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/bootstrap.php');
require_once(__DIR__ . '/../database/user.class.php');
require_once(__DIR__ . '/../database/trainer.class.php');

if (!$session->isLoggedIn()) {
    $session->addMessage('error', 'Tem de estar autenticado para atualizar o perfil.');
    header('Location: ../pages/login.php');
    exit;
}

$user = User::getUser($db, $session->getId());

if (!$user) {
    $session->addMessage('error', 'Utilizador não encontrado.');
    header('Location: ../pages/profile.php');
    exit;
}

$uploadDir = __DIR__ . '/../docs/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0775, true);
}


if (isset($_POST['remove_photo'])) {
    if ($user->photo && $user->photo !== 'default_profile.png') {
        $oldPath = $uploadDir . $user->photo;
        if (file_exists($oldPath)) unlink($oldPath);
    }
    $user->updatePhoto($db, 'default_profile.png');
    $session->addMessage('success', 'Foto removida.');
    header('Location: ../pages/profile.php');
    exit;
}

if (!empty($_FILES['photo']['name'])) {
    $file    = $_FILES['photo'];
    $ext          = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedExts  = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize      = 2 * 1024 * 1024;

    // Verificar extensão E MIME type real (previne upload de ficheiros PHP renomeados)
    $finfo    = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($ext, $allowedExts) || !in_array($mimeType, $allowedMimes)) {
        $session->addMessage('error', 'Formato não suportado. Use JPG, PNG, GIF ou WebP.');
        header('Location: ../pages/profile.php');
        exit;
    }

    if ($file['size'] > $maxSize) {
        $session->addMessage('error', 'A imagem não pode ter mais de 2MB.');
        header('Location: ../pages/profile.php');
        exit;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $session->addMessage('error', 'Erro ao fazer upload da imagem.');
        header('Location: ../pages/profile.php');
        exit;
    }

    if ($user->photo && $user->photo !== 'default_profile.png') {
        $oldPath = $uploadDir . $user->photo;
        if (file_exists($oldPath)) unlink($oldPath);
    }

    $filename = 'user_' . $user->id . '_' . time() . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
        $session->addMessage('error', 'Não foi possível guardar a imagem.');
        header('Location: ../pages/profile.php');
        exit;
    }

    $user->updatePhoto($db, $filename);
}

$fullName        = trim($_POST['full_name'] ?? '');
$username        = trim($_POST['username'] ?? '');
$bio             = trim($_POST['bio'] ?? '');
$currentPassword = $_POST['current_password'] ?? '';
$newPassword     = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

if (!empty($fullName)) {
    if ($username !== $user->username && User::usernameExists($db, $username)) {
        $session->addMessage('error', 'Este username já está em uso.');
        header('Location: ../pages/profile.php');
        exit;
    }

    $user->fullName = $fullName;
    $user->username = $username ?: $user->username;
    $user->bio      = $bio ?: null;
    $user->save($db);
    $session->setName($user->fullName);
    $session->addMessage('success', 'Perfil atualizado com sucesso!');
}

if (!empty($newPassword)) {
    if (strlen($newPassword) < 8) {
        $session->addMessage('error', 'A nova password deve ter pelo menos 8 caracteres.');
        header('Location: ../pages/profile.php');
        exit;
    }

    if ($newPassword !== $confirmPassword) {
        $session->addMessage('error', 'As novas passwords não coincidem.');
        header('Location: ../pages/profile.php');
        exit;
    }

    $verifyUser = User::getUserWithPassword($db, $user->email, $currentPassword);
    if (!$verifyUser) {
        $session->addMessage('error', 'Password atual incorreta.');
        header('Location: ../pages/profile.php');
        exit;
    }

    $user->updatePassword($db, $newPassword);
    $session->addMessage('success', 'Password atualizada com sucesso!');
}

// Atualizar perfil de treinador se aplicável
if ($user->role === 'trainer') {
    $specializations = trim($_POST['specializations'] ?? '');
    $certifications  = trim($_POST['certifications']  ?? '');

    $trainerProfile = TrainerProfile::getProfile($db, $user->id);
    if ($trainerProfile) {
        $trainerProfile->specializations = $specializations;
        $trainerProfile->certifications  = $certifications;
        $trainerProfile->save($db);
    } else {
        // Criar perfil se não existir
        $tp = new TrainerProfile($user->id, $specializations, $certifications, '');
        $tp->save($db);
    }
}

header('Location: ../pages/profile.php');
?>