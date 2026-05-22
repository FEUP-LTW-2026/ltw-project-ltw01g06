<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/bootstrap.php');
  require_once(__DIR__ . '/../database/class.class.php');
  require_once(__DIR__ . '/../database/enrollment.class.php');
  require_once(__DIR__ . '/../database/waitlist.class.php');

  if (!$session->isLoggedIn()) {
    $session->addMessage('error', 'Tem de estar autenticado para se inscrever numa aula.');
    header('Location: ../pages/index.php');
    exit;
  }

  $classId = (int)($_POST['class_id'] ?? 0);
  $action  = $_POST['action'] ?? 'enroll'; // 'enroll' | 'cancel' | 'waitlist' | 'waitlist_leave'

  if ($classId <= 0) {
    $session->addMessage('error', 'Aula inválida.');
    header('Location: ../pages/schedule.php');
    exit;
  }

  $class = GymClass::getClass($db, $classId);

  if (!$class || !$class->isActive) {
    $session->addMessage('error', 'Aula não encontrada.');
    header('Location: ../pages/schedule.php');
    exit;
  }

  $userId = $session->getId();

  try {
    if ($action === 'cancel') {
      // O trigger trg_promote_from_waitlist trata da lista de espera automaticamente
      $result = Enrollment::cancel($db, $userId, $classId);
      $message = $result ? 'Inscrição cancelada.' : 'Não foi possível cancelar a inscrição.';
      $type    = $result ? 'success' : 'error';
      $session->addMessage($type, $message);

    } elseif ($action === 'waitlist') {
      $entry = Waitlist::join($db, $userId, $classId);
      if ($entry) {
        $session->addMessage('success', 'Estás na lista de espera na posição ' . $entry->position . '.');
      } else {
        $session->addMessage('error', 'Já estás inscrito ou na lista de espera desta aula.');
      }

    } elseif ($action === 'waitlist_leave') {
      $result = Waitlist::leave($db, $userId, $classId);
      $message = $result ? 'Removido da lista de espera.' : 'Não estavas na lista de espera.';
      $type    = $result ? 'success' : 'error';
      $session->addMessage($type, $message);

    } else {
      // Inscrição normal
      if (!$class->hasAvailableSpots()) {
        $session->addMessage('error', 'Esta aula está cheia. Podes entrar na lista de espera.');
      } else {
        $enrollment = Enrollment::enroll($db, $userId, $classId);
        if ($enrollment) {
          $session->addMessage('success', 'Inscrito na aula "' . $class->name . '" com sucesso!');
        } else {
          $session->addMessage('error', 'Já estás inscrito nesta aula.');
        }
      }
    }

  } catch (\Exception $e) {
    // O trigger da DB pode lançar "Class is full" se houver race condition
    $session->addMessage('error', 'Não foi possível completar a operação. Tenta novamente.');
  }

  header('Location: ../pages/schedule.php');
?>
