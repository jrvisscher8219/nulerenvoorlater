<?php
/**
 * Get CSRF Token
 * ===============
 * Returns a CSRF token for forms
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/security.php';

Security::startSecureSession();

if (isset($_GET['action']) && $_GET['action'] === 'get_csrf') {
    $token = Security::generateCSRFToken();
    Security::jsonResponse(['token' => $token]);
}

Security::jsonResponse(['error' => 'Invalid request'], 400);
