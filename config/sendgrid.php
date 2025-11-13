<?php
/**
 * Configurazione SendGrid per invio email OTP
 * 
 * SendGrid API Key - Ottienila da:
 * https://app.sendgrid.com/settings/api_keys
 */

// API Key SendGrid
define('SENDGRID_API_KEY', 'YOUR_SENDGRID_API_KEY_HERE');

// Email mittente (deve essere verificata su SendGrid)
define('SENDGRID_FROM_EMAIL', 'f.calcagna.ips@gmail.com');
define('SENDGRID_FROM_NAME', 'Trading AI');

// URL API SendGrid
define('SENDGRID_API_URL', 'https://api.sendgrid.com/v3/mail/send');
?>
