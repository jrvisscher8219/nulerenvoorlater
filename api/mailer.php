<?php
/**
 * Mailer Wrapper
 * ---------------
 * Uses PHPMailer with SMTP when available/configured, falls back to mail().
 * No secrets are stored here; configure via environment variables or constants in config.php.
 */

// Try to load configuration if not already
if (!defined('SITE_NAME')) {
    $configPath = __DIR__ . '/config.php';
    if (file_exists($configPath)) {
        require_once $configPath;
    }
}

class Mailer {
    /**
     * Send an email using SMTP (PHPMailer) if available, otherwise fallback to mail().
     *
     * @param string $to
     * @param string $subject
     * @param string $body
     * @param string|null $replyToEmail
     * @param string|null $replyToName
     * @return bool
     */
    public static function send($to, $subject, $body, $replyToEmail = null, $replyToName = null) {
        $useSmtp = self::smtpEnabled();

        if ($useSmtp && self::hasPHPMailer()) {
            try {
                $mailer = self::buildPHPMailer();
                $mailer->setFrom(self::fromEmail(), self::fromName());
                $mailer->addAddress($to);
                if ($replyToEmail) {
                    $mailer->addReplyTo($replyToEmail, $replyToName ?: $replyToEmail);
                }
                $mailer->Subject = $subject;
                $mailer->Body    = $body;
                $mailer->AltBody = $body;
                return $mailer->send();
            } catch (\Throwable $e) {
                // Fallback to mail() if SMTP fails
            }
        }

        // Fallback: native mail()
        $headers = [];
        $headers[] = 'From: ' . self::fromName() . ' <' . self::fromEmail() . '>';
        if ($replyToEmail) {
            $headers[] = 'Reply-To: ' . $replyToEmail;
        }
        $headers[] = 'Content-Type: text/plain; charset=UTF-8';
        return @mail($to, $subject, $body, implode("\r\n", $headers));
    }

    private static function smtpEnabled() {
        // Prefer constant if defined, else environment variable
        if (defined('SMTP_ENABLED')) return (bool)SMTP_ENABLED;
        $env = getenv('SMTP_ENABLED');
        return $env ? filter_var($env, FILTER_VALIDATE_BOOLEAN) : false;
    }

    private static function fromEmail() {
        if (defined('EMAIL_FROM') && EMAIL_FROM) return EMAIL_FROM;
        return 'no-reply@' . (parse_url(defined('SITE_URL') ? SITE_URL : '', PHP_URL_HOST) ?: 'localhost');
    }

    private static function fromName() {
        return defined('SITE_NAME') ? SITE_NAME : 'Website';
    }

    private static function hasPHPMailer() {
        // Try Composer autoload first
        $autoloader = __DIR__ . '/vendor/autoload.php';
        if (file_exists($autoloader)) {
            require_once $autoloader;
            return class_exists('PHPMailer\\PHPMailer\\PHPMailer');
        }
        // Try manual include of PHPMailer sources (if uploaded manually)
        $base = __DIR__ . '/vendor/PHPMailer/src';
        if (is_dir($base)) {
            require_once $base . '/PHPMailer.php';
            require_once $base . '/SMTP.php';
            require_once $base . '/Exception.php';
            return class_exists('PHPMailer\\PHPMailer\\PHPMailer');
        }
        return false;
    }

    private static function buildPHPMailer() {
        $mailer = new PHPMailer\PHPMailer\PHPMailer(true);
        $mailer->isSMTP();
        $mailer->Host       = self::env('SMTP_HOST', 'localhost');
        $mailer->Port       = (int) self::env('SMTP_PORT', 587);
        $mailer->SMTPAuth   = (bool) self::env('SMTP_AUTH', true);
        $mailer->Username   = self::env('SMTP_USERNAME', '');
        $mailer->Password   = self::env('SMTP_PASSWORD', '');
        $mailer->SMTPSecure = self::env('SMTP_SECURE', 'tls'); // tls or ssl
        $mailer->CharSet    = 'UTF-8';
        $mailer->isHTML(false);
        return $mailer;
    }

    private static function env($key, $default = null) {
        // Prefer constant if defined
        if (defined($key)) return constant($key);
        $val = getenv($key);
        return $val !== false ? $val : $default;
    }
}
