<?php
  declare(strict_types = 1);

  class GymClass {
    public int $id;
    public string $name;
    public string $description;
    public string $type;
    public string $level;
    public ?int $trainerId;
    public ?string $trainerName;
    public string $dayOfWeek;
    public string $startTime;
    public int $durationMin;
    public int $capacity;
    public int $currentEnrollment;
    public bool $isActive;
    public string $createdAt;

    public function __construct(
      int $id,
      string $name,
      string $description,
      string $type,
      string $level,
      ?int $trainerId,
      ?string $trainerName,
      string $dayOfWeek,
      string $startTime,
      int $durationMin,
      int $capacity,
      int $currentEnrollment,
      bool $isActive,
      string $createdAt
    ) {
      $this->id                = $id;
      $this->name              = $name;
      $this->description       = $description;
      $this->type              = $type;
      $this->level             = $level;
      $this->trainerId         = $trainerId;
      $this->trainerName       = $trainerName;
      $this->dayOfWeek         = $dayOfWeek;
      $this->startTime         = $startTime;
      $this->durationMin       = $durationMin;
      $this->capacity          = $capacity;
      $this->currentEnrollment = $currentEnrollment;
      $this->isActive          = $isActive;
      $this->createdAt         = $createdAt;
    }

    public function hasAvailableSpots() : bool {
      return $this->currentEnrollment < $this->capacity;
    }

    public function getAvailableSpots() : int {
      return max(0, $this->capacity - $this->currentEnrollment);
    }

    // Devolve hora de fim calculada a partir de start_time + duration_min
    public function getEndTime() : string {
      $start = \DateTime::createFromFormat('H:i', $this->startTime);
      $start->modify('+' . $this->durationMin . ' minutes');
      return $start->format('H:i');
    }

    public static function getClass(PDO $db, int $id) : ?GymClass {
      $stmt = $db->prepare('
        SELECT c.*,
          u.full_name AS trainer_name,
          (SELECT COUNT(*) FROM enrollments WHERE class_id = c.id AND status = "active") AS current_enrollment
        FROM classes c
        LEFT JOIN users u ON c.trainer_id = u.id
        WHERE c.id = ?
      ');
      $stmt->execute(array($id));

      if ($class = $stmt->fetch()) {
        return self::hydrate($class);
      }
      return null;
    }

    public static function getAllClasses(PDO $db, bool $activeOnly = true) : array {
      $where = $activeOnly ? 'WHERE c.is_active = 1' : '';

      $stmt = $db->query('
        SELECT c.*,
          u.full_name AS trainer_name,
          (SELECT COUNT(*) FROM enrollments WHERE class_id = c.id AND status = "active") AS current_enrollment
        FROM classes c
        LEFT JOIN users u ON c.trainer_id = u.id
        ' . $where . '
        ORDER BY
          CASE c.day_of_week
            WHEN "monday"    THEN 1
            WHEN "tuesday"   THEN 2
            WHEN "wednesday" THEN 3
            WHEN "thursday"  THEN 4
            WHEN "friday"    THEN 5
            WHEN "saturday"  THEN 6
            WHEN "sunday"    THEN 7
          END,
          c.start_time
      ');

      $classes = array();
      while ($class = $stmt->fetch()) {
        $classes[] = self::hydrate($class);
      }
      return $classes;
    }

    public static function getClassesByDay(PDO $db, string $day) : array {
      $stmt = $db->prepare('
        SELECT c.*,
          u.full_name AS trainer_name,
          (SELECT COUNT(*) FROM enrollments WHERE class_id = c.id AND status = "active") AS current_enrollment
        FROM classes c
        LEFT JOIN users u ON c.trainer_id = u.id
        WHERE c.day_of_week = ? AND c.is_active = 1
        ORDER BY c.start_time
      ');
      $stmt->execute(array(strtolower($day)));

      $classes = array();
      while ($class = $stmt->fetch()) {
        $classes[] = self::hydrate($class);
      }
      return $classes;
    }

    public static function getClassesByTrainer(PDO $db, int $trainerId) : array {
      $stmt = $db->prepare('
        SELECT c.*,
          u.full_name AS trainer_name,
          (SELECT COUNT(*) FROM enrollments WHERE class_id = c.id AND status = "active") AS current_enrollment
        FROM classes c
        LEFT JOIN users u ON c.trainer_id = u.id
        WHERE c.trainer_id = ?
        ORDER BY
          CASE c.day_of_week
            WHEN "monday"    THEN 1
            WHEN "tuesday"   THEN 2
            WHEN "wednesday" THEN 3
            WHEN "thursday"  THEN 4
            WHEN "friday"    THEN 5
            WHEN "saturday"  THEN 6
            WHEN "sunday"    THEN 7
          END,
          c.start_time
      ');
      $stmt->execute(array($trainerId));

      $classes = array();
      while ($class = $stmt->fetch()) {
        $classes[] = self::hydrate($class);
      }
      return $classes;
    }

    private static function hydrate(array $row) : self {
      return new self(
        (int)$row['id'],
        $row['name'],
        $row['description'] ?? '',
        $row['type'],
        $row['level'],
        isset($row['trainer_id']) ? (int)$row['trainer_id'] : null,
        $row['trainer_name'] ?? null,
        $row['day_of_week'],
        $row['start_time'],
        (int)$row['duration_min'],
        (int)$row['capacity'],
        (int)($row['current_enrollment'] ?? 0),
        (bool)$row['is_active'],
        $row['created_at']
      );
    }
  }
?>
