<?php
/**
 * Security Helper Functions
 * ==========================
 * Beveiligings functies voor het comment systeem
 */

// Voorkom directe toegang
if (!defined('DB_HOST')) {
    die('Direct access not permitted');
}

class Security {
    
    /**
     * Generate CSRF Token
     */
    public static function generateCSRFToken() {
        if (session_status() === PHP_SESSION_NONE) {
            self::startSecureSession();
        }
        
        $token = bin2hex(random_bytes(32));
        $_SESSION[CSRF_TOKEN_NAME] = $token;
        $_SESSION[CSRF_TOKEN_NAME . '_time'] = time();
        
        return $token;
    }
    
    /**
     * Validate CSRF Token
     */
    public static function validateCSRFToken($token) {
        if (session_status() === PHP_SESSION_NONE) {
            self::startSecureSession();
        }
        
        if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
            return false;
        }
        
        // Check token expiry
        if (isset($_SESSION[CSRF_TOKEN_NAME . '_time'])) {
            $age = time() - $_SESSION[CSRF_TOKEN_NAME . '_time'];
            if ($age > CSRF_TOKEN_LIFETIME) {
                return false;
            }
        }
        
        // Constant time comparison to prevent timing attacks
        return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
    }
    
    /**
     * Start Secure Session
     */
    public static function startSecureSession() {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        
        $secure = IS_HTTPS;
        $httponly = true;
        $samesite = 'Strict';
        
        ini_set('session.use_strict_mode', 1);
        ini_set('session.cookie_httponly', $httponly);
        ini_set('session.cookie_secure', $secure);
        ini_set('session.cookie_samesite', $samesite);
        ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
        
        session_name(SESSION_NAME);
        session_start();
        
        // Regenerate session ID periodically
        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = time();
        } else if (time() - $_SESSION['created'] > 1800) {
            session_regenerate_id(true);
            $_SESSION['created'] = time();
        }
    }
    
    /**
     * Sanitize Input
     */
    public static function sanitize($input, $type = 'string') {
        switch ($type) {
            case 'email':
                return filter_var($input, FILTER_SANITIZE_EMAIL);
                
            case 'int':
                return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
                
            case 'string':
            default:
                return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
        }
    }
    
    /**
     * Validate Input
     */
    public static function validate($input, $type, $options = []) {
        switch ($type) {
            case 'email':
                return filter_var($input, FILTER_VALIDATE_EMAIL) !== false;
                
            case 'length':
                $min = $options['min'] ?? 0;
                $max = $options['max'] ?? PHP_INT_MAX;
                $len = mb_strlen($input);
                return $len >= $min && $len <= $max;
                
            case 'required':
                return !empty($input);
                
            default:
                return true;
        }
    }
    
    /**
     * Get Client IP Address
     */
    public static function getClientIP() {
        $ip_keys = [
            'HTTP_CF_CONNECTING_IP',  // CloudFlare
            'HTTP_X_FORWARDED_FOR',   // Proxy
            'HTTP_X_REAL_IP',         // Nginx
            'REMOTE_ADDR'             // Fallback
        ];
        
        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                // If multiple IPs, take first one
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                // Validate IP
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        return '0.0.0.0';
    }
    
    /**
     * Anonymize IP Address (for privacy/GDPR)
     */
    public static function anonymizeIP($ip) {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            // IPv4: Keep first 2 octets, zero out last 2
            $parts = explode('.', $ip);
            return $parts[0] . '.' . $parts[1] . '.0.0';
        } elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            // IPv6: Keep first 4 groups
            $parts = explode(':', $ip);
            return implode(':', array_slice($parts, 0, 4)) . '::';
        }
        return '0.0.0.0';
    }
    
    /**
     * Calculate Spam Score
     */
    public static function calculateSpamScore($text, $email = '', $name = '') {
        $score = 0.0;
        $text_lower = strtolower($text);
        
        // Check for spam keywords
        foreach (SPAM_KEYWORDS as $keyword) {
            if (stripos($text_lower, strtolower($keyword)) !== false) {
                $score += 0.3;
            }
        }
        
        // Check for excessive links
        $link_count = preg_match_all('/(http|https|www\.)/i', $text);
        if ($link_count > MAX_LINKS_ALLOWED) {
            $score += 0.2 * ($link_count - MAX_LINKS_ALLOWED);
        }
        
        // Check for excessive capitals
        $capitals = preg_match_all('/[A-Z]/', $text);
        $total_chars = strlen(preg_replace('/[^A-Za-z]/', '', $text));
        if ($total_chars > 0 && ($capitals / $total_chars) > 0.5) {
            $score += 0.2;
        }
        
        // Check for suspicious patterns
        if (preg_match('/(.)\1{4,}/', $text)) { // Repeated characters
            $score += 0.15;
        }
        
        // Email and name similarity (possible fake)
        if (!empty($email) && !empty($name)) {
            $email_name = strstr($email, '@', true);
            if (similar_text(strtolower($name), strtolower($email_name)) > 5) {
                // This is actually normal, don't penalize
            }
        }
        
        // Very short messages with links
        if ($total_chars < 20 && $link_count > 0) {
            $score += 0.25;
        }
        
        return min($score, 1.0); // Cap at 1.0
    }
    
    /**
     * Check Rate Limit
     */
    public static function checkRateLimit($db, $type = 'comment') {
        $ip = self::getClientIP();
        
        try {
            $stmt = $db->prepare("
                SELECT comment_attempts, login_attempts, locked_until 
                FROM rate_limits 
                WHERE ip_address = ?
            ");
            $stmt->execute([$ip]);
            $limit = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Check if locked
            if ($limit && $limit['locked_until']) {
                if (strtotime($limit['locked_until']) > time()) {
                    return [
                        'allowed' => false,
                        'message' => 'Te veel pogingen. Probeer later opnieuw.',
                        'retry_after' => strtotime($limit['locked_until']) - time()
                    ];
                }
            }
            
            $max_attempts = $type === 'comment' ? RATE_LIMIT_COMMENTS : RATE_LIMIT_LOGIN;
            $period = $type === 'comment' ? RATE_LIMIT_PERIOD : RATE_LIMIT_LOGIN_PERIOD;
            
            if (!$limit) {
                // First attempt, create record
                $stmt = $db->prepare("
                    INSERT INTO rate_limits (ip_address, comment_attempts, login_attempts) 
                    VALUES (?, ?, ?)
                ");
                $comment_val = $type === 'comment' ? 1 : 0;
                $login_val = $type === 'login' ? 1 : 0;
                $stmt->execute([$ip, $comment_val, $login_val]);
                
                return ['allowed' => true];
            }
            
            // Check if within period
            $last_attempt = strtotime($limit['last_attempt'] ?? 'now');
            $time_passed = time() - $last_attempt;
            
            if ($time_passed > $period) {
                // Reset counter
                $field = $type === 'comment' ? 'comment_attempts' : 'login_attempts';
                $stmt = $db->prepare("
                    UPDATE rate_limits 
                    SET {$field} = 1, last_attempt = NOW(), locked_until = NULL
                    WHERE ip_address = ?
                ");
                $stmt->execute([$ip]);
                
                return ['allowed' => true];
            }
            
            // Check attempts
            $attempts = $type === 'comment' ? $limit['comment_attempts'] : $limit['login_attempts'];
            
            if ($attempts >= $max_attempts) {
                // Lock the IP
                $stmt = $db->prepare("
                    UPDATE rate_limits 
                    SET locked_until = DATE_ADD(NOW(), INTERVAL ? SECOND)
                    WHERE ip_address = ?
                ");
                $stmt->execute([LOCKOUT_DURATION, $ip]);
                
                return [
                    'allowed' => false,
                    'message' => 'Te veel pogingen. Je bent tijdelijk geblokkeerd.',
                    'retry_after' => LOCKOUT_DURATION
                ];
            }
            
            // Increment counter
            $field = $type === 'comment' ? 'comment_attempts' : 'login_attempts';
            $stmt = $db->prepare("
                UPDATE rate_limits 
                SET {$field} = {$field} + 1, last_attempt = NOW()
                WHERE ip_address = ?
            ");
            $stmt->execute([$ip]);
            
            return [
                'allowed' => true,
                'remaining' => $max_attempts - $attempts - 1
            ];
            
        } catch (PDOException $e) {
            self::logError('Rate limit check failed: ' . $e->getMessage());
            // Fail open - allow request if rate limiting fails
            return ['allowed' => true];
        }
    }
    
    /**
     * Verify reCAPTCHA
     */
    public static function verifyRecaptcha($token) {
        if (!RECAPTCHA_ENABLED) {
            return true;
        }
        
        if (empty($token)) {
            return false;
        }
        
        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $data = [
            'secret' => RECAPTCHA_SECRET_KEY,
            'response' => $token,
            'remoteip' => self::getClientIP()
        ];
        
        $options = [
            'http' => [
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data)
            ]
        ];
        
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        
        if ($result === false) {
            return false;
        }
        
        $response = json_decode($result);
        
        if (!$response->success) {
            return false;
        }
        
        // Check score (v3)
        if (isset($response->score)) {
            return $response->score >= RECAPTCHA_MIN_SCORE;
        }
        
        return true;
    }
    
    /**
     * Log Error
     */
    public static function logError($message) {
        if (!LOG_ERRORS) {
            return;
        }
        
        $log_dir = dirname(ERROR_LOG_FILE);
        if (!file_exists($log_dir)) {
            @mkdir($log_dir, 0755, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $ip = self::getClientIP();
        $log_message = "[{$timestamp}] IP: {$ip} - {$message}\n";
        
        @error_log($log_message, 3, ERROR_LOG_FILE);
    }
    
    /**
     * Send JSON Response
     */
    public static function jsonResponse($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
