# 🔐 Database Configuratie - Nu leren voor later

## ⚠️ Belangrijk - Veiligheidsrichtlijnen

Dit bestand legt uit hoe je veilig omgaat met de database configuratie van dit project.

---

## 📋 Overzicht Configuratiebestanden

| Bestand | Doel | Git Status | Deployment |
|---------|------|------------|------------|
| `config.example.php` | Template/voorbeeld | ✅ In Git | ✅ Wordt gedeployed |
| `config.php` | **PRODUCTIE** met echte wachtwoorden | ❌ NOOIT in Git | ❌ Wordt NIET gedeployed |
| `config.deploy.php.tpl` | Template voor server | ✅ In Git | ✅ Wordt gedeployed |

---

## 🏠 Voor Lokale Development

### Eerste keer setup:

1. **Kopieer het voorbeeld bestand:**
   ```bash
   cd api/
   cp config.example.php config.php
   ```

2. **Vul je lokale database gegevens in:**
   - Open `config.php` in je editor
   - Pas `DB_HOST`, `DB_NAME`, `DB_USER`, en `DB_PASS` aan
   - Gebruik je lokale MySQL/MariaDB gegevens

3. **Verifieer beveiliging:**
   ```bash
   # Controleer dat config.php in .gitignore staat
   grep "api/config.php" ../.gitignore
   
   # Controleer dat Git het bestand negeert
   git status api/config.php
   # Output moet zijn: "No matches found" of niets
   ```

### ⚠️ NOOIT DOEN:
- ❌ `config.php` committen naar Git
- ❌ Productie wachtwoorden in `config.example.php` zetten
- ❌ Screenshots delen met wachtwoorden zichtbaar
- ❌ Het bestand naar een publieke locatie uploaden

---

## 🌐 Voor Productie (Strato Server)

### Optie 1: Handmatig aanmaken via SFTP

1. **Verbind met je Strato SFTP:**
   - Server: `ssh.strato.com`
   - Gebruiker: `538263254.swh.strato-hosting.eu`
   - Map: `/Nulerenvoorlater/api/`

2. **Maak config.php aan op de server:**
   - Upload `config.example.php` als basis
   - Hernoem naar `config.php`
   - Bewerk met de Strato database gegevens

3. **Zet juiste permissions:**
   ```bash
   chmod 600 config.php
   ```

### Optie 2: Via config.deploy.php.tpl

1. Het bestand `config.deploy.php.tpl` wordt automatisch gedeployed
2. Via SFTP of SSH:
   ```bash
   cd /Nulerenvoorlater/api/
   cp config.deploy.php.tpl config.php
   nano config.php  # of vi config.php
   # Vul echte wachtwoorden in
   chmod 600 config.php
   ```

### Strato Database Gegevens

Je vindt deze in je Strato hosting panel:
- **Inloggen:** https://www.strato.de/apps/CustomerService
- **Navigeer:** Hosting → Database beheer
- **Noteer:**
  - Database host (bijv: `database-5018989034.webspace-host.com`)
  - Database naam (bijv: `dbs14955096`)
  - Database gebruiker (bijv: `dbu5433971`)
  - Database wachtwoord

---

## 🛡️ Beveiligingsmaatregelen

### Huidige bescherming:

✅ **Git Ignore**
- `config.php` staat in `.gitignore`
- Wordt nooit naar GitHub gepusht

✅ **Deployment Exclusion**
- GitHub Actions deployt `config.php` NIET naar server
- Je productie-configuratie wordt nooit overschreven

✅ **File Permissions**
- Automatisch ingesteld op `chmod 600` (alleen owner kan lezen)
- Beschermt tegen andere users op shared hosting

### File Permissions uitleg:

| Permission | Betekenis | Voor wie? |
|------------|-----------|-----------|
| `600` | Read/Write, alleen owner | `config.php` |
| `644` | Read voor iedereen, Write voor owner | Meeste `.php` bestanden |
| `700` | Volledige toegang, alleen owner | `admin/` map |
| `755` | Lees/Execute voor iedereen | `css/`, `js/`, `images/` |

---

## 🔧 Troubleshooting

### "Connection failed" foutmelding

**Controleer:**
1. Is `config.php` aanwezig op de server?
2. Zijn de database gegevens correct?
3. Is de database actief in Strato panel?
4. Staat je IP niet geblokkeerd?

**Test je connectie:**
```php
// Test in een apart bestand: test-db.php
<?php
require_once 'config.php';
require_once 'db.php';

try {
    $pdo = getDBConnection();
    echo "✅ Database connectie succesvol!";
} catch (Exception $e) {
    echo "❌ Fout: " . $e->getMessage();
}
```

### config.php per ongeluk in Git?

**Verwijder uit Git geschiedenis:**
```bash
# Verwijder uit huidige commit
git rm --cached api/config.php

# Commit de verwijdering
git commit -m "Remove config.php from Git tracking"

# Push naar GitHub
git push origin main

# Verifieer
git ls-files | grep config.php
# Output moet leeg zijn
```

**Voor complete geschiedenis verwijdering:**
```bash
# Gebruik BFG Repo-Cleaner of git filter-branch
# Dit is complexer - documentatie: https://git-scm.com/docs/git-filter-branch
```

### Nieuwe developer onboarding

**Stap 1:** Clone repository
```bash
git clone https://github.com/jrvisscher8219/nulerenvoorlater.git
cd nulerenvoorlater
```

**Stap 2:** Setup config
```bash
cd api
cp config.example.php config.php
# Vraag aan projectleider om lokale database gegevens
nano config.php
```

**Stap 3:** Verifieer
```bash
grep "config.php" ../.gitignore  # Moet match tonen
git status api/config.php        # Moet niets tonen
```

---

## 📚 Gerelateerde Documentatie

- **Comments Systeem:** `../docs/COMMENTS_INSTALL.md`
- **Database Setup:** `../sql/setup.sql`
- **Deployment:** `../.github/workflows/deploy.yml`
- **Admin Dashboard:** `./admin/README.md` (indien aanwezig)

---

## 🆘 Contact & Support

Bij vragen over database configuratie:
- **Email:** info@nulerenvoorlater.nl
- **Project:** https://github.com/jrvisscher8219/nulerenvoorlater
- **Website:** https://www.nulerenvoorlater.nl

---

**Laatst bijgewerkt:** 12 november 2025  
**Versie:** 1.0
