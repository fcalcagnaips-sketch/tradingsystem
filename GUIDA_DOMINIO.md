# 🌐 Guida Deployment su Dominio - Trading AI

Questa guida ti aiuterà a spostare Trading AI da localhost a un dominio reale e configurare l'email professionale.

---

## 📋 INDICE

1. [Preparazione Server](#1-preparazione-server)
2. [Upload Files](#2-upload-files)
3. [Configurazione Database](#3-configurazione-database)
4. [Cambio Email Gmail](#4-cambio-email-gmail)
5. [Configurazione Domini Email](#5-configurazione-domini-email)
6. [Aggiornamenti Codice](#6-aggiornamenti-codice)
7. [Test Finali](#7-test-finali)
8. [Risoluzione Problemi](#8-risoluzione-problemi)

---

## 1. PREPARAZIONE SERVER

### Requisiti Hosting:
- ✅ **PHP 8.0+** (verifica con `php -v`)
- ✅ **MySQL 5.7+** o **MariaDB 10.3+**
- ✅ **Apache** con mod_rewrite
- ✅ **SSL Certificate** (HTTPS obbligatorio)
- ✅ **Estensioni PHP:**
  - mysqli
  - openssl
  - curl
  - mbstring
  - json

### Verifica Server:
```bash
# SSH nel server
ssh user@tuodominio.com

# Verifica PHP
php -v
php -m | grep mysqli
php -m | grep openssl
php -m | grep curl
```

---

## 2. UPLOAD FILES

### Metodo A: FTP/SFTP (FileZilla, WinSCP)

1. **Connettiti via FTP** al tuo hosting
   - Host: `ftp.tuodominio.com` o IP server
   - Username: tuo username hosting
   - Password: tua password
   - Porta: 21 (FTP) o 22 (SFTP)

2. **Carica tutti i file** nella root del dominio:
   ```
   /public_html/tradingai/
   o
   /var/www/html/tradingai/
   ```

3. **Escludi questi file** (non caricarli):
   - `test_*.php`
   - `debug_*.php`
   - `.env.example`
   - `vendor/` (se vuoto)

### Metodo B: Git (consigliato)

```bash
# SSH nel server
cd /var/www/html/

# Clone repository
git clone https://github.com/fcalcagnaips-sketch/tradingsystem.git tradingai
cd tradingai

# Pull latest
git pull origin main
```

---

## 3. CONFIGURAZIONE DATABASE

### Step 1: Crea Database sul Server

**Via cPanel/Plesk:**
1. Vai su **MySQL Databases**
2. Crea nuovo database: `tradingai_prod`
3. Crea utente: `tradingai_user`
4. Genera password sicura
5. Assegna **tutti i privilegi** all'utente

**Via SSH:**
```bash
mysql -u root -p
```

```sql
CREATE DATABASE tradingai_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'tradingai_user'@'localhost' IDENTIFIED BY 'PASSWORD_SICURA_QUI';
GRANT ALL PRIVILEGES ON tradingai_prod.* TO 'tradingai_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Step 2: Importa Database

**Via cPanel/phpMyAdmin:**
1. Apri **phpMyAdmin**
2. Seleziona database `tradingai_prod`
3. Tab **Import**
4. Carica file SQL nell'ordine:
   - `setup_database.sql`
   - `setup_roles.sql`
   - `setup_password_reset.sql`
5. Clicca **Go**

**Via SSH:**
```bash
cd /var/www/html/tradingai/

mysql -u tradingai_user -p tradingai_prod < setup_database.sql
mysql -u tradingai_user -p tradingai_prod < setup_roles.sql
mysql -u tradingai_user -p tradingai_prod < setup_password_reset.sql
```

### Step 3: Aggiorna config/database.php

Modifica `config/database.php`:

```php
<?php
// Configurazione Database PRODUZIONE
define('DB_HOST', 'localhost'); // o IP del database
define('DB_NAME', 'tradingai_prod');
define('DB_USER', 'tradingai_user');
define('DB_PASS', 'PASSWORD_SICURA_QUI');

function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error);
        die("Errore di connessione al database");
    }
    
    $conn->set_charset("utf8mb4");
    return $conn;
}
?>
```

---

## 4. CAMBIO EMAIL GMAIL

### Step 1: Nuovo Account Gmail (opzionale)

**Opzione A: Usa nuova email** (es: `noreply@tuodominio.com`)
- Crea nuovo account Gmail
- Oppure usa account esistente

**Opzione B: Usa Gmail esistente**
- Puoi continuare a usare `f.calcagna.ips@gmail.com`
- Salta alla Step 2

### Step 2: Genera App Password

1. **Vai su:** https://myaccount.google.com/apppasswords
2. **Attiva 2FA** se non attiva:
   - https://myaccount.google.com/signinoptions/twosv
3. **Crea App Password:**
   - Nome app: `Trading AI Production`
   - Copia password (16 caratteri, tipo: `abcd efgh ijkl mnop`)

### Step 3: Aggiorna config/gmail.php

Modifica `config/gmail.php`:

```php
<?php
// Configurazione Gmail SMTP

define('GMAIL_SMTP_HOST', 'smtp.gmail.com');
define('GMAIL_SMTP_PORT', 587);
define('GMAIL_SMTP_ENCRYPTION', 'tls');

// AGGIORNA QUI ⬇️
define('GMAIL_USERNAME', 'tuanuova@email.com'); // ← Cambia qui
define('GMAIL_PASSWORD', 'abcdefghijklmnop'); // ← App Password qui (senza spazi)

// Email mittente
define('GMAIL_FROM_EMAIL', 'tuanuova@email.com'); // ← Cambia qui
define('GMAIL_FROM_NAME', 'Trading AI'); // ← Nome mittente
?>
```

**⚠️ IMPORTANTE:**
- Rimuovi TUTTI gli spazi dalla App Password
- Usa solo email Gmail verificate
- NON commitare su GitHub con password reale

---

## 5. CONFIGURAZIONE DOMINIO EMAIL

### Opzione 1: Email Professionale (Consigliata)

Se vuoi usare email tipo `noreply@tuodominio.com`:

**Servizi consigliati:**
- **Google Workspace** (€5/mese) - professionale
- **Zoho Mail** (Gratis fino a 5 utenti)
- **ProtonMail** (Gratis/Premium)

**Setup Google Workspace:**
1. Registrati su: https://workspace.google.com/
2. Verifica dominio con DNS record
3. Crea email: `noreply@tuodominio.com`
4. Genera App Password come Step 4
5. Usa questa email in `config/gmail.php`

### Opzione 2: SMTP Hosting

Se il tuo hosting include email:

Modifica `includes/gmail_otp.php` e usa SMTP del tuo hosting:

```php
// Esempio con SMTP hosting
define('SMTP_HOST', 'mail.tuodominio.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'noreply@tuodominio.com');
define('SMTP_PASSWORD', 'password_email_hosting');
```

---

## 6. AGGIORNAMENTI CODICE

### Step 1: Aggiorna URL nei File

**File: `forgot_password.php` (riga 53)**

Cerca:
```php
$resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/tradingai/forgot_password.php?step=2&token=" . $token;
```

Cambia in:
```php
$resetLink = "https://tuodominio.com/forgot_password.php?step=2&token=" . $token;
```

### Step 2: Configura .htaccess

Crea/modifica `.htaccess` nella root:

```apache
# Force HTTPS
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Security Headers
Header set X-Content-Type-Options "nosniff"
Header set X-Frame-Options "SAMEORIGIN"
Header set X-XSS-Protection "1; mode=block"

# PHP Settings
php_value upload_max_filesize 10M
php_value post_max_size 10M
php_value max_execution_time 300
php_value max_input_time 300

# Disable directory listing
Options -Indexes

# Protect config files
<FilesMatch "^(\.env|config\.php|database\.php|gmail\.php|telegram\.php)$">
    Order allow,deny
    Deny from all
</FilesMatch>
```

### Step 3: Permessi File

```bash
# SSH nel server
cd /var/www/html/tradingai/

# Imposta owner
chown -R www-data:www-data .

# Imposta permessi
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;

# Proteggi config
chmod 640 config/*.php
```

---

## 7. TEST FINALI

### Checklist Test:

#### ✅ **1. Test Database**
```
https://tuodominio.com/login.php
```
- Login con admin / admin123 funziona?

#### ✅ **2. Test Registrazione**
```
https://tuodominio.com/register.php
```
- Registra nuovo utente
- Ricevi OTP via email? ✉️
- Email arriva correttamente?

#### ✅ **3. Test Password Dimenticata**
```
https://tuodominio.com/forgot_password.php
```
- Inserisci email
- Ricevi email di reset? ✉️
- Link funziona?
- Password si aggiorna?

#### ✅ **4. Test Dashboard**
```
https://tuodominio.com/dashboard.php
```
- Dashboard si carica?
- Sidebar funziona?

#### ✅ **5. Test Admin**
```
https://tuodominio.com/users.php
https://tuodominio.com/roles.php
```
- Pagine accessibili solo come admin?

---

## 8. RISOLUZIONE PROBLEMI

### ❌ Problema: Email non arrivano

**Causa:** SMTP bloccato o credenziali errate

**Soluzione:**
1. Verifica App Password in `config/gmail.php`
2. Controlla log: `/var/log/apache2/error.log`
3. Test SMTP:
   ```
   https://tuodominio.com/test_gmail.php
   ```
4. Verifica firewall server (porta 587 aperta)

### ❌ Problema: Errore connessione database

**Causa:** Credenziali database errate

**Soluzione:**
1. Verifica `config/database.php`
2. Test connessione:
   ```bash
   mysql -u tradingai_user -p tradingai_prod
   ```
3. Verifica privilegi utente

### ❌ Problema: Errore 500

**Causa:** Errori PHP o permessi

**Soluzione:**
1. Abilita log errori in `php.ini`:
   ```ini
   display_errors = On
   error_reporting = E_ALL
   ```
2. Controlla log Apache
3. Verifica permessi file (step 6.3)

### ❌ Problema: Link reset password non funziona

**Causa:** URL hardcoded sbagliato

**Soluzione:**
1. Verifica `forgot_password.php` linea 53
2. Assicurati usi `https://` e dominio corretto
3. Controlla timezone server:
   ```php
   date_default_timezone_set('Europe/Rome');
   ```

---

## 📦 CHECKLIST FINALE

Prima di andare LIVE, verifica:

- [ ] Database importato e configurato
- [ ] `config/database.php` aggiornato
- [ ] `config/gmail.php` con email corretta
- [ ] App Password Gmail funzionante
- [ ] URL aggiornati in `forgot_password.php`
- [ ] `.htaccess` configurato
- [ ] HTTPS attivo (SSL certificate)
- [ ] Permessi file corretti (640 per config)
- [ ] Test registrazione funzionante
- [ ] Test recupero password funzionante
- [ ] Test invio email OTP funzionante
- [ ] Credenziali admin cambiate (non usare admin/admin123)
- [ ] File di test rimossi (`test_*.php`, `debug_*.php`)
- [ ] Backup database fatto

---

## 🔒 SICUREZZA POST-DEPLOYMENT

### 1. Cambia Password Admin

```sql
-- Via phpMyAdmin o SSH
UPDATE users 
SET password = '$2y$10$NUOVA_PASSWORD_HASH_QUI' 
WHERE username = 'admin';
```

Oppure usa il sistema "Password Dimenticata".

### 2. Rimuovi File Debug

```bash
rm test_gmail.php
rm test_sendgrid.php
rm test_telegram_otp.php
rm debug_*.php
rm install.php
```

### 3. Backup Automatici

**Setup backup giornaliero:**

```bash
# Crea script backup
nano /root/backup_tradingai.sh
```

```bash
#!/bin/bash
BACKUP_DIR="/backup/tradingai"
DATE=$(date +%Y%m%d_%H%M%S)

# Backup database
mysqldump -u tradingai_user -pPASSWORD tradingai_prod > $BACKUP_DIR/db_$DATE.sql

# Backup files
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/html/tradingai/

# Rimuovi backup vecchi (>7 giorni)
find $BACKUP_DIR -name "*.sql" -mtime +7 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete
```

```bash
# Rendi eseguibile
chmod +x /root/backup_tradingai.sh

# Aggiungi a cron (ogni giorno alle 3:00)
crontab -e
0 3 * * * /root/backup_tradingai.sh
```

---

## 📞 SUPPORTO

**In caso di problemi:**

1. **Controlla log:**
   - `/var/log/apache2/error.log`
   - `/var/log/php_errors.log`

2. **Test componenti:**
   - Database: `mysql -u user -p`
   - PHP: `php -v`
   - SMTP: Usa tool online come https://www.gmass.co/smtp-test

3. **Verifica configurazione:**
   - `phpinfo()` per vedere estensioni PHP
   - `netstat -tulpn` per porte aperte

---

## ✅ TUTTO FATTO!

Il tuo sistema Trading AI è ora LIVE su:
```
https://tuodominio.com
```

**Link importanti:**
- Login: `https://tuodominio.com/login.php`
- Registrazione: `https://tuodominio.com/register.php`
- Dashboard: `https://tuodominio.com/dashboard.php`

**Buon trading! 🚀**
