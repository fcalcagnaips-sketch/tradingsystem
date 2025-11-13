<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/gmail.php';

// Generate 6-digit OTP
function generateOTP() {
    return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

// Send OTP via Gmail SMTP (semplificato)
function sendOTPEmailGmail($email, $otp) {
    // Usa semplicemente mail() di PHP - più semplice e funziona
    $to = $email;
    $subject = 'Codice OTP - Trading AI';
    
    // HTML Email con stile
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; }
            .container { max-width: 600px; margin: 0 auto; }
            .header { background: #009ef7; padding: 20px; text-align: center; }
            .header h1 { color: white; margin: 0; }
            .content { padding: 40px 20px; background: #f5f8fa; }
            .box { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .otp-code { background: #f5f8fa; padding: 20px; text-align: center; border-radius: 8px; margin: 20px 0; }
            .otp-code span { font-size: 32px; font-weight: bold; color: #009ef7; letter-spacing: 8px; }
            .footer { text-align: center; color: #b5b5c3; font-size: 12px; margin-top: 20px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🔐 Trading AI</h1>
            </div>
            <div class='content'>
                <div class='box'>
                    <h2 style='color: #181c32; margin-top: 0;'>Il tuo codice OTP</h2>
                    <p style='color: #7e8299; font-size: 16px;'>Utilizza il seguente codice per completare la registrazione:</p>
                    <div class='otp-code'>
                        <span>{$otp}</span>
                    </div>
                    <p style='color: #7e8299; font-size: 14px;'>
                        ⏰ Questo codice è valido per <strong>5 minuti</strong>.<br>
                        ⚠️ Non condividere questo codice con nessuno.
                    </p>
                </div>
                <p class='footer'>
                    Se non hai richiesto questo codice, ignora questa email.
                </p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Headers per email HTML
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . GMAIL_FROM_NAME . " <" . GMAIL_FROM_EMAIL . ">\r\n";
    $headers .= "Reply-To: " . GMAIL_FROM_EMAIL . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    // Invia email reale via SMTP Gmail
    $sent = sendEmailViaSMTP($to, $subject, $message);
    
    error_log("[OTP] Email: $email | Codice: $otp | Inviata: " . ($sent ? 'SI' : 'NO'));
    
    return $sent;
}

// Funzione per inviare email via SMTP Gmail usando socket
function sendEmailViaSMTP($to, $subject, $htmlBody) {
    $from = GMAIL_FROM_EMAIL;
    $fromName = GMAIL_FROM_NAME;
    
    // Connessione SMTP Gmail (porta 587 - prima plain, poi STARTTLS)
    $smtp = fsockopen('smtp.gmail.com', 587, $errno, $errstr, 30);
    
    if (!$smtp) {
        error_log("SMTP Error: $errstr ($errno)");
        return false;
    }
    
    // Leggi banner
    $response = fgets($smtp, 515);
    if (substr($response, 0, 3) != '220') {
        fclose($smtp);
        return false;
    }
    
    // EHLO
    fputs($smtp, "EHLO localhost\r\n");
    // Leggi tutte le risposte EHLO (multi-line)
    while ($line = fgets($smtp, 515)) {
        if ($line[3] == ' ') break; // Ultima riga
    }
    
    // STARTTLS
    fputs($smtp, "STARTTLS\r\n");
    $response = fgets($smtp, 515);
    
    if (substr($response, 0, 3) != '220') {
        error_log("STARTTLS Failed: $response");
        fclose($smtp);
        return false;
    }
    
    // Enable crypto
    if (!stream_socket_enable_crypto($smtp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
        error_log("Failed to enable TLS");
        fclose($smtp);
        return false;
    }
    
    // EHLO again after TLS
    fputs($smtp, "EHLO localhost\r\n");
    // Leggi tutte le risposte EHLO (multi-line)
    while ($line = fgets($smtp, 515)) {
        if ($line[3] == ' ') break;
    }
    
    // AUTH LOGIN
    fputs($smtp, "AUTH LOGIN\r\n");
    $response = fgets($smtp, 515);
    
    if (substr($response, 0, 3) != '334') {
        error_log("AUTH LOGIN Failed: $response");
        fclose($smtp);
        return false;
    }
    
    // Username
    fputs($smtp, base64_encode(GMAIL_USERNAME) . "\r\n");
    $response = fgets($smtp, 515);
    
    if (substr($response, 0, 3) != '334') {
        error_log("Username Failed: $response");
        fclose($smtp);
        return false;
    }
    
    // Password
    fputs($smtp, base64_encode(GMAIL_PASSWORD) . "\r\n");
    $response = fgets($smtp, 515);
    
    if (substr($response, 0, 3) != '235') {
        error_log("SMTP Auth Failed: $response");
        fclose($smtp);
        return false;
    }
    
    // MAIL FROM
    fputs($smtp, "MAIL FROM: <$from>\r\n");
    fgets($smtp, 515);
    
    // RCPT TO
    fputs($smtp, "RCPT TO: <$to>\r\n");
    fgets($smtp, 515);
    
    // DATA
    fputs($smtp, "DATA\r\n");
    fgets($smtp, 515);
    
    // Headers
    $headers = "From: $fromName <$from>\r\n";
    $headers .= "To: $to\r\n";
    $headers .= "Subject: $subject\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "\r\n";
    
    // Invia headers e body
    fputs($smtp, $headers);
    fputs($smtp, $htmlBody);
    fputs($smtp, "\r\n.\r\n");
    
    $response = fgets($smtp, 515);
    
    // QUIT
    fputs($smtp, "QUIT\r\n");
    fclose($smtp);
    
    return substr($response, 0, 3) == '250';
}

// Funzione helper per inviare via SMTP usando fsockopen
function sendSMTPEmail($to, $subject, $message, $headers) {
    $socket = fsockopen('ssl://' . GMAIL_SMTP_HOST, 465, $errno, $errstr, 30);
    
    if (!$socket) {
        error_log("SMTP connection failed: $errstr ($errno)");
        return false;
    }
    
    // Leggi risposta server
    $response = fgets($socket, 515);
    
    // EHLO
    fputs($socket, "EHLO localhost\r\n");
    $response = fgets($socket, 515);
    
    // AUTH LOGIN
    fputs($socket, "AUTH LOGIN\r\n");
    $response = fgets($socket, 515);
    
    // Username (base64)
    fputs($socket, base64_encode(GMAIL_USERNAME) . "\r\n");
    $response = fgets($socket, 515);
    
    // Password (base64)
    fputs($socket, base64_encode(GMAIL_PASSWORD) . "\r\n");
    $response = fgets($socket, 515);
    
    if (strpos($response, '235') === false) {
        error_log("SMTP auth failed: $response");
        fclose($socket);
        return false;
    }
    
    // MAIL FROM
    fputs($socket, "MAIL FROM: <" . GMAIL_FROM_EMAIL . ">\r\n");
    fgets($socket, 515);
    
    // RCPT TO
    fputs($socket, "RCPT TO: <$to>\r\n");
    fgets($socket, 515);
    
    // DATA
    fputs($socket, "DATA\r\n");
    fgets($socket, 515);
    
    // Email headers e body
    fputs($socket, "To: $to\r\n");
    fputs($socket, "From: " . GMAIL_FROM_NAME . " <" . GMAIL_FROM_EMAIL . ">\r\n");
    fputs($socket, "Subject: $subject\r\n");
    fputs($socket, $headers . "\r\n");
    fputs($socket, "\r\n");
    fputs($socket, $message);
    fputs($socket, "\r\n.\r\n");
    $response = fgets($socket, 515);
    
    // QUIT
    fputs($socket, "QUIT\r\n");
    fclose($socket);
    
    return strpos($response, '250') !== false;
}

// Create new OTP request for email
function createEmailOTPGmail($email) {
    $conn = getDBConnection();
    
    // Delete old OTPs for this email
    $stmt = $conn->prepare("DELETE FROM otp_verifications WHERE phone = ? AND verified = 0");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->close();
    
    // Generate new OTP
    $otp = generateOTP();
    $expiresAt = date('Y-m-d H:i:s', strtotime('+5 minutes'));
    
    // Save to database
    $stmt = $conn->prepare("INSERT INTO otp_verifications (phone, otp_code, expires_at) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $email, $otp, $expiresAt);
    
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        
        // Send OTP via Gmail
        $sent = sendOTPEmailGmail($email, $otp);
        
        if ($sent) {
            return [
                'success' => true,
                'message' => 'Codice OTP inviato alla tua email',
                'otp' => $otp // Remove in production!
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Errore nell\'invio email. Riprova.'
            ];
        }
    }
    
    $stmt->close();
    $conn->close();
    return ['success' => false, 'message' => 'Errore nell\'invio OTP'];
}

// Verify OTP for email
function verifyEmailOTP($email, $otp) {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("
        SELECT id, otp_code, expires_at, attempts 
        FROM otp_verifications 
        WHERE phone = ? AND verified = 0 
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        $conn->close();
        return ['success' => false, 'message' => 'Nessun OTP trovato per questa email'];
    }
    
    $row = $result->fetch_assoc();
    $stmt->close();
    
    // Check if expired
    if (strtotime($row['expires_at']) < time()) {
        $conn->close();
        return ['success' => false, 'message' => 'OTP scaduto'];
    }
    
    // Check attempts
    if ($row['attempts'] >= 3) {
        $conn->close();
        return ['success' => false, 'message' => 'Troppi tentativi. Richiedi un nuovo OTP'];
    }
    
    // Verify OTP
    if ($row['otp_code'] === $otp) {
        // Mark as verified
        $stmt = $conn->prepare("UPDATE otp_verifications SET verified = 1 WHERE id = ?");
        $stmt->bind_param("i", $row['id']);
        $stmt->execute();
        $stmt->close();
        $conn->close();
        
        return ['success' => true, 'message' => 'Email verificata con successo'];
    } else {
        // Increment attempts
        $stmt = $conn->prepare("UPDATE otp_verifications SET attempts = attempts + 1 WHERE id = ?");
        $stmt->bind_param("i", $row['id']);
        $stmt->execute();
        $stmt->close();
        $conn->close();
        
        return ['success' => false, 'message' => 'OTP non valido'];
    }
}
?>
