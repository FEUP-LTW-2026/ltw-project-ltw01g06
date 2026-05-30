<?php
declare(strict_types=1);

class Enrollment {
    public int $id;
    public int $userId;
    public int $classId;
    public string $status;
    public string $enrolledAt;
    public string $updatedAt;
    public ?string $userName;
    public ?string $className;

    public function __construct(int $id, int $userId, int $classId, string $status,
        string $enrolledAt, string $updatedAt, ?string $userName=null, ?string $className=null) {
        $this->id=$id; $this->userId=$userId; $this->classId=$classId; $this->status=$status;
        $this->enrolledAt=$enrolledAt; $this->updatedAt=$updatedAt; $this->userName=$userName; $this->className=$className;
    }

    public static function enroll(PDO $db, int $userId, int $classId): ?Enrollment {
        $stmt = $db->prepare('SELECT id, status FROM enrollments WHERE user_id=? AND class_id=?');
        $stmt->execute([$userId, $classId]);
        $existing = $stmt->fetch();
        if ($existing) {
            if ($existing['status'] === 'active') return null;
            $stmt = $db->prepare('UPDATE enrollments SET status="active", updated_at=datetime("now") WHERE id=?');
            $stmt->execute([$existing['id']]);
            return self::getEnrollment($db, (int)$existing['id']);
        }
        $stmt = $db->prepare('INSERT INTO enrollments (user_id,class_id,status) VALUES (?,?,"active")');
        $stmt->execute([$userId, $classId]);
        return self::getEnrollment($db, (int)$db->lastInsertId());
    }

    public static function cancel(PDO $db, int $userId, int $classId): bool {
        $stmt = $db->prepare('UPDATE enrollments SET status="cancelled", updated_at=datetime("now") WHERE user_id=? AND class_id=? AND status="active"');
        $stmt->execute([$userId, $classId]);
        return $stmt->rowCount() > 0;
    }

    public static function deleteById(PDO $db, int $enrollmentId): bool {
        $stmt = $db->prepare('DELETE FROM enrollments WHERE id=?');
        $stmt->execute([$enrollmentId]);
        return $stmt->rowCount() > 0;
    }

    public static function getEnrollment(PDO $db, int $id): ?Enrollment {
        $stmt = $db->prepare('SELECT e.*, u.full_name AS user_name, c.name AS class_name FROM enrollments e LEFT JOIN users u ON e.user_id=u.id LEFT JOIN classes c ON e.class_id=c.id WHERE e.id=?');
        $stmt->execute([$id]);
        if ($r = $stmt->fetch()) return self::hydrate($r);
        return null;
    }

    public static function getUserEnrollments(PDO $db, int $userId): array {
        $stmt = $db->prepare('SELECT e.*, u.full_name AS user_name, c.name AS class_name FROM enrollments e LEFT JOIN users u ON e.user_id=u.id LEFT JOIN classes c ON e.class_id=c.id WHERE e.user_id=? AND e.status="active" ORDER BY e.enrolled_at DESC');
        $stmt->execute([$userId]);
        $r = [];
        while ($row = $stmt->fetch()) $r[] = self::hydrate($row);
        return $r;
    }

    public static function getClassEnrollments(PDO $db, int $classId): array {
        $stmt = $db->prepare('SELECT e.*, u.full_name AS user_name, c.name AS class_name FROM enrollments e LEFT JOIN users u ON e.user_id=u.id LEFT JOIN classes c ON e.class_id=c.id WHERE e.class_id=? AND e.status="active" ORDER BY e.enrolled_at');
        $stmt->execute([$classId]);
        $r = [];
        while ($row = $stmt->fetch()) $r[] = self::hydrate($row);
        return $r;
    }

    public static function isUserEnrolled(PDO $db, int $userId, int $classId): bool {
        $stmt = $db->prepare('SELECT 1 FROM enrollments WHERE user_id=? AND class_id=? AND status="active"');
        $stmt->execute([$userId, $classId]);
        return $stmt->fetch() !== false;
    }

    private static function hydrate(array $row): self {
        return new self((int)$row['id'], (int)$row['user_id'], (int)$row['class_id'],
            $row['status'], $row['enrolled_at'], $row['updated_at'],
            $row['user_name'] ?? null, $row['class_name'] ?? null);
    }
}
?>