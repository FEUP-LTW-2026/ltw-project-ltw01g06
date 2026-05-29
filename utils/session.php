<?php
declare(strict_types=1);

class Session {
    private array $messages;

    public function __construct() {
        session_start();
        $this->messages = isset($_SESSION['messages']) ? $_SESSION['messages'] : [];
        unset($_SESSION['messages']);
    }

    public function isLoggedIn(): bool {
        return isset($_SESSION['id']);
    }

    public function logout(): void {
        session_destroy();
    }

    public function getId(): ?int {
        return isset($_SESSION['id']) ? $_SESSION['id'] : null;
    }

    public function getName(): ?string {
        return isset($_SESSION['name']) ? $_SESSION['name'] : null;
    }

    public function getEmail(): ?string {
        return isset($_SESSION['email']) ? $_SESSION['email'] : null;
    }

    public function getRole(): ?string {
        return isset($_SESSION['role']) ? $_SESSION['role'] : null;
    }

    public function setId(int $id): void {
        $_SESSION['id'] = $id;
    }

    public function setName(string $name): void {
        $_SESSION['name'] = $name;
    }

    public function setEmail(string $email): void {
        $_SESSION['email'] = $email;
    }

    public function setRole(string $role): void {
        $_SESSION['role'] = $role;
    }

    public function isAdmin(): bool {
        return $this->getRole() === 'admin';
    }

    public function isTrainer(): bool {
        return $this->getRole() === 'trainer';
    }

    public function isMember(): bool {
        return $this->getRole() === 'member';
    }

    public function addMessage(string $type, string $text): void {
        $_SESSION['messages'][] = ['type' => $type, 'text' => $text];
    }

    public function getMessages(): array {
        return $this->messages;
    }
}
?>