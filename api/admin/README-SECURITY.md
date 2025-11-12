# 🔐 Admin Security - BELANGRIJK

## ⚠️ VERWIJDER create_admin.php VAN DE SERVER!

### Waarom?
`create_admin.php` is een **setup script** dat gebruikt wordt om het eerste admin account aan te maken.

**GEVAAR:** Als dit bestand op de live server blijft staan, kan **iedereen**:
- Een eigen admin account aanmaken
- Toegang krijgen tot je moderatie dashboard
- Reacties goedkeuren/verwijderen
- Database gegevens bekijken

---

## 🛡️ Beveiligingsstappen

### 1. ✅ Verwijder van Live Server

**Via SFTP:**
1. Verbind met: `ssh.strato.com`
2. Ga naar: `/Nulerenvoorlater/api/admin/`
3. **VERWIJDER:** `create_admin.php`

**Via SSH:**
```bash
ssh 538263254.swh.strato-hosting.eu@ssh.strato.com
rm /Nulerenvoorlater/api/admin/create_admin.php
```

**Verificatie (via browser):**
- Deze URL moet een 404 geven:
- `https://www.nulerenvoorlater.nl/api/admin/create_admin.php`

---

### 2. ✅ Check Deployment Exclusion

Het bestand is al uitgesloten van automatische deployment:
```yaml
--exclude 'api/admin/create_admin.php'
```

✅ Dit betekent: Toekomstige deployments uploaden dit bestand NIET meer.

---

### 3. ✅ Bewaar Lokaal (Voor Backup)

Het bestand blijft bewaard in je lokale Git repository voor noodgevallen:
- Lokaal: `/home/geheimpje/Bureaublad/nulerenvoorlater1/api/admin/create_admin.php`
- **NIET** op GitHub (staat in .gitignore)
- **NIET** op live server (excluded van deployment)

---

## 📋 Admin Bestanden Overzicht

| Bestand | Doel | Live Server | Git Repo |
|---------|------|-------------|----------|
| `login.php` | Admin login | ✅ JA | ✅ JA |
| `dashboard.php` | Moderatie overzicht | ✅ JA | ✅ JA |
| `moderate.php` | Reacties beheren | ✅ JA | ✅ JA |
| `logout.php` | Uitloggen | ✅ JA | ✅ JA |
| `check_admin.php` | Auth verificatie | ✅ JA | ✅ JA |
| `reset_password.php` | Wachtwoord reset | ✅ JA | ✅ JA |
| **`create_admin.php`** | **Setup (1x gebruik)** | ❌ **VERWIJDER** | ✅ Lokaal only |

---

## 🔑 Admin Login Gegevens

**Login URL:**
```
https://www.nulerenvoorlater.nl/api/admin/login.php
```

**Credentials:**
- Staan in: `api/config.php` (server only)
- Username: `ADMIN_USERNAME`
- Password: `ADMIN_PASSWORD`

**Standaard:**
- Username: `ramon`
- Password: Zoals ingesteld in config.php

---

## 🆘 Als je Rate Limit Problemen Hebt

### Symptoom: "Te veel pogingen"

**Oplossing 1: Wachten**
- Wacht 1 uur (LOCKOUT_DURATION)
- Probeer daarna opnieuw

**Oplossing 2: Database Reset**
Via phpMyAdmin:
```sql
DELETE FROM rate_limits;
```

**Oplossing 3: Rate Limit Verhogen**
In `api/config.php`:
```php
define('RATE_LIMIT_LOGIN', 10);           // Verhoog naar 10
define('RATE_LIMIT_LOGIN_PERIOD', 900);   // 15 minuten
define('LOCKOUT_DURATION', 1800);         // Verlaag naar 30 min
```

---

## 🔄 Als je Opnieuw een Admin Moet Aanmaken

### Scenario: Wachtwoord vergeten / Account kwijt

**Optie 1: Via create_admin.php (Tijdelijk)**
1. Upload `create_admin.php` tijdelijk naar server via SFTP
2. Ga naar: `https://www.nulerenvoorlater.nl/api/admin/create_admin.php`
3. Volg de instructies
4. **VERWIJDER direct daarna!**

**Optie 2: Via phpMyAdmin (Veiliger)**
```sql
-- Verwijder oude admin:
DELETE FROM admin_users WHERE username = 'ramon';

-- Maak nieuwe admin:
INSERT INTO admin_users (username, password_hash, email, is_active) VALUES
('ramon', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'info@nulerenvoorlater.nl', 1);
```

**Optie 3: Reset Password Script**
Gebruik: `reset_password.php` (als geïmplementeerd)

---

## 📚 Gerelateerde Documentatie

- **Config Security:** `../README-CONFIG.md`
- **Database Setup:** `../../sql/setup.sql`
- **Comments System:** `../../docs/COMMENTS_INSTALL.md`

---

## ✅ Security Checklist

Na setup, verifieer:

- [ ] `create_admin.php` verwijderd van live server
- [ ] Admin login werkt: `login.php`
- [ ] `config.php` heeft permissions 600
- [ ] Rate limiting werkt (test met foute login)
- [ ] Dashboard is alleen toegankelijk na login
- [ ] CSRF tokens worden correct gevalideerd

---

**Laatst bijgewerkt:** 12 november 2025  
**Versie:** 1.0
