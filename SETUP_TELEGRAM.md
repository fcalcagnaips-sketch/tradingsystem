# Setup Sistema Registrazione con OTP Telegram

## 📋 Panoramica
Sistema di registrazione in 3 step con verifica OTP via Telegram.

## 🚀 Setup Database

1. Esegui il file SQL per aggiornare il database:
```sql
mysql -u root tradingai < update_database.sql
```

Oppure esegui manualmente da phpMyAdmin o MySQL Workbench.

## 🤖 Configurazione Bot Telegram

### Passo 1: Creare il Bot

1. Apri Telegram e cerca **@BotFather**
2. Invia il comando `/newbot`
3. Scegli un nome per il bot (es: "Trading AI Bot")
4. Scegli uno username per il bot (deve finire con "bot", es: "tradingai_otp_bot")
5. Copia il **Token** che ti viene fornito

### Passo 2: Configurare il Token

1. Apri il file `includes/otp.php`
2. Trova la riga:
```php
$botToken = 'YOUR_BOT_TOKEN_HERE';
```
3. Sostituisci con il tuo token:
```php
$botToken = '1234567890:ABCdefGHIjklMNOpqrsTUVwxyz';
```

### Passo 3: Impostare il Webhook (Opzionale per Produzione)

Per ricevere messaggi in tempo reale, configura il webhook:

```
https://api.telegram.org/botYOUR_TOKEN/setWebhook?url=https://yourdomain.com/tradingai/telegram_webhook.php
```

Sostituisci:
- `YOUR_TOKEN` con il token del bot
- `yourdomain.com` con il tuo dominio

## 👥 Flusso Utente

### Per l'utente che si registra:

1. **Prima della registrazione** - L'utente deve:
   - Aprire Telegram
   - Cercare il bot (username scelto prima)
   - Inviare `/start`
   - Premere "📱 Condividi Numero" per collegare il telefono

2. **Durante la registrazione**:
   - **Step 1**: Inserisce il numero di telefono
   - **Step 2**: Riceve l'OTP su Telegram e lo inserisce
   - **Step 3**: Completa la registrazione con dati personali

## 📁 File Coinvolti

```
tradingai/
├── register.php              # Pagina registrazione (3 step)
├── includes/
│   └── otp.php               # Gestione OTP e invio Telegram
├── config/
│   ├── telegram.php          # Configurazione bot
│   └── database.php          # Configurazione DB
├── telegram_webhook.php      # Webhook per bot (gestisce messaggi)
├── update_database.sql       # Script aggiornamento DB
└── SETUP_TELEGRAM.md         # Questo file
```

## 🔧 Test in Sviluppo

Per testare senza Telegram configurato:

1. Il codice OTP viene loggato in `error_log` di PHP
2. Guarda il file di log per vedere l'OTP generato
3. L'OTP è visibile anche nell'alert di successo (RIMUOVERE IN PRODUZIONE!)

## 🔒 Sicurezza

**IMPORTANTE per la produzione:**

1. In `includes/otp.php`, rimuovi questa riga:
```php
'otp' => $otp // Remove in production!
```

2. In `register.php`, rimuovi la visualizzazione dell'OTP:
```php
$success = $result['message'] . ' (OTP: ' . $result['otp'] . ')';
```
Cambia in:
```php
$success = $result['message'];
```

3. Abilita HTTPS per proteggere le comunicazioni
4. Limita i tentativi di OTP (già implementato: max 3)
5. OTP valido solo 5 minuti (già implementato)

## 📝 Database Schema

### Tabella `users` - Nuovi campi:
- `first_name` VARCHAR(50)
- `last_name` VARCHAR(50)
- `phone` VARCHAR(20)
- `phone_verified` TINYINT(1)
- `telegram_id` VARCHAR(50)
- `telegram_verified` TINYINT(1)

### Tabella `otp_verifications`:
- `id` INT AUTO_INCREMENT PRIMARY KEY
- `phone` VARCHAR(50) - Numero di telefono
- `otp_code` VARCHAR(6) - Codice OTP
- `created_at` TIMESTAMP
- `expires_at` TIMESTAMP - Scadenza
- `verified` TINYINT(1) - Se verificato
- `attempts` INT - Tentativi falliti

## 🎯 Funzionalità

✅ Registrazione multi-step
✅ Verifica telefono con OTP
✅ OTP inviato via Telegram
✅ Password visibile con toggle
✅ Conferma password con validazione
✅ Validazione email
✅ Username unico
✅ Password cifrate (bcrypt)
✅ Protezione SQL injection
✅ Limite tentativi OTP
✅ Scadenza OTP (5 minuti)
✅ Step indicator visivo

## 🆘 Troubleshooting

### OTP non arriva su Telegram
1. Verifica che il bot sia stato avviato dall'utente (/start)
2. Verifica che l'utente abbia condiviso il numero
3. Controlla che il token sia corretto in `includes/otp.php`
4. Verifica i log: `telegram_log.txt` e PHP error log

### Errore "Telegram Chat ID non trovato"
- L'utente deve prima avviare il bot e condividere il numero
- Guida l'utente ad aprire il bot prima della registrazione

### Webhook non funziona
1. Verifica che l'URL sia raggiungibile pubblicamente
2. Usa HTTPS (richiesto da Telegram)
3. Controlla i log del webhook in `telegram_log.txt`

## 📞 Supporto

Per problemi o domande, controlla:
- Documentazione Telegram Bot API: https://core.telegram.org/bots/api
- Log PHP: controlla errori in `error_log`
- Log Telegram: controlla `telegram_log.txt`
