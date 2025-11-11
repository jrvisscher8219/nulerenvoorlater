# 📖 Comment System - Installatie Handleiding

## 🎯 Overzicht

Je hebt nu een volledig reactiesysteem met:
- ✅ Gemodereerde reacties (goedkeuring vereist)
- ✅ Admin panel voor moderatie
- ✅ Spam bescherming (honeypot + rate limiting + spam score)
- ✅ AVG-compliant (IP anonimisering na 30 dagen)
- ✅ Veilig (SQL injection, XSS, CSRF bescherming)
- ✅ reCAPTCHA v3 ready (optioneel)

---

## 📋 Installatie Stappen

### **Stap 1: Database Aanmaken**

1. **Login bij je Strato hosting panel**
2. **Ga naar MySQL Databases**
3. **Maak een nieuwe database aan** (of gebruik bestaande)
   - Noteer: Database naam, gebruikersnaam, wachtwoord, host

4. **Importeer het database schema:**
   - Via phpMyAdmin of command line
   - Upload bestand: `/sql/setup.sql`
   - Of kopieer de inhoud en run in SQL query venster

```bash
# Via command line (als je SSH toegang hebt):
mysql -u jouw_db_user -p jouw_db_naam < sql/setup.sql
```

---

### **Stap 2: Configuratie Aanmaken**

1. **Kopieer het voorbeeld config bestand:**
```bash
cp api/config.example.php api/config.php
```

2. **Open `api/config.php` en vul in:**
```php
// Database gegevens (van Stap 1)
define('DB_HOST', 'localhost');  // Of rdbms.strato.de
define('DB_NAME', 'jouw_database_naam');
define('DB_USER', 'jouw_database_user');
define('DB_PASS', 'jouw_database_wachtwoord');

// Admin account (dit wordt je eerste login)
define('ADMIN_USERNAME', 'admin');  // Kies je eigen naam
define('ADMIN_PASSWORD', 'JouwVeiligWachtwoord123!'); // MIN 12 tekens!
define('ADMIN_EMAIL', 'info@nulerenvoorlater.nl');
```

3. **Beveilig config.php:**
```bash
chmod 600 api/config.php
```

4. **Voeg toe aan .gitignore:**
```bash
echo "api/config.php" >> .gitignore
```

---

### **Stap 3: Admin Account Aanmaken**

Er zijn twee opties:

#### **Optie A: Automatisch (via PHP script)**

Maak een tijdelijk bestand: `setup-admin.php` in je root:

```php
<?php
require_once 'api/config.php';
require_once 'api/db.php';
require_once 'api/security.php';

$result = Database::setupAdminAccount();
echo $result['message'];

// VERWIJDER DIT BESTAND DIRECT NA GEBRUIK!
?>
```

Ga naar: `https://www.nulerenvoorlater.nl/setup-admin.php`  
**Verwijder het bestand daarna!**

#### **Optie B: Handmatig (via database)**

Run deze SQL query (vervang met jouw gegevens):

```sql
INSERT INTO admin_users (username, password_hash, email) 
VALUES (
    'admin',
    '$argon2id$v=19$m=65536,t=4,p=1$[jouw_gegenereerde_hash]',
    'info@nulerenvoorlater.nl'
);
```

Om een wachtwoord hash te maken, run in PHP:
```php
<?php
echo password_hash('JouwWachtwoord', PASSWORD_ARGON2ID);
?>
```

---

### **Stap 4: Test de Installatie**

1. **Test database connectie:**
   - Ga naar: `/api/admin/login.php`
   - Zie je een login scherm? ✅ Database werkt!

2. **Test admin login:**
   - Login met je ADMIN_USERNAME en ADMIN_PASSWORD
   - Kom je op het dashboard? ✅ Auth werkt!

3. **Test reactie plaatsen:**
   - Ga naar: `/blogs/digitaal-geletterd.html`
   - Scroll naar reactie sectie
   - Plaats een test reactie
   - Zie je "Bedankt" bericht? ✅ Formulier werkt!

4. **Test moderatie:**
   - Login op admin panel
   - Zie je de test reactie bij "Te beoordelen"?
   - Klik "Goedkeuren"
   - Refresh de blog pagina
   - Zie je de reactie? ✅ Alles werkt!

---

### **Stap 5: reCAPTCHA Toevoegen (Optioneel)**

1. **Ga naar:** https://www.google.com/recaptcha/admin
2. **Klik "+"** voor nieuw e site
3. **Kies:** reCAPTCHA v3
4. **Voeg domain toe:** `nulerenvoorlater.nl`
5. **Kopieer de keys**

6. **Update `api/config.php`:**
```php
define('RECAPTCHA_ENABLED', true);
define('RECAPTCHA_SITE_KEY', 'jouw_site_key_hier');
define('RECAPTCHA_SECRET_KEY', 'jouw_secret_key_hier');
```

