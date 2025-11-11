-- ============================================================================
-- Nu leren voor later - Comment System Database Setup
-- ============================================================================
-- Datum: 11 november 2025
-- Beschrijving: Database schema voor gemodereerd reactiesysteem
-- ============================================================================

-- Gebruik UTF8MB4 voor emoji support en internationale karakters
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- ============================================================================
-- Tabel: comments
-- Beschrijving: Opslag van alle blog reacties met moderatie status
-- ============================================================================
CREATE TABLE IF NOT EXISTS comments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    blog_id VARCHAR(100) NOT NULL COMMENT 'Identifier van de blog (bijv: digitaal-geletterd)',
    author_name VARCHAR(100) NOT NULL COMMENT 'Naam van de reactie schrijver',
    author_email VARCHAR(255) NOT NULL COMMENT 'E-mailadres (niet publiek zichtbaar)',
    comment_text TEXT NOT NULL COMMENT 'De reactie zelf',
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending' COMMENT 'Moderatie status',
    ip_address VARCHAR(45) DEFAULT NULL COMMENT 'IP adres voor spam tracking (IPv4/IPv6)',
    user_agent VARCHAR(255) DEFAULT NULL COMMENT 'Browser info voor spam detectie',
    spam_score FLOAT DEFAULT 0 COMMENT 'Spam score (0-1, hoger = meer spam)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Wanneer reactie is geplaatst',
    approved_at TIMESTAMP NULL DEFAULT NULL COMMENT 'Wanneer goedgekeurd',
    approved_by VARCHAR(50) NULL DEFAULT NULL COMMENT 'Welke admin heeft goedgekeurd',
    
    -- Indices voor snelle queries
    INDEX idx_blog_status (blog_id, status, created_at),
    INDEX idx_created (created_at),
    INDEX idx_status (status),
    INDEX idx_email (author_email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Blog reacties met moderatie';

-- ============================================================================
-- Tabel: admin_users
-- Beschrijving: Admin accounts voor moderatie toegang
-- ============================================================================
CREATE TABLE IF NOT EXISTS admin_users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE COMMENT 'Login gebruikersnaam',
    password_hash VARCHAR(255) NOT NULL COMMENT 'Bcrypt/Argon2 gehashed wachtwoord',
    email VARCHAR(255) NOT NULL COMMENT 'E-mail voor notificaties',
    is_active TINYINT(1) DEFAULT 1 COMMENT 'Account actief (1) of geblokkeerd (0)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Account aanmaak datum',
    last_login TIMESTAMP NULL DEFAULT NULL COMMENT 'Laatste login tijdstip',
    
    INDEX idx_username (username),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Admin gebruikers voor moderatie';

-- ============================================================================
-- Tabel: rate_limits
-- Beschrijving: Rate limiting voor spam preventie
-- ============================================================================
CREATE TABLE IF NOT EXISTS rate_limits (
    ip_address VARCHAR(45) PRIMARY KEY COMMENT 'IP adres (IPv4/IPv6)',
    comment_attempts INT DEFAULT 1 COMMENT 'Aantal reactie pogingen',
    login_attempts INT DEFAULT 0 COMMENT 'Aantal login pogingen (admin)',
    last_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Laatste poging',
    locked_until TIMESTAMP NULL DEFAULT NULL COMMENT 'Geblokkeerd tot (NULL = niet geblokkeerd)',
    
    INDEX idx_locked (locked_until)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Rate limiting en IP blokkering';

-- ============================================================================
-- Tabel: comment_reports (Optioneel - voor toekomstig gebruik)
-- Beschrijving: Bezoekers kunnen reacties rapporteren als ongepast
-- ============================================================================
CREATE TABLE IF NOT EXISTS comment_reports (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    comment_id INT UNSIGNED NOT NULL COMMENT 'Welke reactie wordt gerapporteerd',
    reason ENUM('spam', 'inappropriate', 'offensive', 'other') NOT NULL COMMENT 'Reden van rapportage',
    description TEXT DEFAULT NULL COMMENT 'Optionele uitleg',
    reporter_ip VARCHAR(45) DEFAULT NULL COMMENT 'IP van rapporteerder',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Wanneer gerapporteerd',
    resolved TINYINT(1) DEFAULT 0 COMMENT 'Is behandeld door admin',
    
    FOREIGN KEY (comment_id) REFERENCES comments(id) ON DELETE CASCADE,
    INDEX idx_comment (comment_id),
    INDEX idx_resolved (resolved)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Rapportages van reacties';

-- ============================================================================
-- Standaard Admin Account Aanmaken
-- ============================================================================
-- BELANGRIJK: Pas dit aan met je eigen gegevens!
-- Standaard wachtwoord: "ChangeMe123!" (MOET je direct wijzigen!)
-- 
-- Om een nieuw wachtwoord hash te maken, run in PHP:
-- echo password_hash('JouwWachtwoord', PASSWORD_ARGON2ID);
-- ============================================================================

-- Voorbeeld admin (VERWIJDER of WIJZIG deze regel na installatie!)
INSERT INTO admin_users (username, password_hash, email) VALUES
('admin', '$argon2id$v=19$m=65536,t=4,p=1$SExaMPleHashDontUseThis$HashWillBeGeneratedLater', 'info@nulerenvoorlater.nl')
ON DUPLICATE KEY UPDATE username=username;

-- ============================================================================
-- Triggers voor automatische data cleanup (Privacy - AVG)
-- ============================================================================

-- Trigger: Anonimiseer IP adressen ouder dan 30 dagen
DELIMITER $$
CREATE EVENT IF NOT EXISTS anonymize_old_ips
ON SCHEDULE EVERY 1 DAY
DO
BEGIN
    UPDATE comments 
    SET ip_address = CONCAT(SUBSTRING_INDEX(ip_address, '.', 2), '.0.0')
    WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)
    AND ip_address IS NOT NULL
    AND ip_address NOT LIKE '%.0.0';
    
    -- Clean oude rate limit entries (ouder dan 7 dagen)
    DELETE FROM rate_limits 
    WHERE last_attempt < DATE_SUB(NOW(), INTERVAL 7 DAY);
END$$
DELIMITER ;

-- ============================================================================
-- Handige Views voor Admin Dashboard
-- ============================================================================

-- View: Recente pending reacties
CREATE OR REPLACE VIEW pending_comments AS
SELECT 
    c.id,
    c.blog_id,
    c.author_name,
    c.author_email,
    LEFT(c.comment_text, 100) AS comment_preview,
    c.created_at,
    c.spam_score,
    TIMESTAMPDIFF(HOUR, c.created_at, NOW()) AS hours_waiting
FROM comments c
WHERE c.status = 'pending'
ORDER BY c.created_at DESC;

-- View: Statistieken per blog
CREATE OR REPLACE VIEW blog_comment_stats AS
SELECT 
    blog_id,
    COUNT(*) AS total_comments,
    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) AS approved_count,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_count,
    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) AS rejected_count,
    MAX(created_at) AS last_comment_date
