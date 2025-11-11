<?php
/**
 * Admin Login Page
 * =================
 * Login interface voor moderatie toegang
 */

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/security.php';
require_once dirname(__DIR__) . '/db.php';

Security::startSecureSession();

// Als al ingelogd, redirect naar dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

// Handle login POST
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = Security::sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    // Validate CSRF
    if (!Security::validateCSRFToken($csrf_token)) {
        $error = 'Beveiligingsvalidatie mislukt.';
    } else {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Check rate limiting
            $rateCheck = Security::checkRateLimit($db, 'login');
            if (!$rateCheck['allowed']) {
                $error = $rateCheck['message'];
            } else {
                // Fetch admin user
                $stmt = $db->prepare("
                    SELECT id, username, password_hash, email, is_active 
                    FROM admin_users 
                    WHERE username = ?
                ");
                $stmt->execute([$username]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user && password_verify($password, $user['password_hash'])) {
                    if ($user['is_active'] != 1) {
                        $error = 'Account is gedeactiveerd.';
                    } else {
                        // Login successful
                        $_SESSION['admin_logged_in'] = true;
                        $_SESSION['admin_id'] = $user['id'];
                        $_SESSION['admin_username'] = $user['username'];
                        
                        // Update last login
                        $stmt = $db->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
                        $stmt->execute([$user['id']]);
                        
                        // Redirect to dashboard
                        header('Location: dashboard.php');
                        exit;
                    }
                } else {
                    $error = 'Ongeldige gebruikersnaam of wachtwoord.';
                }
            }
        } catch (Exception $e) {
            Security::logError('Login error: ' . $e->getMessage());
            $error = 'Login mislukt. Probeer opnieuw.';
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
    <title>Admin Login — <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../../css/styles.min.css">
    <style>
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 2rem;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        .login-form input[type="text"],
        .login-form input[type="password"] {
            width: 100%;
            padding: 0.75rem;
            margin: 0.5rem 0 1rem;
            border: 2px solid var(--light);
            border-radius: 8px;
            font-size: 1rem;
        }
        .login-form input:focus {
            outline: none;
            border-color: var(--accent);
        }
        .error-message {
            background: #fee;
            color: #c33;
            padding: 0.75rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Admin Login</h1>
        <p class="muted">Moderatie toegang voor <?php echo htmlspecialchars(SITE_NAME); ?></p>
        
        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" class="login-form">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            
            <label for="username">Gebruikersnaam:</label>
            <input type="text" id="username" name="username" required autofocus>
            
            <label for="password">Wachtwoord:</label>
            <input type="password" id="password" name="password" required>
            
            <button type="submit" class="btn-primary" style="width: 100%; margin-top: 1rem;">Inloggen</button>
        </form>
        
        <p class="small muted" style="margin-top: 2rem; text-align: center;">
            <a href="../../index.html">← Terug naar site</a>
        </p>
    </div>
</body>
</html>
