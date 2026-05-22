<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../templates/common.tpl.php');

  $session = new Session();

  if ($session->isLoggedIn()) {
    header('Location: profile.php');
    exit;
  }

  drawHeader($session, 'Login');
?>

<section class="auth-page">
  <div class="auth-container">
    <h1><i class="fas fa-sign-in-alt"></i> Entrar</h1>
    <p>Bem-vindo de volta!</p>
    
    <form action="../actions/action_login.php" method="post" class="auth-form">
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="email@exemplo.com" required>
      </div>
      
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="A tua password" required>
      </div>
      
      <button type="submit" class="btn btn-primary btn-block">
        <i class="fas fa-sign-in-alt"></i> Entrar
      </button>
    </form>
    
    <p class="auth-switch">
      Ainda nao tens conta? <a href="register.php">Registar</a>
    </p>
  </div>
</section>

<?php
  drawFooter();
?>
