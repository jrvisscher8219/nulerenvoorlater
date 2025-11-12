<?php
/**
 * Admin - Change Password
 */

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/security.php';
require_once dirname(__DIR__) . '/db.php';

Security::startSecureSession();

// Require login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!Security::validateCSRFToken($csrf)) {
        $error = 'Beveiligingsvalidatie mislukt.';
    } elseif (empty($current) || empty($new) || empty($confirm)) {
        $error = 'Alle velden zijn verplicht.';
    } elseif ($new !== $confirm) {
        $error = 'Nieuwe wachtwoorden komen niet overeen.';
    } elseif (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d).{12,}$/', $new)) {
        $error = 'Wachtwoord moet minimaal 12 tekens zijn en een hoofdletter, kleine letter en cijfer bevatten.';
    } else {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare('SELECT id, password_hash FROM admin_users WHERE id = ?');
            $stmt->execute([$_SESSION['admin_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || !password_verify($current, $user['password_hash'])) {
                $error = 'Huidig wachtwoord is onjuist.';
            } else {
                // Hash new password
                if (defined('PASSWORD_ARGON2ID')) {
                    $hash = password_hash($new, PASSWORD_ARGON2ID);
                } else {
                    $hash = password_hash($new, PASSWORD_BCRYPT);
                }

                $upd = $db->prepare('UPDATE admin_users SET password_hash = ? WHERE id = ?');
                $upd->execute([$hash, $user['id']]);
                $success = 'Wachtwoord succesvol gewijzigd.';
            }
        } catch (Exception $e) {
            $error = 'Er ging iets mis. Probeer later opnieuw.';
            Security::logError('Change password error: ' . $e->getMessage());
        }
    }
}

$csrf_token = Security::generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Wachtwoord wijzigen — <?php echo SITE_NAME; ?></title>
  <link rel="stylesheet" href="../../css/styles.min.css">
  <style>
    .container { max-width: 520px; margin: 60px auto; background: #fff; padding: 24px; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,.1); }
    .error { background:#fee; color:#c33; padding:12px; border-radius:8px; margin-bottom:12px; }
    .success { background:#e8f6ef; color:#2a7a4b; padding:12px; border-radius:8px; margin-bottom:12px; }
    label { display:block; font-weight:600; margin-top:12px; }
    input[type=password] { width:100%; padding:.75rem; border:2px solid var(--light); border-radius:8px; margin-top:6px; }
    .actions { display:flex; gap:12px; align-items:center; margin-top:16px; }
    .muted { color: var(--muted); font-size: .9rem; }
  </style>
  <meta name="robots" content="noindex,nofollow">
  <meta http-equiv="X-Frame-Options" content="DENY">
  <meta http-equiv="X-Content-Type-Options" content="nosniff">
  <meta http-equiv="Referrer-Policy" content="strict-origin-when-cross-origin">
  <meta http-equiv="Permissions-Policy" content="camera=(), microphone=(), geolocation=()">
  <meta http-equiv="Content-Security-Policy" content="frame-ancestors 'none'">
  
</head>
<body>
  <div class="container">
    <h1>Wachtwoord wijzigen</h1>
    <p class="muted">Ingelogd als <strong><?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'admin'); ?></strong></p>

    <?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <?php if ($success): ?><div class="success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

    <form method="POST" autocomplete="off">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

      <label for="current_password">Huidig wachtwoord</label>
      <input id="current_password" name="current_password" type="password" required>

      <label for="new_password">Nieuw wachtwoord</label>
      <input id="new_password" name="new_password" type="password" required>
      <p class="muted">Minimaal 12 tekens, bevat hoofdletter, kleine letter en cijfer.</p>

      <label for="confirm_password">Bevestig nieuw wachtwoord</label>
      <input id="confirm_password" name="confirm_password" type="password" required>

      <div class="actions">
        <button type="submit" class="btn-primary">Wijzig wachtwoord</button>
        <a class="btn-secondary" href="dashboard.php">Terug naar dashboard</a>
      </div>
    </form>
  </div>
</body>
</html>
