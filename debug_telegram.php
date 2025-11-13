<?php
require_once __DIR__ . '/config/telegram.php';

$chatId = '8329115809';
$testOTP = '123456';

$message = "🔐 *Trading AI - Codice OTP DEBUG*\n\n";
$message .= "Il tuo codice di verifica è: `$testOTP`\n\n";
$message .= "⏰ Valido per 5 minuti\n";
$message .= "⚠️ Non condividere questo codice con nessuno";

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
curl_setopt($ch, CURLOPT_VERBOSE, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "<h1>Debug Telegram API</h1>";
echo "<h3>Request:</h3>";
echo "<p><strong>URL:</strong> $url</p>";
echo "<p><strong>Chat ID:</strong> $chatId</p>";
echo "<p><strong>Token:</strong> " . substr(TELEGRAM_BOT_TOKEN, 0, 10) . "...</p>";

echo "<h3>Response:</h3>";
echo "<p><strong>HTTP Code:</strong> $httpCode</p>";

if ($httpCode === 200) {
    echo "<p style='color: green;'><strong>✅ Messaggio inviato!</strong></p>";
} else {
    echo "<p style='color: red;'><strong>❌ Errore invio!</strong></p>";
}

if ($curlError) {
    echo "<p style='color: red;'><strong>cURL Error:</strong> $curlError</p>";
}

echo "<h3>API Response:</h3>";
$responseData = json_decode($response, true);
echo "<pre>" . print_r($responseData, true) . "</pre>";

echo "<h3>Raw Response:</h3>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";
?>
