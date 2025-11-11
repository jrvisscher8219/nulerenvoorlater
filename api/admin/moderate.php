<?php
/**
 * Moderate Comment API
 * ====================
 * Goedkeuren, afwijzen of verwijderen van reacties
 */

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/security.php';
require_once dirname(__DIR__) . '/db.php';

Security::startSecureSession();

// Check if logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    Security::jsonResponse(['success' => false, 'message' => 'Not authenticated'], 401);
}

// Only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Security::jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate CSRF
    if (!isset($data['csrf_token']) || !Security::validateCSRFToken($data['csrf_token'])) {
        Security::jsonResponse(['success' => false, 'message' => 'Invalid CSRF token'], 403);
    }
    
    $comment_id = isset($data['comment_id']) ? (int)$data['comment_id'] : 0;
    $action = isset($data['action']) ? $data['action'] : '';
    
    if ($comment_id <= 0) {
        Security::jsonResponse(['success' => false, 'message' => 'Invalid comment ID'], 400);
    }
    
    if (!in_array($action, ['approved', 'rejected', 'delete'])) {
        Security::jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
    }
    
    $db = Database::getInstance()->getConnection();
    
    if ($action === 'delete') {
        // Delete comment
        $stmt = $db->prepare("DELETE FROM comments WHERE id = ?");
        $stmt->execute([$comment_id]);
        
        Security::jsonResponse(['success' => true, 'message' => 'Comment deleted']);
    } else {
        // Update status
        $stmt = $db->prepare("
            UPDATE comments 
            SET status = ?, approved_at = NOW(), approved_by = ?
            WHERE id = ?
        ");
        $stmt->execute([$action, $_SESSION['admin_username'], $comment_id]);
        
        Security::jsonResponse(['success' => true, 'message' => 'Comment updated']);
    }
    
} catch (Exception $e) {
    Security::logError('Moderate error: ' . $e->getMessage());
    Security::jsonResponse(['success' => false, 'message' => 'Operation failed'], 500);
}
