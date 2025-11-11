<?php
// Debug mode - toon alle errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "Script started...<br>";

// Test 1: Config laden
try {
    require_once __DIR__ . '/../config.php';
    echo "✅ Config loaded<br>";
} catch (Exception $e) {
    die("❌ Config error: " . $e->getMessage());
}

// Test 2: Database connectie
try {
    echo "Connecting to database...<br>";
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    echo "DSN: " . htmlspecialchars($dsn) . "<br>";
    
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    echo "✅ Database connected<br>";
} catch (PDOException $e) {
    die("❌ Database error: " . $e->getMessage());
}

// Test 3: Check tabellen
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'admin_users'");
    if ($stmt->rowCount() === 0) {
        die('❌ Tabel admin_users bestaat niet. Voer eerst sql/setup.sql uit.');
    }
    echo "✅ Table admin_users exists<br>";
} catch (PDOException $e) {
    die("❌ Table check error: " . $e->getMessage());
}

// Test 4: Check bestaande admin
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM admin_users");
    $count = $stmt->fetchColumn();
    echo "✅ Current admin count: " . $count . "<br>";
    
    if ($count > 0) {
        echo '<h2>⚠️ Admin account bestaat al!</h2>';
        echo '<p><a href="login.php">Ga naar login</a></p>';
        echo '<p><strong>VERWIJDER create_admin.php voor beveiliging!</strong></p>';
        exit;
    }
} catch (PDOException $e) {
    die("❌ Count error: " . $e->getMessage());
}

// Test 5: Maak admin aan
try {
    $username = ADMIN_USERNAME;
    $password = ADMIN_PASSWORD;
    $email = ADMIN_EMAIL;
    
    echo "Creating admin...<br>";
    echo "Username: " . htmlspecialchars($username) . "<br>";
    echo "Email: " . htmlspecialchars($email) . "<br>";
    
    // Check PHP versie voor PASSWORD_ARGON2ID
    if (defined('PASSWORD_ARGON2ID')) {
        $passwordHash = password_hash($password, PASSWORD_ARGON2ID);
        echo "✅ Using ARGON2ID<br>";
    } else {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        echo "⚠️ Using BCRYPT (PHP < 7.2)<br>";
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO admin_users (username, password_hash, email, created_at)
        VALUES (:username, :password_hash, :email, NOW())
    ");
    
    $stmt->execute([
        'username' => $username,
        'password_hash' => $passwordHash,
        'email' => $email
    ]);
    
    echo '<h2>✅ Admin account aangemaakt!</h2>';
    echo '<h3>Login gegevens:</h3>';
    echo '<p><strong>Username:</strong> ' . htmlspecialchars($username) . '</p>';
    echo '<p><strong>Password:</strong> ' . htmlspecialchars($password) . '</p>';
    echo '<p><strong>Email:</strong> ' . htmlspecialchars($email) . '</p>';
    echo '<h3>⚠️ BELANGRIJK:</h3>';
    echo '<ul>';
    echo '<li>VERWIJDER dit bestand: api/admin/create_admin.php</li>';
    echo '<li>VERWIJDER het wachtwoord uit config.php</li>';
    echo '</ul>';
    echo '<p><a href="login.php">→ Ga naar login</a></p>';
    
} catch (PDOException $e) {
    die("❌ Insert error: " . $e->getMessage());
}

