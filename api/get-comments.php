<?php
/**
 * Get Comments API Endpoint
 * ==========================
 * Haalt goedgekeurde reacties op voor een specifieke blog
 */

// Load configuration
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/db.php';

// Set headers
header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: public, max-age=300'); // Cache for 5 minutes

try {
    // Get blog ID from query parameter
    $blog_id = isset($_GET['blog_id']) ? Security::sanitize($_GET['blog_id']) : '';
    
    if (empty($blog_id)) {
        Security::jsonResponse([
            'success' => false,
            'message' => 'Blog ID is required'
        ], 400);
    }
    
    // Get database connection
    $db = Database::getInstance()->getConnection();
    
    // Fetch approved comments for this blog
    $stmt = $db->prepare("
        SELECT 
            id,
            author_name,
            comment_text,
            created_at,
            approved_at
        FROM comments
        WHERE blog_id = ? 
        AND status = 'approved'
        ORDER BY created_at ASC
    ");
    
    $stmt->execute([$blog_id]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format timestamps for display
    foreach ($comments as &$comment) {
        $comment['created_at_formatted'] = formatDateTime($comment['created_at']);
        $comment['time_ago'] = timeAgo($comment['created_at']);
        
        // Remove email from output (privacy)
        unset($comment['author_email']);
    }
    
    Security::jsonResponse([
        'success' => true,
        'comments' => $comments,
        'count' => count($comments)
    ]);
    
} catch (PDOException $e) {
    Security::logError('Database error in get-comments: ' . $e->getMessage());
    Security::jsonResponse([
        'success' => false,
        'message' => 'Failed to load comments'
    ], 500);
} catch (Exception $e) {
    Security::logError('Error in get-comments: ' . $e->getMessage());
    Security::jsonResponse([
        'success' => false,
        'message' => 'Failed to load comments'
    ], 500);
}

/**
 * Format DateTime
 */
function formatDateTime($datetime) {
    $dt = new DateTime($datetime);
    $dt->setTimezone(new DateTimeZone('Europe/Amsterdam'));
    return $dt->format('d-m-Y H:i');
}

/**
 * Time Ago Helper
 */
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return 'zojuist';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' ' . ($mins == 1 ? 'minuut' : 'minuten') . ' geleden';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' ' . ($hours == 1 ? 'uur' : 'uur') . ' geleden';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' ' . ($days == 1 ? 'dag' : 'dagen') . ' geleden';
    } elseif ($diff < 2592000) {
        $weeks = floor($diff / 604800);
        return $weeks . ' ' . ($weeks == 1 ? 'week' : 'weken') . ' geleden';
    } else {
        $dt = new DateTime($datetime);
        return $dt->format('d-m-Y');
    }
}
