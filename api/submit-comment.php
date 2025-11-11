<?php
/**
 * Submit Comment API Endpoint
 * ============================
 * Ontvangt en valideert nieuwe reacties
 */

// Load configuration
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/db.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Security::jsonResponse([
        'success' => false,
        'message' => 'Method not allowed'
    ], 405);
}

// Set headers
header('Content-Type: application/json; charset=UTF-8');

try {
    // Get database connection
    $db = Database::getInstance()->getConnection();
    
    // Check rate limiting
    $rateCheck = Security::checkRateLimit($db, 'comment');
    if (!$rateCheck['allowed']) {
        Security::jsonResponse([
            'success' => false,
            'message' => $rateCheck['message'],
            'retry_after' => $rateCheck['retry_after'] ?? null
        ], 429);
    }
    
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    // If not JSON, try form data
    if (!$data) {
        $data = $_POST;
    }
    
    // Honeypot check (spam trap)
    if (!empty($data[HONEYPOT_FIELD])) {
        // Looks like spam, but don't tell them
        Security::jsonResponse([
            'success' => true,
            'message' => 'Reactie ontvangen! Deze wordt beoordeeld voordat deze zichtbaar wordt.'
        ]);
    }
    
    // Validate CSRF token
    if (!isset($data['csrf_token']) || !Security::validateCSRFToken($data['csrf_token'])) {
        Security::jsonResponse([
            'success' => false,
            'message' => 'Beveiligingsvalidatie mislukt. Ververs de pagina en probeer opnieuw.'
        ], 403);
    }
    
    // Verify reCAPTCHA (if enabled)
    if (RECAPTCHA_ENABLED) {
        if (!isset($data['recaptcha_token'])) {
            Security::jsonResponse([
                'success' => false,
                'message' => 'reCAPTCHA verificatie ontbreekt.'
            ], 400);
        }
        
        if (!Security::verifyRecaptcha($data['recaptcha_token'])) {
            Security::jsonResponse([
                'success' => false,
                'message' => 'reCAPTCHA verificatie mislukt. Probeer opnieuw.'
            ], 400);
        }
    }
    
    // Extract and sanitize inputs
    $blog_id = isset($data['blog_id']) ? Security::sanitize($data['blog_id']) : '';
    $author_name = isset($data['name']) ? Security::sanitize($data['name']) : '';
    $author_email = isset($data['email']) ? Security::sanitize($data['email'], 'email') : '';
    $comment_text = isset($data['comment']) ? trim($data['comment']) : '';
    
    // Validation
    $errors = [];
    
    if (empty($blog_id)) {
        $errors[] = 'Blog ID ontbreekt.';
    }
    
    if (!Security::validate($author_name, 'required')) {
        $errors[] = 'Naam is verplicht.';
    } elseif (!Security::validate($author_name, 'length', ['min' => 2, 'max' => 100])) {
        $errors[] = 'Naam moet tussen 2 en 100 tekens zijn.';
    }
    
    if (!Security::validate($author_email, 'required')) {
        $errors[] = 'E-mailadres is verplicht.';
    } elseif (!Security::validate($author_email, 'email')) {
        $errors[] = 'Ongeldig e-mailadres.';
    }
    
    if (!Security::validate($comment_text, 'required')) {
        $errors[] = 'Reactie mag niet leeg zijn.';
    } elseif (!Security::validate($comment_text, 'length', ['min' => COMMENT_MIN_LENGTH, 'max' => COMMENT_MAX_LENGTH])) {
        $errors[] = 'Reactie moet tussen ' . COMMENT_MIN_LENGTH . ' en ' . COMMENT_MAX_LENGTH . ' tekens zijn.';
    }
    
    if (!empty($errors)) {
        Security::jsonResponse([
            'success' => false,
            'message' => 'Validatie fouten',
            'errors' => $errors
        ], 400);
    }
    
    // Sanitize comment text (prevent XSS)
    $comment_text = Security::sanitize($comment_text);
    
    // Calculate spam score
    $spam_score = Security::calculateSpamScore($comment_text, $author_email, $author_name);
    
    // Determine status
    $status = 'pending';
    
    // Auto-approve trusted emails (if configured)
    if (in_array($author_email, TRUSTED_EMAILS)) {
        $status = 'approved';
    }
    
    // Auto-reject high spam score
    if ($spam_score > 0.8) {
        $status = 'rejected';
    }
    
    // Get client info
    $ip_address = LOG_IP_ADDRESSES ? Security::getClientIP() : null;
    $user_agent = LOG_USER_AGENTS ? ($_SERVER['HTTP_USER_AGENT'] ?? null) : null;
    
    // Insert comment into database
    $stmt = $db->prepare("
        INSERT INTO comments (
            blog_id, 
            author_name, 
            author_email, 
            comment_text, 
            status, 
            spam_score,
            ip_address, 
            user_agent
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $result = $stmt->execute([
        $blog_id,
        $author_name,
        $author_email,
        $comment_text,
        $status,
        $spam_score,
        $ip_address,
        $user_agent
    ]);
    
    if (!$result) {
        throw new Exception('Failed to save comment');
    }
    
    $comment_id = $db->lastInsertId();
    
    // Send email notification (if enabled and not spam)
    if (EMAIL_NOTIFICATIONS && $status !== 'rejected') {
        sendNotificationEmail($blog_id, $author_name, $author_email, $comment_text, $comment_id, $status);
    }
    
    // Log success
    Security::logError("New comment submitted: ID={$comment_id}, Blog={$blog_id}, Status={$status}, Spam={$spam_score}");
    
    // Response based on status
    $message = $status === 'approved' 
        ? 'Bedankt voor je reactie! Deze is nu zichtbaar.'
        : 'Bedankt voor je reactie! Deze wordt beoordeeld voordat deze zichtbaar wordt.';
    
    Security::jsonResponse([
        'success' => true,
        'message' => $message,
        'comment_id' => $comment_id,
        'status' => $status
    ]);
    
} catch (PDOException $e) {
    Security::logError('Database error in submit-comment: ' . $e->getMessage());
    Security::jsonResponse([
        'success' => false,
        'message' => 'Er ging iets mis. Probeer het later opnieuw.'
    ], 500);
} catch (Exception $e) {
    Security::logError('Error in submit-comment: ' . $e->getMessage());
    Security::jsonResponse([
        'success' => false,
        'message' => 'Er ging iets mis. Probeer het later opnieuw.'
    ], 500);
}

/**
 * Send Email Notification
 */
function sendNotificationEmail($blog_id, $name, $email, $comment, $comment_id, $status) {
    if (!EMAIL_NOTIFICATIONS) {
        return;
    }
    
    $to = NOTIFICATION_EMAIL;
    $subject = "[Nu leren voor later] Nieuwe reactie op: {$blog_id}";
    
    $body = "Er is een nieuwe reactie geplaatst op je blog.\n\n";
    $body .= "Blog: {$blog_id}\n";
    $body .= "Status: {$status}\n";
    $body .= "Naam: {$name}\n";
    $body .= "E-mail: {$email}\n";
    $body .= "Reactie ID: {$comment_id}\n\n";
    $body .= "Reactie:\n";
    $body .= str_repeat('-', 50) . "\n";
    $body .= $comment . "\n";
    $body .= str_repeat('-', 50) . "\n\n";
    
    if ($status === 'pending') {
        $body .= "Modereer deze reactie:\n";
        $body .= SITE_URL . "/api/admin/dashboard.php\n";
    }
    
    $headers = [];
    $headers[] = 'From: ' . SITE_NAME . ' <' . EMAIL_FROM . '>';
    $headers[] = 'Reply-To: ' . $email;
    $headers[] = 'Content-Type: text/plain; charset=UTF-8';
    
    @mail($to, $subject, $body, implode("\r\n", $headers));
}
