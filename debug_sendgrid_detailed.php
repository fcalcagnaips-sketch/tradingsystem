<?php
require_once __DIR__ . '/config/sendgrid.php';

echo "<h1>Debug SendGrid API</h1>";

// Test 1: Verifica configurazione
echo "<h3>1. Configurazione:</h3>";
echo "<p><strong>API Key:</strong> " . substr(SENDGRID_API_KEY, 0, 20) . "...</p>";
echo "<p><strong>From Email:</strong> " . SENDGRID_FROM_EMAIL . "</p>";
echo "<p><strong>API URL:</strong> " . SENDGRID_API_URL . "</p>";
echo "<hr>";

// Test 2: Verifica API Key con chiamata semplice
echo "<h3>2. Test API Key (verifica validità):</h3>";
$ch = curl_init('https://api.sendgrid.com/v3/scopes');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . SENDGRID_API_KEY
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    echo "<p style='color: green;'>✅ API Key valida!</p>";
    echo "<pre>Scopes: " . htmlspecialchars($response) . "</pre>";
} else {
    echo "<p style='color: red;'>❌ API Key non valida! HTTP: $httpCode</p>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
}
echo "<hr>";

// Test 3: Invia email di test
echo "<h3>3. Test invio email:</h3>";
$testEmail = 'f.calcagna.ips@gmail.com';
$message = [
    'personalizations' => [[
        'to' => [['email' => $testEmail]]
    ]],
    'from' => [
        'email' => SENDGRID_FROM_EMAIL,
        'name' => SENDGRID_FROM_NAME
    ],
    'subject' => 'Test SendGrid',
    'content' => [[
        'type' => 'text/plain',
        'value' => 'Questo è un test di invio email da SendGrid.'
    ]]
];

$ch = curl_init(SENDGRID_API_URL);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . SENDGRID_API_KEY,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_VERBOSE, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "<p><strong>HTTP Code:</strong> $httpCode</p>";
if ($httpCode >= 200 && $httpCode < 300) {
    echo "<p style='color: green;'>✅ Email inviata con successo!</p>";
} else {
    echo "<p style='color: red;'>❌ Errore invio email!</p>";
}

if ($curlError) {
    echo "<p style='color: red;'><strong>cURL Error:</strong> $curlError</p>";
}

echo "<h4>Risposta API:</h4>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";

echo "<h4>Request inviata:</h4>";
echo "<pre>" . htmlspecialchars(json_encode($message, JSON_PRETTY_PRINT)) . "</pre>";
?>
