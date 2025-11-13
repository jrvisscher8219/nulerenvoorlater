<?php
/**
 * DEBUG SCRIPT - Login Diagnostics
 * VERWIJDER NA GEBRUIK!
 */

// Toon alle errors
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 Login Debug Informatie</h1>";
echo "<p style='color: red;'><strong>⚠️ VERWIJDER DIT BESTAND NA GEBRUIK!</strong></p>";
echo "<hr>";

// Test 1: Config laden
try {
    require_once __DIR__ . '/../config.php';
    echo "✅ Config loaded<br>";
    echo "📋 ADMIN_USERNAME from config: <strong>" . htmlspecialchars(ADMIN_USERNAME) . "</strong><br>";
    echo "📋 ADMIN_PASSWORD from config: <strong>" . htmlspecialchars(ADMIN_PASSWORD) . "</strong><br>";
} catch (Exception $e) {
    die("❌ Config error: " . $e->getMessage());
}

echo "<hr>";

// Test 2: Database connectie
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    echo "✅ Database connected<br>";
} catch (PDOException $e) {
    die("❌ Database error: " . $e->getMessage());
}

echo "<hr>";

// Test 3: Haal admin gegevens op
try {
    $stmt = $pdo->query("SELECT id, username, password_hash, email, is_active, created_at, last_login FROM admin_users");
    $users = $stmt->fetchAll();
    
    echo "<h2>👥 Admin Users in Database:</h2>";
    echo "<p>Aantal admins: " . count($users) . "</p>";
    
    foreach ($users as $user) {
        echo "<div style='background: #f0f0f0; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
        echo "<strong>ID:</strong> " . $user['id'] . "<br>";
        echo "<strong>Username:</strong> " . htmlspecialchars($user['username']) . "<br>";
        echo "<strong>Email:</strong> " . htmlspecialchars($user['email']) . "<br>";
        echo "<strong>Is Active:</strong> " . ($user['is_active'] ? '✅ Yes' : '❌ No') . "<br>";
        echo "<strong>Password Hash:</strong> <code style='font-size: 10px;'>" . htmlspecialchars(substr($user['password_hash'], 0, 60)) . "...</code><br>";
        echo "<strong>Hash Type:</strong> ";
        
        if (strpos($user['password_hash'], '$2y$') === 0) {
            echo "BCRYPT<br>";
        } elseif (strpos($user['password_hash'], '$argon2id$') === 0) {
            echo "ARGON2ID<br>";
        } elseif (strpos($user['password_hash'], '$argon2i$') === 0) {
            echo "ARGON2I<br>";
        } else {
            echo "⚠️ UNKNOWN or INVALID<br>";
        }
        
        echo "<strong>Created:</strong> " . $user['created_at'] . "<br>";
        echo "<strong>Last Login:</strong> " . ($user['last_login'] ?? 'Never') . "<br>";
        echo "</div>";
    }
} catch (PDOException $e) {
    die("❌ Query error: " . $e->getMessage());
}

echo "<hr>";

// Test 4: Password Verificatie Test
echo "<h2>🔐 Password Verification Test</h2>";

$configUsername = ADMIN_USERNAME;
$configPassword = ADMIN_PASSWORD;

try {
    $stmt = $pdo->prepare("SELECT username, password_hash FROM admin_users WHERE username = ?");
    $stmt->execute([$configUsername]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "✅ User '<strong>" . htmlspecialchars($user['username']) . "</strong>' found in database<br><br>";
        
        echo "<strong>Testing password verification:</strong><br>";
        echo "Config password: '<code>" . htmlspecialchars($configPassword) . "</code>'<br>";
        echo "Database hash: '<code style='font-size: 10px;'>" . htmlspecialchars(substr($user['password_hash'], 0, 60)) . "...</code>'<br><br>";
        
        $verify = password_verify($configPassword, $user['password_hash']);
        
        if ($verify) {
            echo "✅ <strong style='color: green;'>PASSWORD MATCH! Login should work!</strong><br>";
        } else {
            echo "❌ <strong style='color: red;'>PASSWORD MISMATCH! This is the problem!</strong><br>";
            echo "<br><strong>Possible causes:</strong><br>";
            echo "1. Password in config.php is different from what was used to create the account<br>";
            echo "2. Password hash in database is corrupted<br>";
            echo "3. Wrong hashing algorithm was used<br>";
        }
    } else {
        echo "❌ User '<strong>" . htmlspecialchars($configUsername) . "</strong>' NOT FOUND in database<br>";
        echo "Username in config might be wrong!<br>";
    }
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}

echo "<hr>";

// Test 5: Generate new hash for comparison
echo "<h2>🔧 Generate New Hash (for testing)</h2>";
echo "<p>If you want to reset the password, use one of these hashes:</p>";

$testPassword = ADMIN_PASSWORD;

if (defined('PASSWORD_ARGON2ID')) {
    $newHash = password_hash($testPassword, PASSWORD_ARGON2ID);
    echo "<strong>ARGON2ID hash for password '</strong><code>" . htmlspecialchars($testPassword) . "</code><strong>':</strong><br>";
    echo "<textarea style='width: 100%; height: 60px; font-size: 10px;'>" . htmlspecialchars($newHash) . "</textarea><br><br>";
}

$bcryptHash = password_hash($testPassword, PASSWORD_BCRYPT);
echo "<strong>BCRYPT hash for password '</strong><code>" . htmlspecialchars($testPassword) . "</code><strong>':</strong><br>";
echo "<textarea style='width: 100%; height: 60px; font-size: 10px;'>" . htmlspecialchars($bcryptHash) . "</textarea><br><br>";

echo "<hr>";
echo "<h2>💡 Next Steps</h2>";
echo "<p>If password verification FAILED:</p>";
echo "<ol>";
echo "<li>Copy one of the hashes above</li>";
echo "<li>Go to phpMyAdmin</li>";
echo "<li>Run: <code>UPDATE admin_users SET password_hash = '[paste hash here]' WHERE username = '" . htmlspecialchars($configUsername) . "'</code></li>";
echo "<li>Try logging in again</li>";
echo "</ol>";

echo "<hr>";
echo "<p style='color: red; font-weight: bold;'>⚠️ DELETE THIS FILE AFTER USE: api/admin/debug_login.php</p>";
?>
