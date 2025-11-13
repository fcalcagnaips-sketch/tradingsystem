<?php
require_once __DIR__ . '/includes/gmail_otp.php';

$testEmail = 'f.calcagna.ips@gmail.com';
$testOTP = generateOTP();

echo "<h1>Test Gmail SMTP - OTP Email</h1>";
echo "<p><strong>Email:</strong> $testEmail</p>";
echo "<p><strong>OTP:</strong> $testOTP</p>";
echo "<hr>";
echo "<p>Invio email in corso...</p>";

$sent = sendOTPEmailGmail($testEmail, $testOTP);

if ($sent) {
    echo "<p style='color: green;'><strong>✅ Email inviata!</strong></p>";
    echo "<p>Controlla la tua Gmail (anche spam).</p>";
} else {
    echo "<p style='color: red;'><strong>❌ Errore invio!</strong></p>";
    echo "<p>Controlla i log di PHP.</p>";
}
?>
