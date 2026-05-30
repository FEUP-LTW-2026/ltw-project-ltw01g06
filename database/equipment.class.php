<?php
declare(strict_types=1);

class Equipment {
    public int $id;
    public string $name;
    public ?string $category;
    public int $quantity;
    public int $available;
    public string $status;
    public ?string $notes;
    public string $updatedAt;

    public function __construct(
        int $id,
        string $name,
        ?string $category,
        int $quantity,
        int $available,
        string $status,
        ?string $notes,
        string $updatedAt
    ) {
        $this->id        = $id;
        $this->name      = $name;
        $this->category  = $category;
        $this->quantity  = $quantity;
        $this->available = $available;
        $this->status    = $status;
        $this->notes     = $notes;
        $this->updatedAt = $updatedAt;
    }

    public function getStatusLabel(): string {
        return match($this->status) {
            'available'   => 'Disponível',
            'maintenance' => 'Manutenção',
            'retired'     => 'Retirado',
            default       => ucfirst($this->status),
        };
    }

    public static function getAll(PDO $db): array {
        $stmt = $db->query('SELECT * FROM equipment ORDER BY category, name');
        $items = array();
        while ($row = $stmt->fetch()) {
            $items[] = self::hydrate($row);
        }
        return $items;
    }

    public static function getById(PDO $db, int $id): ?Equipment {
        $stmt = $db->prepare('SELECT * FROM equipment WHERE id = ?');
        $stmt->execute(array($id));
        if ($row = $stmt->fetch()) {
            return self::hydrate($row);
        }
        return null;
    }

    public static function create(PDO $db, string $name, ?string $category, int $quantity, int $available, string $status, ?string $notes): Equipment {
        $stmt = $db->prepare('
            INSERT INTO equipment (name, category, quantity, available, status, notes)
            VALUES (?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute(array($name, $category, $quantity, $available, $status, $notes));
        return self::getById($db, (int)$db->lastInsertId());
    }

    public function save(PDO $db): void {
        $stmt = $db->prepare('
            UPDATE equipment
            SET name = ?, category = ?, quantity = ?, available = ?, status = ?, notes = ?, updated_at = datetime("now")
            WHERE id = ?
        ');
        $stmt->execute(array($this->name, $this->category, $this->quantity, $this->available, $this->status, $this->notes, $this->id));
    }

    public static function delete(PDO $db, int $id): bool {
        $stmt = $db->prepare('DELETE FROM equipment WHERE id = ?');
        $stmt->execute(array($id));
        return $stmt->rowCount() > 0;
    }

    private static function hydrate(array $row): self {
        return new self(
            (int)$row['id'],
            $row['name'],
            $row['category'] ?? null,
            (int)$row['quantity'],
            (int)$row['available'],
            $row['status'],
            $row['notes'] ?? null,
            $row['updated_at']
        );
    }
}
?>
