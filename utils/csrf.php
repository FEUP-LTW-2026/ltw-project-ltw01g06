<?php
declare(strict_types=1);

// Valida o token CSRF no POST. Termina o request se inválido.
function validateCsrfOrDie(object $session, string $redirect = ''): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!$session->validateCsrf($token)) {
        $session->addMessage('error', 'Token de segurança inválido. Tenta novamente.');
        $location = $redirect ?: ($_SERVER['HTTP_REFERER'] ?? '../index.php');
        header('Location: ' . $location);
        exit;
    }
}
?>