<?php
/**
 * Database Configuration - EXAMPLE FILE
 * ======================================
 * 
 * Dit is een voorbeeld configuratie bestand.
 * 
 * INSTRUCTIES:
 * 1. Kopieer dit bestand naar: api/config.php
 * 2. Vul je echte database gegevens in
 * 3. Voeg config.php toe aan .gitignore (BELANGRIJK!)
 * 
 * Beveiliging:
 * - Bewaar config.php NOOIT in Git/version control
 * - Zet file permissions op 600 (chmod 600 config.php)
 * - Gebruik sterke, unieke wachtwoorden
 */

// ============================================================================
// DATABASE CONFIGURATIE
// ============================================================================
// Vraag deze gegevens op via je Strato hosting panel
// LET OP: dit is een VOORBEELD-bestand. Vul hier GEEN echte wachtwoorden in.
// Mogelijke DB_HOST waarden bij Strato:
//   - rdbms.strato.de
//   - database-XXXXXXXXXX.webspace-host.com (zoals: database-5018989034.webspace-host.com)
// Gebruik precies wat Strato toont in je database overzicht.
define('DB_HOST', 'database-5018989034.webspace-host.com');              // Pas aan naar jouw echte host
define('DB_NAME', 'dbs14955096');           // Vervang met echte databasenaam (bijv: dbs14955096)
define('DB_USER', 'dbu5433971');      // Vervang met echte gebruikersnaam (bijv: dbu5433971)
define('DB_PASS', 'HIER_JOUW_STERKE_WACHTWOORD');     // Vul in config.php het echte wachtwoord in (NOOIT hier)
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATION', 'utf8mb4_unicode_ci');

// ============================================================================
// ADMIN CONFIGURATIE
// ============================================================================
// Standaard admin account (wordt bij eerste run aangemaakt)
define('ADMIN_USERNAME', 'ramon');                      // Pas aan naar jouw voorkeur
define('ADMIN_PASSWORD', 'SterkAdminWachtwoord!123');    // WIJZIG DIT! (min 12 karakters)
define('ADMIN_EMAIL', 'info@nulerenvoorlater.nl');     // Voor notificaties

// ============================================================================
// SITE CONFIGURATIE
// ============================================================================
define('SITE_URL', 'https://www.nulerenvoorlater.nl'); // Jouw site URL (zonder trailing slash)
define('SITE_NAME', 'Nu leren voor later');

// ============================================================================
// SECURITY INSTELLINGEN
// ============================================================================
// Session configuratie
define('SESSION_NAME', 'nlvl_admin_session');
define('SESSION_LIFETIME', 3600);  // 1 uur (in seconden)

// CSRF Token
define('CSRF_TOKEN_NAME', 'csrf_token');
define('CSRF_TOKEN_LIFETIME', 7200); // 2 uur

// Rate Limiting
define('RATE_LIMIT_COMMENTS', 3);        // Max reacties per tijdsperiode
define('RATE_LIMIT_PERIOD', 600);        // Tijdsperiode in seconden (600 = 10 min)
define('RATE_LIMIT_LOGIN', 5);           // Max login pogingen
define('RATE_LIMIT_LOGIN_PERIOD', 900);  // 15 minuten

// IP Lockout
define('LOCKOUT_DURATION', 3600); // 1 uur geblokkeerd na te veel pogingen

// ============================================================================
// COMMENT INSTELLINGEN
// ============================================================================
define('COMMENT_MIN_LENGTH', 10);      // Minimale lengte reactie
define('COMMENT_MAX_LENGTH', 1000);    // Maximale lengte reactie
define('COMMENT_REQUIRE_MODERATION', true); // Altijd moderatie vereist?
define('COMMENT_ALLOW_HTML', false);   // HTML toestaan in reacties?

// Trusted e-mails (optioneel - auto-approve voor deze adressen)
define('TRUSTED_EMAILS', [
    // 'trusted@example.com',
    // 'colleague@school.nl'
]);

// ============================================================================
// E-MAIL NOTIFICATIES
// ============================================================================
define('EMAIL_NOTIFICATIONS', true);           // E-mail bij nieuwe reactie?
define('NOTIFICATION_EMAIL', 'info@nulerenvoorlater.nl'); // Waar naartoe?
define('EMAIL_FROM', 'no-reply@nulerenvoorlater.nl');     // Van adres

// ============================================================================
// reCAPTCHA CONFIGURATIE (Optioneel)
// ============================================================================
// Aanmaken op: https://www.google.com/recaptcha/admin
// Kies: reCAPTCHA v3
define('RECAPTCHA_ENABLED', false);           // Zet op true als je reCAPTCHA wilt
define('RECAPTCHA_SITE_KEY', '');             // Je site key
define('RECAPTCHA_SECRET_KEY', '');           // Je secret key
define('RECAPTCHA_MIN_SCORE', 0.5);           // Minimum score (0.0-1.0)

// ============================================================================
// PRIVACY & AVG INSTELLINGEN
// ============================================================================
define('LOG_IP_ADDRESSES', true);      // IP adressen opslaan? (spam preventie)
define('IP_ANONYMIZE_DAYS', 30);       // Na hoeveel dagen IP anonimiseren?
define('LOG_USER_AGENTS', true);       // Browser info opslaan?

// ============================================================================
// SPAM DETECTIE
// ============================================================================
// Honeypot field name (moet verborgen zijn voor mensen, zichtbaar voor bots)
define('HONEYPOT_FIELD', 'website_url');

// Spam keywords (reacties met deze woorden krijgen hogere spam score)
define('SPAM_KEYWORDS', [
    'viagra', 'cialis', 'casino', 'poker', 'lottery',
    'click here', 'buy now', 'limited offer', 'act now',
    'pills', 'pharmacy', 'discount', 'free money'
]);

// Max aantal links in reactie
define('MAX_LINKS_ALLOWED', 1);

// ============================================================================
// DEBUGGING
// ============================================================================
// Zet op true alleen tijdens development!
define('DEBUG_MODE', false);

// Error logging
define('LOG_ERRORS', true);
define('ERROR_LOG_FILE', __DIR__ . '/../logs/errors.log');

// ============================================================================
// TIMEZONE
// ============================================================================
date_default_timezone_set('Europe/Amsterdam');

// ============================================================================
// AUTO-CONFIGURATIE
// ============================================================================
// Bepaal of we HTTPS gebruiken
define('IS_HTTPS', (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
    (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ||
    (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
));

// Base paths
define('BASE_PATH', dirname(__DIR__));
define('API_PATH', __DIR__);

// ============================================================================
// VALIDATIE
// ============================================================================
// Check of alle vereiste constanten zijn ingesteld
if (
    DB_HOST === 'localhost' && 
    DB_NAME === 'jouw_database_naam' &&
    ADMIN_PASSWORD === 'ChangeMe123!SecurePass'
) {
    die('❌ ERROR: Configuratie is nog niet ingevuld! Bewerk api/config.php met je eigen gegevens.');
}

// ============================================================================
// EINDE CONFIGURATIE
// ============================================================================
