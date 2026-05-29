<?php
declare(strict_types=1);

class Review {
    public int $id;
    public int $userId;
    public int $classId;
    public int $rating;
    public ?string $comment;
    public string $createdAt;
    public ?string $userName;
    public ?string $className;

    public function __construct(
        int $id,
        int $userId,
        int $classId,
        int $rating,
        ?string $comment,
        string $createdAt,
        ?string $userName = null,
        ?string $className = null
    ) {
        $this->id        = $id;
        $this->userId    = $userId;
        $this->classId   = $classId;
        $this->rating    = $rating;
        $this->comment   = $comment;
        $this->createdAt = $createdAt;
        $this->userName  = $userName;
        $this->className = $className;
    }

    // Cria ou atualiza a review do utilizador para esta aula (UNIQUE user_id, class_id)
    public static function upsert(PDO $db, int $userId, int $classId, int $rating, ?string $comment): bool {
        $stmt = $db->prepare('
            INSERT INTO reviews (user_id, class_id, rating, comment)
            VALUES (?, ?, ?, ?)
            ON CONFLICT(user_id, class_id) DO UPDATE SET
                rating    = excluded.rating,
                comment   = excluded.comment,
                created_at = datetime("now")
        ');
        $stmt->execute(array($userId, $classId, $rating, $comment));
        return $stmt->rowCount() > 0;
    }

    public static function delete(PDO $db, int $userId, int $classId): bool {
        $stmt = $db->prepare('DELETE FROM reviews WHERE user_id = ? AND class_id = ?');
        $stmt->execute(array($userId, $classId));
        return $stmt->rowCount() > 0;
    }

    public static function getUserReview(PDO $db, int $userId, int $classId): ?Review {
        $stmt = $db->prepare('
            SELECT r.*, u.full_name AS user_name, c.name AS class_name
            FROM reviews r
            LEFT JOIN users u ON r.user_id = u.id
            LEFT JOIN classes c ON r.class_id = c.id
            WHERE r.user_id = ? AND r.class_id = ?
        ');
        $stmt->execute(array($userId, $classId));

        if ($row = $stmt->fetch()) {
            return self::hydrate($row);
        }
        return null;
    }

    public static function getClassReviews(PDO $db, int $classId): array {
        $stmt = $db->prepare('
            SELECT r.*, u.full_name AS user_name, c.name AS class_name
            FROM reviews r
            LEFT JOIN users u ON r.user_id = u.id
            LEFT JOIN classes c ON r.class_id = c.id
            WHERE r.class_id = ?
            ORDER BY r.created_at DESC
        ');
        $stmt->execute(array($classId));

        $reviews = array();
        while ($row = $stmt->fetch()) {
            $reviews[] = self::hydrate($row);
        }
        return $reviews;
    }

    // Devolve ['average' => float, 'total' => int]
    public static function getClassStats(PDO $db, int $classId): array {
        $stmt = $db->prepare('
            SELECT COUNT(*) AS total, AVG(rating) AS average
            FROM reviews
            WHERE class_id = ?
        ');
        $stmt->execute(array($classId));
        $row = $stmt->fetch();

        return [
            'total'   => (int)($row['total'] ?? 0),
            'average' => round((float)($row['average'] ?? 0), 1),
        ];
    }

    public static function countUserReviews(PDO $db, int $userId): int {
    $stmt = $db->prepare('
        SELECT COUNT(*) AS total
        FROM reviews
        WHERE user_id = ?
    ');
    $stmt->execute(array($userId));
    $row = $stmt->fetch();
    return (int)($row['total'] ?? 0);
}

    private static function hydrate(array $row): self {
        return new self(
            (int)$row['id'],
            (int)$row['user_id'],
            (int)$row['class_id'],
            (int)$row['rating'],
            $row['comment'] ?? null,
            $row['created_at'],
            $row['user_name'] ?? null,
            $row['class_name'] ?? null
        );
    }
}
?>