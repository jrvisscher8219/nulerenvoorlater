<?php
/**
 * Admin Dashboard
 * ================
 * Moderatie interface voor reacties
 */

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/security.php';
require_once dirname(__DIR__) . '/db.php';

Security::startSecureSession();

// Check if logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Get filter
    $filter = $_GET['filter'] ?? 'pending';
    $valid_filters = ['pending', 'approved', 'rejected', 'all'];
    if (!in_array($filter, $valid_filters)) {
        $filter = 'pending';
    }
    
    // Build query
    $where = $filter === 'all' ? '' : "WHERE status = ?";
    $stmt = $db->prepare("
        SELECT id, blog_id, author_name, author_email, comment_text, status, spam_score, created_at
        FROM comments
        {$where}
        ORDER BY created_at DESC
        LIMIT 100
    ");
    
    if ($filter === 'all') {
        $stmt->execute();
    } else {
        $stmt->execute([$filter]);
    }
    
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get stats
    $stats = $db->query("
        SELECT 
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
            COUNT(*) as total
        FROM comments
    ")->fetch(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    die('Database error: ' . $e->getMessage());
}

$csrf_token = Security::generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../../css/styles.min.css">
    <style>
        .dashboard { max-width: 1200px; margin: 2rem auto; padding: 0 1rem; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
        .stat-card { background: #fff; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .stat-number { font-size: 2rem; font-weight: bold; color: var(--accent); }
        .comment-card { background: #fff; padding: 1.5rem; border-radius: 12px; margin-bottom: 1rem; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .comment-meta { display: flex; gap: 1rem; flex-wrap: wrap; font-size: 0.9rem; color: var(--muted); margin-bottom: 0.5rem; }
        .comment-text { background: #f8f8f8; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
        .comment-actions { display: flex; gap: 0.5rem; margin-top: 1rem; }
        .btn-small { padding: 0.5rem 1rem; border-radius: 6px; border: none; cursor: pointer; font-size: 0.9rem; }
        .btn-approve { background: #4caf50; color: #fff; }
        .btn-reject { background: #f44336; color: #fff; }
        .btn-delete { background: #999; color: #fff; }
        .filters { display: flex; gap: 0.5rem; margin-bottom: 2rem; flex-wrap: wrap; }
        .filter-btn { padding: 0.5rem 1rem; border-radius: 999px; border: 2px solid var(--accent); background: #fff; color: var(--accent); cursor: pointer; text-decoration: none; }
        .filter-btn.active { background: var(--accent); color: #fff; }
        .spam-badge { background: #ff9800; color: #fff; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; }
    </style>
</head>
<body>
    <div class="dashboard">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1>Reactie Moderatie</h1>
            <div>
                <span>Ingelogd als: <strong><?php echo htmlspecialchars($_SESSION['admin_username']); ?></strong></span> |
                <a href="logout.php">Uitloggen</a> |
                <a href="../../index.html">← Site</a>
            </div>
        </div>
        
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['pending']; ?></div>
                <div class="muted">Te beoordelen</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['approved']; ?></div>
                <div class="muted">Goedgekeurd</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['rejected']; ?></div>
                <div class="muted">Afgewezen</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total']; ?></div>
                <div class="muted">Totaal</div>
            </div>
        </div>
        
        <div class="filters">
            <a href="?filter=pending" class="filter-btn <?php echo $filter === 'pending' ? 'active' : ''; ?>">Te beoordelen (<?php echo $stats['pending']; ?>)</a>
            <a href="?filter=approved" class="filter-btn <?php echo $filter === 'approved' ? 'active' : ''; ?>">Goedgekeurd</a>
            <a href="?filter=rejected" class="filter-btn <?php echo $filter === 'rejected' ? 'active' : ''; ?>">Afgewezen</a>
            <a href="?filter=all" class="filter-btn <?php echo $filter === 'all' ? 'active' : ''; ?>">Alles</a>
        </div>
        
        <?php if (empty($comments)): ?>
            <p class="muted">Geen reacties gevonden voor dit filter.</p>
        <?php else: ?>
            <?php foreach ($comments as $comment): ?>
                <div class="comment-card" data-id="<?php echo $comment['id']; ?>">
                    <div class="comment-meta">
                        <span><strong><?php echo htmlspecialchars($comment['author_name']); ?></strong></span>
                        <span><?php echo htmlspecialchars($comment['author_email']); ?></span>
                        <span>Blog: <?php echo htmlspecialchars($comment['blog_id']); ?></span>
                        <span><?php echo date('d-m-Y H:i', strtotime($comment['created_at'])); ?></span>
                        <span>Status: <strong><?php echo $comment['status']; ?></strong></span>
                        <?php if ($comment['spam_score'] > 0.3): ?>
                            <span class="spam-badge">Spam: <?php echo round($comment['spam_score'] * 100); ?>%</span>
                        <?php endif; ?>
                    </div>
                    <div class="comment-text">
                        <?php echo nl2br(htmlspecialchars($comment['comment_text'])); ?>
                    </div>
                    <div class="comment-actions">
                        <?php if ($comment['status'] !== 'approved'): ?>
                            <button onclick="moderateComment(<?php echo $comment['id']; ?>, 'approved')" class="btn-small btn-approve">✓ Goedkeuren</button>
                        <?php endif; ?>
                        <?php if ($comment['status'] !== 'rejected'): ?>
                            <button onclick="moderateComment(<?php echo $comment['id']; ?>, 'rejected')" class="btn-small btn-reject">✗ Afwijzen</button>
                        <?php endif; ?>
                        <button onclick="deleteComment(<?php echo $comment['id']; ?>)" class="btn-small btn-delete">🗑 Verwijderen</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <script>
        const csrfToken = '<?php echo $csrf_token; ?>';
        
        function moderateComment(id, action) {
            if (!confirm(`Weet je zeker dat je deze reactie wilt ${action === 'approved' ? 'goedkeuren' : 'afwijzen'}?`)) {
                return;
            }
            
            fetch('moderate.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ comment_id: id, action: action, csrf_token: csrfToken })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Fout: ' + data.message);
                }
            })
            .catch(e => alert('Error: ' + e));
        }
        
        function deleteComment(id) {
            if (!confirm('Weet je zeker dat je deze reactie wilt verwijderen? Dit kan niet ongedaan worden gemaakt.')) {
                return;
            }
            
            fetch('moderate.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ comment_id: id, action: 'delete', csrf_token: csrfToken })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Fout: ' + data.message);
                }
            })
            .catch(e => alert('Error: ' + e));
        }
    </script>
</body>
</html>
