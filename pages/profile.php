<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/user.class.php');
  require_once(__DIR__ . '/../database/enrollment.class.php');
  require_once(__DIR__ . '/../templates/common.tpl.php');
  require_once(__DIR__ . '/../templates/user.tpl.php');

  $session = new Session();

  if (!$session->isLoggedIn()) {
    $session->addMessage('error', 'Precisas de estar autenticado para ver o perfil.');
    header('Location: login.php');
    exit;
  }

  $db = getDatabaseConnection();
  $user = User::getUser($db, $session->getId());
  $enrollments = Enrollment::getUserEnrollments($db, $session->getId());

  if (!$user) {
    $session->addMessage('error', 'Utilizador nao encontrado.');
    header('Location: index.php');
    exit;
  }

  drawHeader($session, 'O Meu Perfil', '/css/pages/profile.css');
?>

<section class="page-header">
  <h1><i class="fas fa-user"></i> O Meu Perfil</h1>
  <p>Gere a tua conta e ve as tuas inscricoes.</p>
</section>

<section class="profile-section">
  <div class="profile-grid">
    <div class="profile-card">
      <h2><i class="fas fa-id-card"></i> Dados Pessoais</h2>
      <?php drawProfileForm($user); ?>
    </div>
    
    <div class="profile-sidebar">
      <div class="membership-info">
        <h3><i class="fas fa-star"></i> Membro</h3>
        <p class="membership-type"><?= htmlspecialchars(ucfirst($user->membershipType)) ?></p>
        <p class="membership-status <?= $user->membershipStatus ?>"><?= htmlspecialchars(ucfirst($user->membershipStatus)) ?></p>
        <p class="member-since">Membro desde: <?= htmlspecialchars(date('d/m/Y', strtotime($user->createdAt))) ?></p>
      </div>
      
      <?php drawUserEnrollments($enrollments); ?>
    </div>
  </div>
</section>

<?php
  drawFooter();
?>