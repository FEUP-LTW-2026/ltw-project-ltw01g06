<?php
  declare(strict_types = 1);

  class Waitlist {
    public int $id;
    public int $userId;
    public int $classId;
    public int $position;
    public string $joinedAt;
    public ?string $userName;
    public ?string $className;

    public function __construct(
      int $id,
      int $userId,
      int $classId,
      int $position,
      string $joinedAt,
      ?string $userName  = null,
      ?string $className = null
    ) {
      $this->id        = $id;
      $this->userId    = $userId;
      $this->classId   = $classId;
      $this->position  = $position;
      $this->joinedAt  = $joinedAt;
      $this->userName  = $userName;
      $this->className = $className;
    }

    // Junta utilizador à lista de espera. Devolve null se já estiver na lista ou inscrito.
    public static function join(PDO $db, int $userId, int $classId) : ?Waitlist {
      // Não entrar se já estiver inscrito
      if (Enrollment::isUserEnrolled($db, $userId, $classId)) {
        return null;
      }

      // Não entrar duas vezes na waitlist
      $stmt = $db->prepare('SELECT 1 FROM waitlist WHERE user_id = ? AND class_id = ?');
      $stmt->execute(array($userId, $classId));
      if ($stmt->fetch()) {
        return null;
      }

      // Posição = última posição + 1
      $stmt = $db->prepare('SELECT COALESCE(MAX(position), 0) + 1 FROM waitlist WHERE class_id = ?');
      $stmt->execute(array($classId));
      $position = (int)$stmt->fetchColumn();

      $stmt = $db->prepare('INSERT INTO waitlist (user_id, class_id, position) VALUES (?, ?, ?)');
      $stmt->execute(array($userId, $classId, $position));

      return self::getEntry($db, (int)$db->lastInsertId());
    }

    // Remove utilizador da lista de espera e reordena as posições seguintes.
    public static function leave(PDO $db, int $userId, int $classId) : bool {
      $stmt = $db->prepare('SELECT position FROM waitlist WHERE user_id = ? AND class_id = ?');
      $stmt->execute(array($userId, $classId));
      $row = $stmt->fetch();
      if (!$row) return false;

      $position = (int)$row['position'];

      $stmt = $db->prepare('DELETE FROM waitlist WHERE user_id = ? AND class_id = ?');
      $stmt->execute(array($userId, $classId));

      // Reordenar posições seguintes
      $stmt = $db->prepare('UPDATE waitlist SET position = position - 1 WHERE class_id = ? AND position > ?');
      $stmt->execute(array($classId, $position));

      return true;
    }

    public static function getEntry(PDO $db, int $id) : ?Waitlist {
      $stmt = $db->prepare('
        SELECT w.*, u.full_name AS user_name, c.name AS class_name
        FROM waitlist w
        LEFT JOIN users u ON w.user_id = u.id
        LEFT JOIN classes c ON w.class_id = c.id
        WHERE w.id = ?
      ');
      $stmt->execute(array($id));

      if ($row = $stmt->fetch()) {
        return self::hydrate($row);
      }
      return null;
    }

    // Posição do utilizador numa aula específica. Devolve null se não estiver na lista.
    public static function getUserPosition(PDO $db, int $userId, int $classId) : ?int {
      $stmt = $db->prepare('SELECT position FROM waitlist WHERE user_id = ? AND class_id = ?');
      $stmt->execute(array($userId, $classId));
      $row = $stmt->fetch();
      return $row ? (int)$row['position'] : null;
    }

    public static function getClassWaitlist(PDO $db, int $classId) : array {
      $stmt = $db->prepare('
        SELECT w.*, u.full_name AS user_name, c.name AS class_name
        FROM waitlist w
        LEFT JOIN users u ON w.user_id = u.id
        LEFT JOIN classes c ON w.class_id = c.id
        WHERE w.class_id = ?
        ORDER BY w.position
      ');
      $stmt->execute(array($classId));

      $entries = array();
      while ($row = $stmt->fetch()) {
        $entries[] = self::hydrate($row);
      }
      return $entries;
    }

    public static function getUserWaitlists(PDO $db, int $userId) : array {
      $stmt = $db->prepare('
        SELECT w.*, u.full_name AS user_name, c.name AS class_name
        FROM waitlist w
        LEFT JOIN users u ON w.user_id = u.id
        LEFT JOIN classes c ON w.class_id = c.id
        WHERE w.user_id = ?
        ORDER BY w.joined_at
      ');
      $stmt->execute(array($userId));

      $entries = array();
      while ($row = $stmt->fetch()) {
        $entries[] = self::hydrate($row);
      }
      return $entries;
    }

    private static function hydrate(array $row) : self {
      return new self(
        (int)$row['id'],
        (int)$row['user_id'],
        (int)$row['class_id'],
        (int)$row['position'],
        $row['joined_at'],
        $row['user_name']  ?? null,
        $row['class_name'] ?? null
      );
    }
  }
?>
