<?php
require_once __DIR__ . '/../config/database.php';

// Generate 6-digit OTP
function generateOTP() {
    return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

// Get Telegram Chat ID from phone number
function getTelegramChatId($phone) {
    $conn = getDBConnection();
    
    // Cerca negli utenti esistenti
    $stmt = $conn->prepare("SELECT telegram_id FROM users WHERE phone = ? AND telegram_id IS NOT NULL");
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stmt->close();
        $conn->close();
        return $row['telegram_id'];
    }
    
    $stmt->close();
    $conn->close();
    return null;
}

// Send OTP via Telegram Bot
function sendOTPTelegram($phone, $otp) {
    // Telegram Bot Token - DA CONFIGURARE
    $botToken = 'YOUR_BOT_TOKEN_HERE'; // Ottieni da @BotFather su Telegram
    
    // In un sistema reale, dovresti avere un database che mappa numeri di telefono a chat_id Telegram
    // Per ora usiamo il numero come chat_id (SOLO PER TESTING)
    $chatId = getTelegramChatId($phone);
    
    if (!$chatId) {
        // Per testing: l'utente deve prima avviare una conversazione con il bot
        error_log("Telegram Chat ID non trovato per $phone. L'utente deve avviare il bot.");
        error_log("OTP per $phone: $otp");
        return false;
    }
    
    $message = "🔐 *Trading AI - Codice OTP*\n\n";
    $message .= "Il tuo codice di verifica è: `$otp`\n\n";
    $message .= "⏰ Valido per 5 minuti\n";
    $message .= "⚠️ Non condividere questo codice con nessuno";
    
    $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
    $data = [
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'Markdown'
    ];
    
    // Per testing in development (rimuovi in produzione)
    error_log("OTP for $phone (Telegram ID: $chatId): $otp");
    
    // Invia messaggio via Telegram
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $httpCode === 200;
}

// Create new OTP request
function createOTP($phone) {
    $conn = getDBConnection();
    
    // Delete old OTPs for this phone
    $stmt = $conn->prepare("DELETE FROM otp_verifications WHERE phone = ? AND verified = 0");
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $stmt->close();
    
    // Generate new OTP
    $otp = generateOTP();
    $expiresAt = date('Y-m-d H:i:s', strtotime('+5 minutes'));
    
    // Save to database
    $stmt = $conn->prepare("INSERT INTO otp_verifications (phone, otp_code, expires_at) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $phone, $otp, $expiresAt);
    
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        
        // Send OTP via Telegram
        $sent = sendOTPTelegram($phone, $otp);
        
        return [
            'success' => true,
            'message' => 'OTP inviato su Telegram',
            'otp' => $otp // Remove in production!
        ];
    }
    
    $stmt->close();
    $conn->close();
    return ['success' => false, 'message' => 'Errore nell\'invio OTP'];
}

// Verify OTP
function verifyOTP($phone, $otp) {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("
        SELECT id, otp_code, expires_at, attempts 
        FROM otp_verifications 
        WHERE phone = ? AND verified = 0 
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        $conn->close();
        return ['success' => false, 'message' => 'Nessun OTP trovato per questo numero'];
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
        
        return ['success' => true, 'message' => 'Telefono verificato con successo'];
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

// Check if phone is verified
function isPhoneVerified($phone) {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM otp_verifications 
        WHERE phone = ? AND verified = 1
    ");
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    $stmt->close();
    $conn->close();
    
    return $row['count'] > 0;
}
?>