FROM comments
GROUP BY blog_id;

-- ============================================================================
-- Test Data (Optioneel - alleen voor development/testing)
-- ============================================================================
-- Uncomment onderstaande regels om test reacties toe te voegen

/*
INSERT INTO comments (blog_id, author_name, author_email, comment_text, status, created_at) VALUES
('digitaal-geletterd', 'Jan de Vries', 'jan@example.com', 'Geweldig artikel! Dit helpt me echt om digitale geletterdheid te integreren in mijn lessen.', 'approved', NOW() - INTERVAL 2 DAY),
('digitaal-geletterd', 'Sara Bakker', 'sara@example.com', 'Dank voor de praktische tips. De vier pilaren zijn nu veel duidelijker!', 'approved', NOW() - INTERVAL 1 DAY),
('digitaal-geletterd', 'Test Pending', 'test@example.com', 'Dit is een test reactie die nog op goedkeuring wacht.', 'pending', NOW() - INTERVAL 3 HOUR),
('digitaal-geletterd', 'Spam Bot', 'spam@spam.com', 'Click here for amazing deals!!!', 'rejected', NOW() - INTERVAL 5 HOUR);
*/

-- ============================================================================
-- Verificatie Queries
-- ============================================================================
-- Run deze queries na installatie om te controleren of alles werkt:

-- Toon alle tabellen
-- SHOW TABLES;

-- Toon structuur van comments tabel
-- DESCRIBE comments;

-- Tel aantal reacties per status
-- SELECT status, COUNT(*) FROM comments GROUP BY status;

-- Toon admin accounts
-- SELECT id, username, email, created_at FROM admin_users;

-- ============================================================================
-- KLAAR!
-- ============================================================================
-- De database is nu klaar voor gebruik.
-- Volgende stappen:
-- 1. Pas het admin wachtwoord aan in admin_users tabel
-- 2. Configureer api/config.php met deze database gegevens
-- 3. Test de verbinding via api/db.php
-- ============================================================================
