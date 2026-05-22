<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../templates/common.tpl.php');

  $session = new Session();

  if ($session->isLoggedIn()) {
    header('Location: profile.php');
    exit;
  }

  drawHeader($session, 'Registar');
?>

<section class="auth-page">
  <div class="auth-container">
    <h1><i class="fas fa-user-plus"></i> Criar Conta</h1>
    <p>Junta-te a comunidade Equilibrio Gym</p>
    
    <form action="../actions/action_register.php" method="post" class="auth-form">
      <div class="form-group">
        <label for="name">Nome Completo *</label>
        <input type="text" id="name" name="name" placeholder="O teu nome" required>
      </div>
      
      <div class="form-group">
        <label for="email">Email *</label>
        <input type="email" id="email" name="email" placeholder="email@exemplo.com" required>
      </div>
      
      <div class="form-group">
        <label for="phone">Telefone</label>
        <input type="tel" id="phone" name="phone" placeholder="+351 912 345 678">
      </div>
      
      <div class="form-group">
        <label for="password">Password *</label>
        <input type="password" id="password" name="password" placeholder="Minimo 6 caracteres" required minlength="6">
      </div>
      
      <div class="form-group">
        <label for="confirm_password">Confirmar Password *</label>
        <input type="password" id="confirm_password" name="confirm_password" placeholder="Repete a password" required>
      </div>
      
      <button type="submit" class="btn btn-primary btn-block">
        <i class="fas fa-user-plus"></i> Criar Conta
      </button>
    </form>
    
    <p class="auth-switch">
      Ja tens conta? <a href="login.php">Entrar</a>
    </p>
  </div>
</section>

<?php
  drawFooter();
?>
