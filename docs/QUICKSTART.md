# 🚀 Quick Start Guide - Reactiesysteem

## ⚡ In 5 Minuten Live

### 1️⃣ Database Setup (2 min)
```bash
# Login bij Strato > MySQL > Importeer sql/setup.sql
```

### 2️⃣ Config Aanmaken (1 min)
```bash
cp api/config.example.php api/config.php
nano api/config.php  # Vul database gegevens in
```

**Minimaal vereist:**
- DB_HOST, DB_NAME, DB_USER, DB_PASS
- ADMIN_USERNAME, ADMIN_PASSWORD (kies zelf!)

### 3️⃣ Admin Account (30 sec)
Ga naar: `/api/admin/login.php`

Als dit werkt, login met je ADMIN_USERNAME/PASSWORD uit config.php

### 4️⃣ Test! (1 min)
1. Ga naar `/blogs/digitaal-geletterd.html`
2. Scroll naar beneden
3. Plaats test reactie
4. Login admin panel
5. Keur goed
6. Refresh blog → reactie zichtbaar!

## ✅ Klaar!

**Problemen?** Zie `docs/COMMENTS_INSTALL.md` voor uitgebreide handleiding.

---

## 📁 Bestanden Overzicht

```
/sql/setup.sql                  → Database schema
/api/config.example.php         → Config template
/api/config.php                 → Jouw config (maak aan!)
/api/db.php                     → Database connectie
/api/security.php               → Security helpers
/api/submit-comment.php         → Nieuwe reactie
/api/get-comments.php           → Reacties ophalen
/api/admin/login.php            → Admin login
/api/admin/dashboard.php        → Moderatie panel
/css/comments.css               → Styling
/js/comments.js                 → Frontend logic
/blogs/digitaal-geletterd.html  → Voorbeeld integratie
```

---

## 🔑 Belangrijkste URLs

- **Admin login:** `/api/admin/login.php`
- **Dashboard:** `/api/admin/dashboard.php`
- **Blog met reacties:** `/blogs/digitaal-geletterd.html`

---

## 🛡️ Security Checklist

- [ ] `api/config.php` heeft permissions 600
- [ ] config.php staat in .gitignore
- [ ] Sterk admin wachtwoord (12+ chars)
- [ ] Test XSS/SQL injection werkt NIET

---

## 💡 Tips

**Auto-approve vertrouwde mensen:**
```php
define('TRUSTED_EMAILS', [
    'collega@school.nl',
    'vriend@email.nl'
]);
```

**Striktere spam filter:**
```php
define('RATE_LIMIT_COMMENTS', 2);  // Max 2 reacties per 10 min
define('MAX_LINKS_ALLOWED', 0);    // Geen links toestaan
```

**E-mail uit:**
```php
define('EMAIL_NOTIFICATIONS', false);
```

---

## 📧 Support

**Foutmeldingen?** Check `/logs/errors.log`

**Vragen?** Zie volledige docs in `docs/COMMENTS_INSTALL.md`

---

**Veel succes! 🎉**
