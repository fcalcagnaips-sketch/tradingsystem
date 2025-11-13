<?php
/**
 * Configurazione Gmail SMTP per invio email OTP
 * 
 * Come ottenere App Password:
 * 1. Vai su: https://myaccount.google.com/apppasswords
 * 2. Genera una "App Password" per Mail
 * 3. Copia la password (16 caratteri)
 * 4. Incollala qui sotto
 */

// Configurazione Gmail SMTP
define('GMAIL_SMTP_HOST', 'smtp.gmail.com');
define('GMAIL_SMTP_PORT', 587);
define('GMAIL_SMTP_ENCRYPTION', 'tls');

// Il tuo account Gmail
define('GMAIL_USERNAME', 'f.calcagna.ips@gmail.com');
define('GMAIL_PASSWORD', 'vevaheuqwwwbrorc'); // App Password Gmail (16 caratteri)

// Email mittente
define('GMAIL_FROM_EMAIL', 'f.calcagna.ips@gmail.com');
define('GMAIL_FROM_NAME', 'Trading AI');
?>
