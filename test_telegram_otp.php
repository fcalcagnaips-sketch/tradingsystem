<?php
/**
 * Script di test per verificare invio OTP su Telegram
 * Usa questo per testare il bot senza registrazione completa
 */

require_once __DIR__ . '/config/telegram.php';

// Il tuo chat_id Telegram
$chatId = '8329115809';

// Genera un OTP di test
$testOTP = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

// Messaggio OTP
$message = "🔐 *Trading AI - Codice OTP TEST*\n\n";
$message .= "Il tuo codice di verifica è: `$testOTP`\n\n";
$message .= "⏰ Valido per 5 minuti\n";
$message .= "⚠️ Non condividere questo codice con nessuno";

// Invia via Telegram Bot API
$url = "https://api.telegram.org/bot" . TELEGRAM_BOT_TOKEN . "/sendMessage";
$data = [
    'chat_id' => $chatId,
    'text' => $message,
    'parse_mode' => 'Markdown'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<h1>Test Invio OTP Telegram</h1>";
echo "<p><strong>Chat ID:</strong> $chatId</p>";
echo "<p><strong>OTP Generato:</strong> $testOTP</p>";
echo "<p><strong>HTTP Code:</strong> $httpCode</p>";

if ($httpCode === 200) {
    echo "<p style='color: green;'><strong>✅ SUCCESSO!</strong> Controlla il tuo Telegram (@umberobot)</p>";
} else {
    echo "<p style='color: red;'><strong>❌ ERRORE!</strong> Messaggio non inviato.</p>";
}

echo "<h3>Risposta API:</h3>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";

echo "<hr>";
echo "<p><a href='register.php'>Vai alla registrazione</a></p>";
?>
