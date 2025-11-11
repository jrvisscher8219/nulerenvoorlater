<?php
// gegenereerd tijdens deployment
define('DB_HOST', '__DB_HOST__');
define('DB_NAME', '__DB_NAME__');
define('DB_USER', '__DB_USER__');
define('DB_PASS', '__DB_PASS__');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATION', 'utf8mb4_unicode_ci');

define('ADMIN_USERNAME', '__ADMIN_USERNAME__');
define('ADMIN_PASSWORD', '__ADMIN_PASSWORD__');
define('ADMIN_EMAIL', '__ADMIN_EMAIL__');

define('SITE_URL', '__SITE_URL__');
define('SITE_NAME', 'Nu leren voor later');

define('SESSION_NAME', 'nlvl_admin_session');
define('SESSION_LIFETIME', 3600);
define('CSRF_TOKEN_NAME', 'csrf_token');
define('CSRF_TOKEN_LIFETIME', 7200);
define('RATE_LIMIT_COMMENTS', 3);
define('RATE_LIMIT_PERIOD', 600);
define('RATE_LIMIT_LOGIN', 5);
define('RATE_LIMIT_LOGIN_PERIOD', 900);
define('LOCKOUT_DURATION', 3600);
define('COMMENT_MIN_LENGTH', 10);
define('COMMENT_MAX_LENGTH', 1000);
define('COMMENT_REQUIRE_MODERATION', true);

define('TRUSTED_EMAILS', []);

define('EMAIL_NOTIFICATIONS', true);
define('NOTIFICATION_EMAIL', 'info@nulerenvoorlater.nl');
define('EMAIL_FROM', 'no-reply@nulerenvoorlater.nl');

define('RECAPTCHA_ENABLED', false);
define('RECAPTCHA_SITE_KEY', '');
define('RECAPTCHA_SECRET_KEY', '');

define('LOG_IP_ADDRESSES', true);
define('IP_ANONYMIZE_DAYS', 30);

define('LOG_USER_AGENTS', true);

define('HONEYPOT_FIELD', 'website_url');

define('SPAM_KEYWORDS', ['viagra','cialis','casino','poker','lottery','click here','buy now','limited offer','act now','pills','pharmacy','discount','free money']);

define('MAX_LINKS_ALLOWED', 1);

define('DEBUG_MODE', false);

define('LOG_ERRORS', true);
define('ERROR_LOG_FILE', __DIR__ . '/../logs/errors.log');

date_default_timezone_set('Europe/Amsterdam');

define('IS_HTTPS', (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https'));

define('BASE_PATH', dirname(__DIR__));
define('API_PATH', __DIR__);
