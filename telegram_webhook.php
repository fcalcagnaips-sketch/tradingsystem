<?php
/**
 * Webhook Telegram Bot
 * Gestisce i messaggi ricevuti dal bot e collega i numeri di telefono ai chat_id
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/telegram.php';

// Leggi il payload dal webhook
$content = file_get_contents("php://input");
$update = json_decode($content, true);

// Log per debugging
file_put_contents(__DIR__ . '/telegram_log.txt', date('Y-m-d H:i:s') . ": " . $content . "\n", FILE_APPEND);

if (!$update) {
    http_response_code(200);
    exit;
}

// Estrai informazioni
$message = $update['message'] ?? null;
if (!$message) {
    http_response_code(200);
    exit;
}

$chatId = $message['chat']['id'];
$text = $message['text'] ?? '';
$contact = $message['contact'] ?? null;

// Funzione per inviare messaggi
function sendTelegramMessage($chatId, $text, $keyboard = null) {
    $url = TELEGRAM_API_URL . '/sendMessage';
    $data = [
        'chat_id' => $chatId,
        'text' => $text,
        'parse_mode' => 'Markdown'
    ];
    
    if ($keyboard) {
        $data['reply_markup'] = json_encode($keyboard);
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return $response;
}

// Gestisci il comando /start
if ($text === '/start') {
    $keyboard = [
        'keyboard' => [
            [
                ['text' => '📱 Condividi Numero', 'request_contact' => true]
            ]
        ],
        'resize_keyboard' => true,
        'one_time_keyboard' => true
    ];
    
    $welcomeMessage = "👋 *Benvenuto su Trading AI Bot!*\n\n";
    $welcomeMessage .= "Per ricevere i codici OTP durante la registrazione, devi collegare il tuo numero di telefono.\n\n";
    $welcomeMessage .= "Premi il pulsante qui sotto per condividere il tuo numero 👇";
    
    sendTelegramMessage($chatId, $welcomeMessage, $keyboard);
    http_response_code(200);
    exit;
}

// Gestisci la condivisione del contatto
if ($contact) {
    $phone = $contact['phone_number'];
    
    // Normalizza il numero (aggiungi + se manca)
    if (!str_starts_with($phone, '+')) {
        $phone = '+' . $phone;
    }
    
    $conn = getDBConnection();
    
    // Controlla se esiste già un utente con questo numero
    $stmt = $conn->prepare("SELECT id FROM users WHERE phone = ?");
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Aggiorna telegram_id
        $stmt->close();
        $stmt = $conn->prepare("UPDATE users SET telegram_id = ? WHERE phone = ?");
        $stmt->bind_param("ss", $chatId, $phone);
        
        if ($stmt->execute()) {
            sendTelegramMessage($chatId, "✅ *Perfetto!*\n\nIl tuo numero *$phone* è stato collegato.\n\nOra puoi ricevere i codici OTP durante la registrazione su Trading AI.");
        } else {
            sendTelegramMessage($chatId, "❌ Errore nel collegamento. Riprova più tardi.");
        }
    } else {
        // Salva il mapping per nuovi utenti (verrà usato durante la registrazione)
        $stmt->close();
        $stmt = $conn->prepare("INSERT INTO otp_verifications (phone, otp_code, expires_at, verified) VALUES (?, 'TELEGRAM_LINK', NOW(), 1)");
        $stmt->bind_param("s", $phone);
        
        if ($stmt->execute()) {
            // Salva anche una associazione temporanea
            $stmt->close();
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, phone, telegram_id, full_name, active) VALUES (?, ?, ?, ?, ?, ?, 0) ON DUPLICATE KEY UPDATE telegram_id = ?");
            $tempUsername = 'temp_' . $chatId;
            $tempEmail = 'temp_' . $chatId . '@telegram.tmp';
            $tempPass = password_hash(uniqid(), PASSWORD_DEFAULT);
            $fullName = 'Telegram User ' . $chatId;
            
            $stmt->bind_param("sssssss", $tempUsername, $tempEmail, $tempPass, $phone, $chatId, $fullName, $chatId);
            $stmt->execute();
            
            sendTelegramMessage($chatId, "✅ *Collegamento riuscito!*\n\nIl tuo numero *$phone* è stato registrato.\n\nQuando ti registrerai su Trading AI, riceverai i codici OTP qui su Telegram! 🎉");
        } else {
            sendTelegramMessage($chatId, "❌ Errore nel collegamento. Riprova più tardi.");
        }
    }
    
    $stmt->close();
    $conn->close();
    
    http_response_code(200);
    exit;
}

// Risposta di default
if ($text) {
    $helpMessage = "ℹ️ *Comandi disponibili:*\n\n";
    $helpMessage .= "/start - Collega il tuo numero\n\n";
    $helpMessage .= "Per ricevere i codici OTP, devi prima collegare il tuo numero di telefono usando il comando /start";
    
    sendTelegramMessage($chatId, $helpMessage);
}

http_response_code(200);
?>
