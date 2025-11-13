<?php
// Check admin accounts in database
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Check</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        h2 { color: #333; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; text-align: left; border: 1px solid #ddd; }
        th { background: #f4f4f4; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
<?php
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "<h2 class='success'>Database Connection: ✅ OK</h2>";
    
    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'admin_users'");
    if ($stmt->rowCount() === 0) {
        echo "<h2 class='error'>❌ Tabel 'admin_users' bestaat niet!</h2>";
        echo "<p>Je moet eerst sql/setup.sql uitvoeren.</p>";
        exit;
    }
    
    echo "<h2 class='success'>Tabel 'admin_users': ✅ Bestaat</h2>";
    
    // Get all admin users
    $stmt = $pdo->query("SELECT id, username, email, is_active, created_at, last_login FROM admin_users");
    $admins = $stmt->fetchAll();
    
    echo "<h2>Admin accounts in database:</h2>";
    if (count($admins) === 0) {
        echo "<p class='error'>❌ <strong>Geen admin accounts gevonden!</strong></p>";
        echo "<p>Ga naar <a href='create_admin.php'>create_admin.php</a> om een account aan te maken.</p>";
    } else {
        echo "<table>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Active</th><th>Created</th><th>Last Login</th></tr>";
        foreach ($admins as $admin) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($admin['id']) . "</td>";
            echo "<td>" . htmlspecialchars($admin['username']) . "</td>";
            echo "<td>" . htmlspecialchars($admin['email']) . "</td>";
            echo "<td>" . ($admin['is_active'] ? '✅ Ja' : '❌ Nee') . "</td>";
            echo "<td>" . htmlspecialchars($admin['created_at']) . "</td>";
            echo "<td>" . htmlspecialchars($admin['last_login'] ?? 'Nooit') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<br><p><a href='login.php'>Ga naar login pagina</a></p>";
    }
    
    echo "<hr>";
    echo "<h3>Config instellingen:</h3>";
    echo "<p><strong>ADMIN_USERNAME:</strong> " . htmlspecialchars(ADMIN_USERNAME) . "</p>";
    echo "<p><strong>ADMIN_EMAIL:</strong> " . htmlspecialchars(ADMIN_EMAIL) . "</p>";
    
} catch (PDOException $e) {
    echo "<h2 class='error'>❌ Database Error:</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
</body>
</html>
