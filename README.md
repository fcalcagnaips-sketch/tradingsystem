# Trading AI System

Sistema di trading con intelligenza artificiale - Dashboard con autenticazione

## Caratteristiche

- ✅ Sistema di login sicuro con password cifrate (bcrypt)
- ✅ Dashboard moderna stile Metronic
- ✅ Gestione sessioni PHP
- ✅ Database MySQL
- ✅ Design responsive con Bootstrap 5

## Requisiti

- XAMPP (Apache + MySQL + PHP 7.4+)
- Browser moderno

## Installazione

1. Assicurati che XAMPP sia in esecuzione (Apache e MySQL)

2. Apri il browser e vai su:
   ```
   http://localhost/tradingai/install.php
   ```

3. Lo script creerà automaticamente:
   - Database `tradingai`
   - Tabella `users`
   - Utente amministratore di default

## Credenziali di accesso

**Username:** admin  
**Password:** admin123

## Accesso al sistema

Dopo l'installazione, vai su:
```
http://localhost/tradingai/login.php
```

## Struttura del progetto

```
tradingai/
├── assets/
│   ├── css/          # File CSS personalizzati
│   └── js/           # File JavaScript
├── config/
│   └── database.php  # Configurazione database
├── includes/
│   └── auth.php      # Funzioni di autenticazione
├── login.php         # Pagina di login
├── dashboard.php     # Dashboard principale
├── logout.php        # Script di logout
├── install.php       # Script di installazione
└── README.md         # Questo file
```

## Sicurezza

- Le password sono cifrate con `password_hash()` (bcrypt)
- Protezione contro SQL injection con prepared statements
- Gestione sicura delle sessioni PHP
- Validazione input utente

## Note

- Per modificare le credenziali del database, edita il file `config/database.php`
- Dopo la prima installazione, è consigliato eliminare o proteggere il file `install.php`
