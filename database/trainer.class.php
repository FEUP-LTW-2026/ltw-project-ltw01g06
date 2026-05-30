<?php
  declare(strict_types = 1);

  class TrainerProfile {
    public int $userId;
    public string $specializations;
    public string $certifications;
    public ?string $updatedAt;
    // Campos vindos do JOIN com users
    public ?string $fullName;
    public ?string $username;
    public ?string $email;
    public ?string $bio;
    public ?string $photo;

    public function __construct(
      int $userId,
      string $specializations,
      string $certifications,
      string $updatedAt,
      ?string $fullName = null,
      ?string $username = null,
      ?string $email    = null,
      ?string $bio      = null,
      ?string $photo    = null
    ) {
      $this->userId          = $userId;
      $this->specializations = $specializations;
      $this->certifications  = $certifications;
      $this->updatedAt       = $updatedAt;
      $this->fullName        = $fullName;
      $this->username        = $username;
      $this->email           = $email;
      $this->bio             = $bio;
      $this->photo           = $photo;
    }

    public function save(PDO $db) : void {
      $stmt = $db->prepare('
        INSERT INTO trainer_profiles (user_id, specializations, certifications)
        VALUES (?, ?, ?)
        ON CONFLICT(user_id) DO UPDATE SET
          specializations = excluded.specializations,
          certifications  = excluded.certifications,
          updated_at      = datetime("now")
      ');
      $stmt->execute(array($this->userId, $this->specializations, $this->certifications));
    }

    public function getSpecializationsArray() : array {
      return array_values(array_filter(array_map('trim', explode(',', $this->specializations))));
    }

    public function getCertificationsArray() : array {
      return array_values(array_filter(array_map('trim', explode(',', $this->certifications))));
    }

    public static function getProfile(PDO $db, int $userId) : ?TrainerProfile {
      $stmt = $db->prepare('
        SELECT tp.*, u.full_name, u.username, u.email, u.bio, u.photo
        FROM trainer_profiles tp
        JOIN users u ON tp.user_id = u.id
        WHERE tp.user_id = ?
      ');
      $stmt->execute(array($userId));

      if ($row = $stmt->fetch()) {
        return self::hydrate($row);
      }
      return null;
    }

    public static function getAllProfiles(PDO $db) : array {
    $stmt = $db->query('
        SELECT tp.*, u.full_name, u.username, u.email, u.bio, u.photo
        FROM trainer_profiles tp
        JOIN users u ON tp.user_id = u.id
        ORDER BY u.full_name
    ');
    $profiles = array();
    while ($row = $stmt->fetch()) {
        $profiles[] = self::hydrate($row);
    }
    return $profiles;
}

    private static function hydrate(array $row) : self {
      return new self(
        (int)$row['user_id'],
        $row['specializations'] ?? '',
        $row['certifications']  ?? '',
        $row['updated_at'] ?? null,  
        $row['full_name'] ?? null,
        $row['username']  ?? null,
        $row['email']     ?? null,
        $row['bio']       ?? null,
        $row['photo']     ?? 'default_profile.png'
      );
    }
  }
?>