7. **Voeg script toe aan blog pagina** (in `<head>`):
```html
<script src="https://www.google.com/recaptcha/api.js?render=jouw_site_key_hier"></script>
```

8. **Update `comments.js`** om reCAPTCHA token toe te voegen bij submit.

---

## 🔒 Beveiliging Checklist

### **Direct na installatie:**

- [ ] `api/config.php` heeft permissions 600
- [ ] `api/config.php` staat in `.gitignore`
- [ ] Admin wachtwoord is sterk (min 12 tekens, mix)
- [ ] Database gebruiker heeft ALLEEN toegang tot comment database
- [ ] `setup-admin.php` is verwijderd (als gebruikt)
- [ ] Test dat SQL injection NIET werkt (probeer `'; DROP TABLE--` in naam)
- [ ] Test dat XSS NIET werkt (probeer `<script>alert('XSS')</script>` in reactie)

### **Regelmatig:**

- [ ] Check `/logs/errors.log` voor verdachte activiteit
- [ ] Update admin wachtwoord elke 3 maanden
- [ ] Backup database wekelijks
- [ ] Check rate_limits tabel voor geblokkeerde IPs

---

## 🎨 Blog Integratie

Om reacties toe te voegen aan **andere blogs**, kopieer deze sectie:

```html
<!-- AAN HET EINDE VAN JE BLOG CONTENT -->

<!-- Comments Section -->
<section class="comments-section">
  <div class="comments-header">
    <h2>💬 Reacties</h2>
    <p class="comments-count" id="comments-count">Laden...</p>
  </div>

  <!-- Comment Form -->
  <form id="comment-form" class="comment-form" data-blog-id="BLOG_ID_HIER">
    <!-- ... rest van formulier ... -->
  </form>

  <!-- Comments List -->
  <div id="comments-list" class="comments-list">
    <div class="loading">Reacties laden</div>
  </div>
</section>
```

**Vervang:** `data-blog-id="BLOG_ID_HIER"` met unieke ID (bijv: `ai-tools-2025`)

**Voeg toe in `<head>`:**
```html
<link rel="stylesheet" href="../css/comments.css">
```

**Voeg toe voor `</body>`:**
```html
<script src="../js/comments.js" defer></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    fetch('../api/get-csrf.php?action=get_csrf')
      .then(r => r.json())
      .then(data => {
        if (data.token) {
          document.getElementById('csrf_token').value = data.token;
        }
      });
  });
</script>
```

---

## 📧 E-mail Notificaties

Standaard krijg je een e-mail bij nieuwe reacties.

**Uitschakelen:**
```php
define('EMAIL_NOTIFICATIONS', false);
```

**Wijzig ontvanger:**
```php
define('NOTIFICATION_EMAIL', 'jouw@email.nl');
```

---

## 🛠️ Onderhoud

### **Database Cleanup**

De database ruimt automatisch op (via MySQL EVENT):
- IP adressen worden geanonimiseerd na 30 dagen
- Rate limit entries worden verwijderd na 7 dagen

**Controleren of events draaien:**
```sql
SHOW EVENTS;
```

**Handmatig IP's anonimiseren:**
```sql
UPDATE comments 
SET ip_address = CONCAT(SUBSTRING_INDEX(ip_address, '.', 2), '.0.0')
WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)
AND ip_address NOT LIKE '%.0.0';
```

### **Backup**

**Database backup (wekelijks):**
```bash
mysqldump -u jouw_user -p jouw_database > backup_$(date +%Y%m%d).sql
```

**Of via Strato panel:** Database > Export

---

## ⚙️ Configuratie Opties

Alle opties in `api/config.php`:

| Optie | Standaard | Beschrijving |
|-------|-----------|--------------|
| `COMMENT_MIN_LENGTH` | 10 | Min tekens reactie |
| `COMMENT_MAX_LENGTH` | 1000 | Max tekens reactie |
| `RATE_LIMIT_COMMENTS` | 3 | Max reacties per periode |
| `RATE_LIMIT_PERIOD` | 600 | Periode in seconden (10 min) |
| `LOCKOUT_DURATION` | 3600 | Blokkeer IP voor 1 uur |
| `IP_ANONYMIZE_DAYS` | 30 | IP anonimiseren na X dagen |
| `EMAIL_NOTIFICATIONS` | true | E-mail bij nieuwe reactie |
| `RECAPTCHA_ENABLED` | false | reCAPTCHA aan/uit |
| `DEBUG_MODE` | false | Debug modus (ALLEEN development!) |

---

## 🐛 Troubleshooting

### **Probleem: "Database connection failed"**
- Check `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` in config.php
- Test database login via phpMyAdmin
- Check of MySQL draait

