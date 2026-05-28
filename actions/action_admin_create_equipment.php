<?php
declare(strict_types=1);
require_once(__DIR__ . '/../utils/bootstrap.php');
require_once(__DIR__ . '/../database/equipment.class.php');

if (!$session->isLoggedIn() || !$session->isAdmin()) { header('Location: ../pages/login.php'); exit; }

$name      = trim($_POST['name']     ?? '');
$category  = trim($_POST['category'] ?? '') ?: null;
$quantity  = max(0, (int)($_POST['quantity']  ?? 1));
$available = max(0, (int)($_POST['available'] ?? 1));
$status    = $_POST['status'] ?? 'available';
$notes     = trim($_POST['notes'] ?? '') ?: null;

if (!$name) {
    $session->addMessage('error', 'O nome é obrigatório.');
    header('Location: ../pages/admin-equipment-edit.php'); exit;
}
if (!in_array($status, ['available','maintenance','retired'])) {
    $session->addMessage('error', 'Estado inválido.');
    header('Location: ../pages/admin-equipment-edit.php'); exit;
}
if ($available > $quantity) $available = $quantity;

Equipment::create($db, $name, $category, $quantity, $available, $status, $notes);
$session->addMessage('success', 'Equipamento criado com sucesso!');
header('Location: ../pages/admin-equipment.php');
?>