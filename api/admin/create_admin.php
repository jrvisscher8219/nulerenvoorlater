<?php
/**
 * One-time admin creation script.
 * -------------------------------------------------
 * Gebruik ALLEEN eenmalig om het eerste admin account aan te maken.
 * STAPPEN:
 * 1. Upload dit bestand.
 * 2. Ga naar /api/admin/create_admin.php in je browser.
 * 3. Vul formulier in (sterk wachtwoord!).
 * 4. Na succes: verwijder dit bestand DIRECT van de server.
 */

require_once __DIR__ . '/../../api/config.php';
require_once __DIR__ . '/../../api/db.php';

$pdo = DB::getInstance()->getConnection();

// Controle: bestaat er al een admin?
$stmt = $pdo->query("SELECT COUNT(*) FROM admin_users");
$exists = $stmt->fetchColumn();
if ($exists > 0) {
    http_response_code(403);
    echo '<p>Er bestaat al een admin gebruiker. Verwijder dit bestand.</p>';
    exit;
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $pass = trim($_POST['password'] ?? '');

    if ($user === '' || strlen($user) < 3) {
        $errors[] = 'Gebruikersnaam minimaal 3 tekens.';
    }
    if (strlen($pass) < 12) {
        $errors[] = 'Wachtwoord minimaal 12 tekens.';
    }

    if (empty($errors)) {
        $hash = password_hash($pass, PASSWORD_ARGON2ID);
        $ins = $pdo->prepare('INSERT INTO admin_users (username, password_hash) VALUES (?, ?)');
        if ($ins->execute([$user, $hash])) {
            $success = true;
        } else {
            $errors[] = 'Kon admin niet aanmaken.';
        }
    }
}

?><!doctype html>
<html lang="nl">
<head><meta charset="utf-8"><title>Maak eerste admin</title></head>
<body style="font-family: system-ui; max-width: 600px; margin: 2rem auto;">
<h1>Eerste admin aanmaken</h1>
<?php if ($success): ?>
    <p style="color:green; font-weight:600;">Admin aangemaakt! Login nu via <code>/api/admin/login.php</code> en VERWIJDER dit bestand (create_admin.php).</p>
<?php else: ?>
    <?php if ($errors): ?><div style="background:#ffebee; padding:1rem; border-radius:8px;">&bull; <?= htmlspecialchars(implode('<br>&bull; ', $errors), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
    <form method="post" style="display:grid; gap:1rem; margin-top:1rem;">
        <label>Gebruikersnaam
            <input type="text" name="username" required minlength="3" style="width:100%; padding:.5rem;">
        </label>
        <label>Wachtwoord (min 12 tekens)
            <input type="password" name="password" required minlength="12" style="width:100%; padding:.5rem;">
        </label>
        <button type="submit" style="padding:.75rem 1rem; background:#507a76; color:#fff; border:none; border-radius:6px; cursor:pointer;">Aanmaken</button>
    </form>
    <p style="margin-top:2rem; font-size:.9rem; color:#555;">Verwijder dit bestand direct na succesvol aanmaken voor veiligheid.</p>
<?php endif; ?>
</body>
</html>