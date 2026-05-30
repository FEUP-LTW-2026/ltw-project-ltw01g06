<?php
declare(strict_types=1);
require_once(__DIR__ . '/../utils/bootstrap.php');
require_once(__DIR__ . '/../utils/csrf.php');
require_once(__DIR__ . '/../database/equipment.class.php');

if (!$session->isLoggedIn() || !$session->isAdmin()) { header('Location: ../pages/login.php'); exit; }

$equipmentId = (int)($_POST['equipment_id'] ?? 0);
$item        = $equipmentId > 0 ? Equipment::getById($db, $equipmentId) : null;

if (!$item) {
    $session->addMessage('error', 'Equipamento não encontrado.');
    header('Location: ../pages/admin-equipment.php'); exit;
validateCsrfOrDie($session);
}

$name      = trim($_POST['name']     ?? '') ?: $item->name;
$category  = trim($_POST['category'] ?? '') ?: null;
$quantity  = max(0, (int)($_POST['quantity']  ?? $item->quantity));
$available = max(0, (int)($_POST['available'] ?? $item->available));
$status    = $_POST['status'] ?? $item->status;
$notes     = trim($_POST['notes'] ?? '') ?: null;

if (!in_array($status, ['available','maintenance','retired'])) {
    $session->addMessage('error', 'Estado inválido.');
    header('Location: ../pages/admin-equipment-edit.php?id='.$equipmentId); exit;
}
if ($available > $quantity) $available = $quantity;

$item->name      = $name;
$item->category  = $category;
$item->quantity  = $quantity;
$item->available = $available;
$item->status    = $status;
$item->notes     = $notes;
$item->save($db);

$session->addMessage('success', 'Equipamento atualizado com sucesso!');
header('Location: ../pages/admin-equipment.php');
?>