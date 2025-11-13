<?php
/**
 * Configurazione Bot Telegram
 * 
 * ISTRUZIONI PER CONFIGURARE IL BOT:
 * 
 * 1. Apri Telegram e cerca @BotFather
 * 2. Invia il comando /newbot
 * 3. Segui le istruzioni per creare il bot
 * 4. Copia il token che ti viene fornito
 * 5. Incolla il token nella variabile $botToken qui sotto
 * 6. Avvia il bot con /start
 * 
 * PER COLLEGARE IL NUMERO DI TELEFONO AL TELEGRAM ID:
 * - Gli utenti devono avviare il bot prima di registrarsi
 * - Il bot può richiedere il numero di telefono
 * - Salva l'associazione phone <-> chat_id nel database
 */

// Token del Bot Telegram (ottieni da @BotFather)
define('TELEGRAM_BOT_TOKEN', 'YOUR_BOT_TOKEN_HERE');

// URL API Telegram
define('TELEGRAM_API_URL', 'https://api.telegram.org/bot' . TELEGRAM_BOT_TOKEN);

/**
 * Webhook per ricevere messaggi (opzionale)
 * Imposta il webhook con:
 * https://api.telegram.org/botYOUR_TOKEN/setWebhook?url=https://yourdomain.com/tradingai/telegram_webhook.php
 */
?>
