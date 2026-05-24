<?php
declare(strict_types=1);

require_once(__DIR__ . '/../utils/bootstrap.php');
require_once(__DIR__ . '/../database/equipment.class.php');

if (!$session->isLoggedIn() || !$session->isAdmin()) {
    header('Location: ../pages/login.php');
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'create') {
    $name      = trim($_POST['name'] ?? '');
    $category  = trim($_POST['category'] ?? '') ?: null;
    $quantity  = (int)($_POST['quantity'] ?? 1);
    $available = (int)($_POST['available'] ?? 1);
    $status    = $_POST['status'] ?? 'available';
    $notes     = trim($_POST['notes'] ?? '') ?: null;

    if (!$name) {
        $session->addMessage('error', 'O nome é obrigatório.');
        header('Location: ../pages/admin-equipment.php');
        exit;
    }

    Equipment::create($db, $name, $category, $quantity, min($available, $quantity), $status, $notes);
    $session->addMessage('success', "Equipamento \"{$name}\" adicionado.");

} elseif ($action === 'edit') {
    $id        = (int)($_POST['id'] ?? 0);
    $name      = trim($_POST['name'] ?? '');
    $category  = trim($_POST['category'] ?? '') ?: null;
    $quantity  = (int)($_POST['quantity'] ?? 1);
    $available = (int)($_POST['available'] ?? 0);
    $status    = $_POST['status'] ?? 'available';
    $notes     = trim($_POST['notes'] ?? '') ?: null;

    $item = Equipment::getById($db, $id);
    if ($item && $name) {
        $item->name      = $name;
        $item->category  = $category;
        $item->quantity  = $quantity;
        $item->available = min($available, $quantity);
        $item->status    = $status;
        $item->notes     = $notes;
        $item->save($db);
        $session->addMessage('success', "Equipamento \"{$name}\" atualizado.");
    }

} elseif ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    Equipment::delete($db, $id);
    $session->addMessage('success', 'Equipamento eliminado.');
}

header('Location: ../pages/admin-equipment.php');
?>
