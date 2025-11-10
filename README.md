# Nu leren voor later - Website

Praktische AI-tools en inspiratie voor onderwijsprofessionals.

## 🚀 Automatische Deployment Workflow

Deze website gebruikt GitHub Actions voor automatische deployment naar Strato hosting.

### Setup Instructies

#### 1. GitHub Repository Aanmaken

1. Ga naar [GitHub](https://github.com) en maak een nieuwe repository aan
2. Kies een naam (bijv. `nulerenvoorlater-website`)
3. Maak de repository **private** of **public** (naar wens)
4. **Voeg GEEN** README, .gitignore of license toe (die hebben we al lokaal)

#### 2. GitHub Secrets Configureren

Voor de automatische SFTP deployment moet je de Strato FTP credentials toevoegen:

1. Ga naar je GitHub repository
2. Klik op **Settings** → **Secrets and variables** → **Actions**
3. Klik op **New repository secret** en voeg de volgende secrets toe:

   - **FTP_SERVER**: `your-strato-ftp-server.com` (bijv. `ftp.strato.com` of specifieke server)
   - **FTP_USERNAME**: Je Strato FTP gebruikersnaam
   - **FTP_PASSWORD**: Je Strato FTP wachtwoord

⚠️ **Belangrijk**: Vul hier je echte Strato FTP gegevens in!

#### 3. Lokale Repository Koppelen

Voer de volgende commando's uit in de terminal:

\`\`\`bash
# Hernoem branch naar 'main' (als die 'master' heet)
git branch -M main

# Voeg je GitHub repository toe (vervang USERNAME en REPO)
git remote add origin https://github.com/USERNAME/REPO.git

# Eerste commit maken
git add .
git commit -m "Initiele commit: website met auto-deploy workflow"

# Push naar GitHub
git push -u origin main
\`\`\`

#### 4. Verificatie

Na de eerste push:

1. Ga naar je GitHub repository
2. Klik op **Actions** tab
3. Je zou de workflow "Deploy naar Strato" moeten zien draaien
4. Als alles groen is ✅, is je website automatisch geüpload naar Strato!

---

## 📝 Hoe werkt het?

### Workflow

\`\`\`
Lokaal wijzigingen maken
      ↓
git add . && git commit -m "beschrijving"
      ↓
git push
      ↓
GitHub Actions triggert automatisch
      ↓
Website wordt via SFTP naar Strato geüpload
      ↓
✅ Live!
\`\`\`

### Handmatige Deploy

Je kunt ook handmatig een deployment triggeren:

1. Ga naar **Actions** tab op GitHub
2. Selecteer **Deploy naar Strato** workflow
3. Klik op **Run workflow**

---

## 📁 Project Structuur

\`\`\`
.
├── .github/
│   └── workflows/
│       └── deploy.yml          # Auto-deployment configuratie
├── css/
│   └── styles.css
├── images/
│   └── thumbnails/
├── js/
│   ├── site.js                 # Hoofd JavaScript
│   └── site-backup.js
├── videos/
├── index.html                  # Homepage
├── edubvlogs.html              # Video blogs
├── tools.html                  # AI Tools
├── missie.html                 # Missie pagina
├── over.html                   # Over pagina
├── contact.html                # Contact formulier
├── privacy.html                # Privacy policy
├── .gitignore                  # Git uitsluitingen
└── README.md                   # Deze documentatie
\`\`\`

---

## 🔄 Dagelijkse Workflow

\`\`\`bash
# 1. Wijzigingen maken in VS Code

# 2. Status bekijken
git status

# 3. Wijzigingen toevoegen
git add .

# 4. Commit maken
git commit -m "Beschrijving van wijziging"

# 5. Naar GitHub pushen (triggert auto-deploy)
git push
\`\`\`

---

## 🤖 Toekomstige Automatisering

De huidige setup maakt het mogelijk om in de toekomst:

- **Automatische blog posts** genereren met AI (Python scripts)
- **Video content** automatisch verwerken en publiceren
- **Scheduled deploys** (bijv. elke dag om 08:00)
- **Preview environments** voor test-versies

Voorbeeld toekomstige workflow structuur:

\`\`\`
.github/workflows/
├── deploy.yml              # ✅ Huidige: auto-deploy
├── generate-content.yml    # 🔮 Toekomst: AI content generatie
└── scheduled-post.yml      # 🔮 Toekomst: geplande publicaties
\`\`\`

---

## 🛠️ Troubleshooting

### Deployment faalt?

1. Check GitHub Actions logs (Actions tab → klik op gefaalde run)
2. Verificeer of FTP secrets correct zijn ingesteld
3. Test FTP verbinding handmatig met een FTP client

### Git problemen?

\`\`\`bash
# Status bekijken
git status

# Laatste commits bekijken
git log --oneline -5

# Naar vorige versie terugkeren
git revert HEAD
\`\`\`

---

## 📞 Contact

Website: [www.nulerenvoorlater.nl](https://www.nulerenvoorlater.nl)

---

**Gemaakt met ❤️ voor onderwijsprofessionals**