### **Probleem: "CSRF token invalid"**
- Check of sessions werken: `<?php phpinfo(); ?>` → session.save_path
- Check browser console voor JavaScript errors
- Clear browser cache/cookies

### **Probleem: Reacties verschijnen niet**
- Check admin panel: zijn ze goedgekeurd?
- Check browser console (F12) voor API errors
- Check `blog_id` in formulier komt overeen met database

### **Probleem: "Rate limit" foutmelding**
- Verhoog `RATE_LIMIT_COMMENTS` in config.php
- Of wacht 10 minuten
- Of clear rate_limits tabel: `DELETE FROM rate_limits WHERE ip_address = 'jouw_ip';`

### **Probleem: Admin login werkt niet**
- Check wachtwoord (hoofdlettergevoelig!)
- Check admin_users tabel: `SELECT * FROM admin_users;`
- Check is_active = 1
- Reset wachtwoord via SQL (zie Stap 3)

### **Probleem: E-mails komen niet aan**
- Check spam folder
- Check `EMAIL_FROM` en `NOTIFICATION_EMAIL` in config.php
- Test PHP mail(): `<?php mail('test@test.nl', 'Test', 'Test'); ?>`
- Mogelijk heeft Strato mail() uitgeschakeld (check support)

---

## 📊 Database Queries (Handig)

**Alle pending reacties:**
```sql
SELECT * FROM comments WHERE status = 'pending' ORDER BY created_at DESC;
```

**Statistieken per blog:**
```sql
SELECT blog_id, status, COUNT(*) as count 
FROM comments 
GROUP BY blog_id, status;
```

**Top commenters:**
```sql
SELECT author_name, author_email, COUNT(*) as total
FROM comments
WHERE status = 'approved'
GROUP BY author_email
ORDER BY total DESC
LIMIT 10;
```

**Spam score gemiddelde:**
```sql
SELECT AVG(spam_score) as avg_spam, 
       MAX(spam_score) as max_spam
FROM comments;
```

**Recent goedgekeurd:**
```sql
SELECT * FROM comments 
WHERE status = 'approved' 
ORDER BY approved_at DESC 
LIMIT 20;
```

---

## 🔐 Privacy Update Vereist!

Voeg toe aan je `privacy.html`:

```html
<h3>Reacties op blogs</h3>
<p>Als je een reactie plaatst op onze blogs, verzamelen we:</p>
<ul>
  <li>Je naam (verplicht, wordt publiek getoond)</li>
  <li>Je e-mailadres (verplicht, wordt NIET publiek getoond)</li>
  <li>Je IP-adres (voor spam preventie, wordt na 30 dagen geanonimiseerd)</li>
  <li>Tijdstip van plaatsing</li>
</ul>

<p><strong>Moderatie:</strong> Je reactie wordt pas zichtbaar na goedkeuring door de beheerder.</p>

<p><strong>Je rechten:</strong></p>
<ul>
  <li>Je kunt verwijdering van je reactie verzoeken via info@nulerenvoorlater.nl</li>
  <li>Je gegevens worden niet gedeeld met derden</li>
  <li>We gebruiken Google reCAPTCHA (zie <a href="https://policies.google.com/privacy">Google Privacy Policy</a>)</li>
</ul>
```

---

## 📞 Support

**Hulp nodig?**
- Check de `/logs/errors.log` voor foutmeldingen
- Test met `DEBUG_MODE = true` in config.php (ALLEEN lokaal!)
- Vraag hulp in je Git repo issues

**Versie:** 1.0.0  
**Laatste update:** 11 november 2025  
**Compatibiliteit:** PHP 7.4+, MySQL 5.7+

---

## ✅ Installatie Checklist

Print deze checklist af en vink af:

- [ ] Database aangemaakt en schema geïmporteerd
- [ ] `api/config.php` gemaakt en ingevuld
- [ ] Admin account aangemaakt
- [ ] Admin login getest
- [ ] Test reactie geplaatst
- [ ] Test reactie goedgekeurd
- [ ] Reactie verschijnt op blog pagina
- [ ] reCAPTCHA toegevoegd (optioneel)
- [ ] E-mail notificaties werken
- [ ] Privacy verklaring bijgewerkt
- [ ] `.gitignore` bevat `api/config.php`
- [ ] File permissions ingesteld
- [ ] Backup schema gemaakt
- [ ] Productie test gedaan

**Gefeliciteerd! Je reactiesysteem werkt! 🎉**

---

## 🚀 Volgende Stappen

Na succesvolle installatie kun je:
1. ✅ Reacties toevoegen aan meer blogs
2. ✅ Vertrouwde e-mails toevoegen voor auto-approve
3. ✅ Rate limits aanpassen aan jouw behoeften
4. ✅ Styling customizen in `comments.css`
5. ✅ Statistieken bekijken in admin panel

**Veel plezier met je nieuwe reactiesysteem! 💬**
