<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/sendgrid.php';

// Generate 6-digit OTP
function generateOTP() {
    return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

// Send OTP via SendGrid Email
function sendOTPEmail($email, $otp) {
    $message = [
        'personalizations' => [[
            'to' => [['email' => $email]],
            'subject' => 'Codice OTP - Trading AI'
        ]],
        'from' => [
            'email' => SENDGRID_FROM_EMAIL,
            'name' => SENDGRID_FROM_NAME
        ],
        'content' => [[
            'type' => 'text/html',
            'value' => "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                    <div style='background: #009ef7; padding: 20px; text-align: center;'>
                        <h1 style='color: white; margin: 0;'>🔐 Trading AI</h1>
                    </div>
                    <div style='padding: 40px 20px; background: #f5f8fa;'>
                        <div style='background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
                            <h2 style='color: #181c32; margin-top: 0;'>Il tuo codice OTP</h2>
                            <p style='color: #7e8299; font-size: 16px;'>Utilizza il seguente codice per completare la registrazione:</p>
                            <div style='background: #f5f8fa; padding: 20px; text-align: center; border-radius: 8px; margin: 20px 0;'>
                                <span style='font-size: 32px; font-weight: bold; color: #009ef7; letter-spacing: 8px;'>{$otp}</span>
                            </div>
                            <p style='color: #7e8299; font-size: 14px;'>
                                ⏰ Questo codice è valido per <strong>5 minuti</strong>.<br>
                                ⚠️ Non condividere questo codice con nessuno.
                            </p>
                        </div>
                        <p style='text-align: center; color: #b5b5c3; font-size: 12px; margin-top: 20px;'>
                            Se non hai richiesto questo codice, ignora questa email.
                        </p>
                    </div>
                </div>
            "
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

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Log per debugging
    error_log("SendGrid response for $email: HTTP $httpCode - $response");

    return $httpCode >= 200 && $httpCode < 300;
}

// Create new OTP request for email
function createEmailOTP($email) {
    $conn = getDBConnection();
    
    // Delete old OTPs for this email
    $stmt = $conn->prepare("DELETE FROM otp_verifications WHERE phone = ? AND verified = 0");
    $stmt->bind_param("s", $email); // Riuso campo phone per email
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
        
        // Send OTP via Email
        $sent = sendOTPEmail($email, $otp);
        
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

// Check if email is verified
function isEmailVerified($email) {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM otp_verifications 
        WHERE phone = ? AND verified = 1
    ");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    $stmt->close();
    $conn->close();
    
    return $row['count'] > 0;
}
?>
