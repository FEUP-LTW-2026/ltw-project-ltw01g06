<?php
  declare(strict_types = 1);

  class User {
    public int $id;
    public string $fullName;
    public string $username;
    public string $email;
    public string $role;
    public string $photo;
    public ?string $bio;
    public bool $isActive;
    public string $createdAt;
    public string $updatedAt;

    public function __construct(
      int $id,
      string $fullName,
      string $username,
      string $email,
      string $role,
      string $photo,
      ?string $bio,
      bool $isActive,
      string $createdAt,
      string $updatedAt
    ) {
      $this->id        = $id;
      $this->fullName  = $fullName;
      $this->username  = $username;
      $this->email     = $email;
      $this->role      = $role;
      $this->photo     = $photo;
      $this->bio       = $bio;
      $this->isActive  = $isActive;
      $this->createdAt = $createdAt;
      $this->updatedAt = $updatedAt;
    }

    public function save(PDO $db) : void {
      $stmt = $db->prepare('
        UPDATE users SET full_name = ?, username = ?, email = ?, bio = ?
        WHERE id = ?
      ');
      $stmt->execute(array($this->fullName, $this->username, $this->email, $this->bio, $this->id));
    }

    public function updatePassword(PDO $db, string $newPassword) : void {
      $stmt = $db->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
      $stmt->execute(array(password_hash($newPassword, PASSWORD_BCRYPT), $this->id));
    }

    public function updatePhoto(PDO $db, string $filename) : void {
      $stmt = $db->prepare('UPDATE users SET photo = ? WHERE id = ?');
      $stmt->execute(array($filename, $this->id));
      $this->photo = $filename;
    }

    // Login — aceita email ou username
    public static function getUserWithPassword(PDO $db, string $login, string $password) : ?User {
      $stmt = $db->prepare('
        SELECT * FROM users
        WHERE (LOWER(email) = LOWER(?) OR LOWER(username) = LOWER(?))
          AND is_active = 1
      ');
      $stmt->execute(array($login, $login));

      if ($user = $stmt->fetch()) {
        if (password_verify($password, $user['password_hash'])) {
          return self::hydrate($user);
        }
      }
      return null;
    }

    public static function getUser(PDO $db, int $id) : ?User {
      $stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
      $stmt->execute(array($id));

      if ($user = $stmt->fetch()) {
        return self::hydrate($user);
      }
      return null;
    }

    public static function getUserByEmail(PDO $db, string $email) : ?User {
      $stmt = $db->prepare('SELECT * FROM users WHERE LOWER(email) = LOWER(?)');
      $stmt->execute(array($email));

      if ($user = $stmt->fetch()) {
        return self::hydrate($user);
      }
      return null;
    }

    public static function getUserByUsername(PDO $db, string $username) : ?User {
      $stmt = $db->prepare('SELECT * FROM users WHERE LOWER(username) = LOWER(?)');
      $stmt->execute(array($username));

      if ($user = $stmt->fetch()) {
        return self::hydrate($user);
      }
      return null;
    }

    public static function createUser(PDO $db, string $fullName, string $username, string $email, string $password, string $role = 'member') : ?User {
      $stmt = $db->prepare('
        INSERT INTO users (full_name, username, email, password_hash, role)
        VALUES (?, ?, ?, ?, ?)
      ');
      $stmt->execute(array($fullName, $username, $email, password_hash($password, PASSWORD_BCRYPT), $role));

      return self::getUser($db, (int)$db->lastInsertId());
    }

    public static function getAllUsers(PDO $db, string $role = null) : array {
      if ($role) {
        $stmt = $db->prepare('SELECT * FROM users WHERE role = ? ORDER BY full_name');
        $stmt->execute(array($role));
      } else {
        $stmt = $db->query('SELECT * FROM users ORDER BY full_name');
      }

      $users = array();
      while ($user = $stmt->fetch()) {
        $users[] = self::hydrate($user);
      }
      return $users;
    }

    public static function emailExists(PDO $db, string $email) : bool {
      $stmt = $db->prepare('SELECT 1 FROM users WHERE LOWER(email) = LOWER(?)');
      $stmt->execute(array($email));
      return $stmt->fetch() !== false;
    }

    public static function usernameExists(PDO $db, string $username) : bool {
      $stmt = $db->prepare('SELECT 1 FROM users WHERE LOWER(username) = LOWER(?)');
      $stmt->execute(array($username));
      return $stmt->fetch() !== false;
    }

    private static function hydrate(array $row) : self {
      return new self(
        (int)$row['id'],
        $row['full_name'],
        $row['username'],
        $row['email'],
        $row['role'],
        $row['photo'] ?? 'default_profile.png',
        $row['bio'] ?? null,
        (bool)$row['is_active'],
        $row['created_at'],
        $row['updated_at']
      );
    }
  }
?>
