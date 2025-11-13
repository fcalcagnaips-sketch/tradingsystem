<?php
/**
 * Script di test per verificare invio OTP via SendGrid Email
 */

require_once __DIR__ . '/includes/email_otp.php';

// Inserisci la tua email per il test
$testEmail = 'f.calcagna.ips@gmail.com';

// Genera un OTP di test
$testOTP = generateOTP();

echo "<h1>Test SendGrid Email OTP</h1>";
echo "<p><strong>Email destinatario:</strong> $testEmail</p>";
echo "<p><strong>OTP generato:</strong> $testOTP</p>";
echo "<hr>";

// Prova a inviare l'email
echo "<p>Invio email in corso...</p>";
$sent = sendOTPEmail($testEmail, $testOTP);

if ($sent) {
    echo "<p style='color: green;'><strong>✅ Email inviata con successo!</strong></p>";
    echo "<p>Controlla la tua casella email (anche spam) per vedere l'OTP.</p>";
} else {
    echo "<p style='color: red;'><strong>❌ Errore nell'invio email!</strong></p>";
    echo "<p>Controlla i log di PHP per dettagli.</p>";
}

echo "<hr>";
echo "<h3>Informazioni SendGrid:</h3>";
echo "<p><strong>API Key configurata:</strong> " . substr(SENDGRID_API_KEY, 0, 15) . "...</p>";
echo "<p><strong>From Email:</strong> " . SENDGRID_FROM_EMAIL . "</p>";
echo "<p><strong>From Name:</strong> " . SENDGRID_FROM_NAME . "</p>";

echo "<hr>";
echo "<p><a href='register.php'>Vai alla registrazione</a></p>";
?>
