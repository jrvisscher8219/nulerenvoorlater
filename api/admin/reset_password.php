<?php
/**
 * Reset Admin Password
 * Gebruik dit alleen als je je wachtwoord vergeten bent!
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';

?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Admin Password</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        h2 { color: #333; }
        .success { color: green; padding: 15px; background: #e8f5e9; border-radius: 5px; }
        .error { color: red; padding: 15px; background: #ffebee; border-radius: 5px; }
        .info { padding: 15px; background: #fff3e0; border-radius: 5px; margin: 20px 0; }
        input[type="text"], input[type="password"] { width: 100%; padding: 10px; margin: 10px 0; }
        button { padding: 10px 20px; background: #2196F3; color: white; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #1976D2; }
    </style>
</head>
<body>
    <h1>🔑 Reset Admin Password</h1>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';
    
    if (empty($username) || empty($newPassword)) {
        echo '<div class="error">❌ Vul beide velden in!</div>';
    } else {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            
            // Check if user exists
            $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if (!$user) {
                echo '<div class="error">❌ Gebruiker "' . htmlspecialchars($username) . '" niet gevonden!</div>';
            } else {
                // Hash new password
                if (defined('PASSWORD_ARGON2ID')) {
                    $passwordHash = password_hash($newPassword, PASSWORD_ARGON2ID);
                } else {
                    $passwordHash = password_hash($newPassword, PASSWORD_BCRYPT);
                }
                
                // Update password
                $stmt = $pdo->prepare("UPDATE admin_users SET password_hash = ? WHERE id = ?");
                $stmt->execute([$passwordHash, $user['id']]);
                
                echo '<div class="success">';
                echo '<h2>✅ Wachtwoord succesvol gewijzigd!</h2>';
                echo '<p><strong>Gebruiker:</strong> ' . htmlspecialchars($username) . '</p>';
                echo '<p><strong>Nieuw wachtwoord:</strong> ' . htmlspecialchars($newPassword) . '</p>';
                echo '<p><a href="login.php">→ Ga naar login pagina</a></p>';
                echo '</div>';
                
                echo '<div class="info">';
                echo '<strong>⚠️ BELANGRIJK:</strong> Verwijder dit bestand na gebruik voor beveiliging!';
                echo '</div>';
            }
            
        } catch (PDOException $e) {
            echo '<div class="error">❌ Database fout: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
} else {
    // Show form
    ?>
    <div class="info">
        <p>Gebruik dit formulier om je admin wachtwoord opnieuw in te stellen.</p>
        <p><strong>Let op:</strong> Verwijder dit bestand na gebruik!</p>
    </div>
    
    <form method="POST">
        <label for="username"><strong>Gebruikersnaam:</strong></label>
        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars(ADMIN_USERNAME); ?>" required>
        
        <label for="new_password"><strong>Nieuw wachtwoord:</strong></label>
        <input type="password" id="new_password" name="new_password" placeholder="Nieuw wachtwoord" required>
        
        <button type="submit">Reset Wachtwoord</button>
    </form>
    
    <p style="margin-top: 30px;"><a href="login.php">← Terug naar login</a></p>
    <?php
}
?>

</body>
</html>
