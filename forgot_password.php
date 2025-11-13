<?php
session_start();
require_once __DIR__ . '/includes/gmail_otp.php';
require_once __DIR__ . '/config/database.php';

$error = '';
$success = '';
$step = $_GET['step'] ?? 1;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Step 1: Send reset email
    if (isset($_POST['action']) && $_POST['action'] === 'send_reset') {
        $email = trim($_POST['email'] ?? '');
        
        if (empty($email)) {
            $error = 'Inserisci un indirizzo email';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Email non valida';
        } else {
            // Check if email exists
            $conn = getDBConnection();
            $stmt = $conn->prepare("SELECT id, username FROM users WHERE email = ? AND active = 1");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                $error = 'Email non trovata nel sistema';
                $stmt->close();
                $conn->close();
            } else {
                $user = $result->fetch_assoc();
                $stmt->close();
                
                // Generate reset token
                $token = bin2hex(random_bytes(32));
                $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Save token to database
                $stmt = $conn->prepare("
                    INSERT INTO password_resets (user_id, token, expires_at) 
                    VALUES (?, ?, ?)
                    ON DUPLICATE KEY UPDATE token = ?, expires_at = ?
                ");
                $stmt->bind_param("issss", $user['id'], $token, $expiresAt, $token, $expiresAt);
                $stmt->execute();
                $stmt->close();
                $conn->close();
                
                // Send reset email
                $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/tradingai/forgot_password.php?step=2&token=" . $token;
                $sent = sendPasswordResetEmail($email, $user['username'], $resetLink);
                
                if ($sent) {
                    $success = 'Email di recupero inviata! Controlla la tua casella email.';
                    header('refresh:3;url=login.php');
                } else {
                    $error = 'Errore nell\'invio dell\'email. Riprova.';
                }
            }
        }
    }
    
    // Step 2: Reset password
    elseif (isset($_POST['action']) && $_POST['action'] === 'reset_password') {
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($password) || empty($confirmPassword)) {
            $error = 'Inserisci la nuova password';
        } elseif ($password !== $confirmPassword) {
            $error = 'Le password non coincidono';
        } elseif (strlen($password) < 6) {
            $error = 'La password deve essere di almeno 6 caratteri';
        } else {
            // Verify token
            $conn = getDBConnection();
            $stmt = $conn->prepare("
                SELECT user_id 
                FROM password_resets 
                WHERE token = ? AND expires_at > NOW() AND used = 0
            ");
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                $error = 'Link non valido o scaduto';
                $stmt->close();
                $conn->close();
            } else {
                $row = $result->fetch_assoc();
                $userId = $row['user_id'];
                $stmt->close();
                
                // Update password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $hashedPassword, $userId);
                $stmt->execute();
                $stmt->close();
                
                // Mark token as used
                $stmt = $conn->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
                $stmt->bind_param("s", $token);
                $stmt->execute();
                $stmt->close();
                
                $conn->close();
                
                $success = 'Password aggiornata con successo! Ora puoi effettuare il login.';
                header('refresh:2;url=login.php');
            }
        }
    }
}

// Send password reset email
function sendPasswordResetEmail($email, $username, $resetLink) {
    $subject = 'Recupero Password - Trading AI';
    
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
            .button { display: inline-block; padding: 15px 30px; background: #009ef7; color: white; text-decoration: none; border-radius: 6px; font-weight: bold; margin: 20px 0; }
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
                    <h2 style='color: #181c32; margin-top: 0;'>Recupero Password</h2>
                    <p style='color: #7e8299; font-size: 16px;'>Ciao <strong>$username</strong>,</p>
                    <p style='color: #7e8299; font-size: 16px;'>Hai richiesto il recupero della password per il tuo account Trading AI.</p>
                    <p style='color: #7e8299; font-size: 16px;'>Clicca sul pulsante qui sotto per reimpostare la tua password:</p>
                    <div style='text-align: center;'>
                        <a href='$resetLink' class='button'>Reimposta Password</a>
                    </div>
                    <p style='color: #7e8299; font-size: 14px; margin-top: 20px;'>
                        ⏰ Questo link è valido per <strong>1 ora</strong>.<br>
                        ⚠️ Se non hai richiesto il recupero password, ignora questa email.
                    </p>
                    <p style='color: #b5b5c3; font-size: 12px; margin-top: 20px;'>
                        Se il pulsante non funziona, copia e incolla questo link nel browser:<br>
                        <a href='$resetLink' style='color: #009ef7; word-break: break-all;'>$resetLink</a>
                    </p>
                </div>
                <p class='footer'>
                    Se non hai richiesto questo recupero password, ignora questa email.
                </p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return sendEmailViaSMTP($email, $subject, $message);
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recupero Password - Trading AI</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            height: 100vh;
            overflow: hidden;
        }
        .forgot-password-wrapper {
            height: 100%;
        }
        .forgot-password {
            display: flex;
            height: 100%;
            background: #ffffff;
        }
        .forgot-password-aside {
            flex: 1;
            background-image: url('./assets/js/media/bg-2.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            position: relative;
            display: none;
        }
        @media (min-width: 992px) {
            .forgot-password-aside {
                display: block;
                max-width: 600px;
            }
        }
        .forgot-password-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            background: #ffffff;
        }
        .forgot-password-body {
            width: 100%;
            max-width: 450px;
        }
        .forgot-password-title {
            margin-bottom: 30px;
        }
        .forgot-password-title h3 {
            font-size: 28px;
            font-weight: 600;
            color: #181C32;
            margin: 0 0 10px 0;
        }
        .forgot-password-title p {
            color: #B5B5C3;
            font-size: 14px;
            margin: 0;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            font-size: 13px;
            font-weight: 500;
            color: #181C32;
            margin-bottom: 8px;
            display: block;
        }
        .form-control {
            height: 48px;
            padding: 12px 16px;
            font-size: 14px;
            font-weight: 400;
            line-height: 1.5;
            color: #3F4254;
            background-color: #ffffff;
            border: 1px solid #E4E6EF;
            border-radius: 6px;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
            width: 100%;
        }
        .form-control:focus {
            color: #3F4254;
            background-color: #ffffff;
            border-color: #69b3ff;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0, 158, 247, 0.1);
        }
        .password-toggle {
            position: relative;
        }
        .password-toggle input {
            padding-right: 45px;
        }
        .password-toggle-icon {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #B5B5C3;
            font-size: 18px;
        }
        .btn-primary {
            width: 100%;
            height: 48px;
            background: #5d78ff;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            color: #ffffff;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-primary:hover {
            background: #4c68e6;
            box-shadow: 0 4px 12px rgba(93, 120, 255, 0.4);
        }
        .alert {
            padding: 12px 16px;
            margin-bottom: 20px;
            border-radius: 6px;
            font-size: 13px;
        }
        .alert-danger {
            background-color: #FFE2E5;
            border: 1px solid #F64E60;
            color: #F64E60;
        }
        .alert-success {
            background-color: #C9F7F5;
            border: 1px solid #1BC5BD;
            color: #1BC5BD;
        }
        .forgot-password-footer {
            margin-top: 25px;
            text-align: center;
            font-size: 13px;
            color: #B5B5C3;
        }
        .forgot-password-footer a {
            color: #5d78ff;
            text-decoration: none;
            font-weight: 600;
        }
        .forgot-password-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="forgot-password-wrapper">
        <div class="forgot-password">
            <div class="forgot-password-aside"></div>
            
            <div class="forgot-password-content">
                <div class="forgot-password-body">
                    <?php if ($step == 1): ?>
                        <div class="forgot-password-title">
                            <h3>Password Dimenticata?</h3>
                            <p>Inserisci la tua email per ricevere il link di recupero</p>
                        </div>

                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>

                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <input type="hidden" name="action" value="send_reset">
                            
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       placeholder="email@example.com"
                                       required
                                       autofocus>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                Invia Link di Recupero
                            </button>
                        </form>

                        <div class="forgot-password-footer">
                            <p class="mb-0"><a href="login.php"><i class="bi bi-arrow-left me-2"></i>Torna al Login</a></p>
                        </div>

                    <?php elseif ($step == 2): ?>
                        <?php
                        $token = $_GET['token'] ?? '';
                        
                        // Verify token validity
                        $conn = getDBConnection();
                        $stmt = $conn->prepare("
                            SELECT u.username 
                            FROM password_resets pr
                            JOIN users u ON pr.user_id = u.id
                            WHERE pr.token = ? AND pr.expires_at > NOW() AND pr.used = 0
                        ");
                        $stmt->bind_param("s", $token);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        
                        if ($result->num_rows === 0) {
                            echo '<div class="alert alert-danger">Link non valido o scaduto. <a href="forgot_password.php">Richiedi un nuovo link</a></div>';
                            $stmt->close();
                            $conn->close();
                        } else {
                            $user = $result->fetch_assoc();
                            $stmt->close();
                            $conn->close();
                        ?>
                        
                        <div class="forgot-password-title">
                            <h3>Reimposta Password</h3>
                            <p>Inserisci la tua nuova password</p>
                        </div>

                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>

                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <input type="hidden" name="action" value="reset_password">
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                            
                            <div class="form-group password-toggle">
                                <label for="password">Nuova Password</label>
                                <input type="password" 
                                       class="form-control" 
                                       id="password" 
                                       name="password" 
                                       placeholder="Password (min 6 caratteri)"
                                       required
                                       autofocus>
                                <i class="bi bi-eye password-toggle-icon" id="togglePassword"></i>
                            </div>

                            <div class="form-group">
                                <label for="confirm_password">Conferma Password</label>
                                <input type="password" 
                                       class="form-control" 
                                       id="confirm_password" 
                                       name="confirm_password" 
                                       placeholder="Ripeti la password"
                                       required>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                Reimposta Password
                            </button>
                        </form>
                        
                        <?php } ?>

                        <div class="forgot-password-footer">
                            <p class="mb-0"><a href="login.php"><i class="bi bi-arrow-left me-2"></i>Torna al Login</a></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Password toggle
        const togglePassword = document.getElementById('togglePassword');
        if (togglePassword) {
            togglePassword.addEventListener('click', function() {
                const password = document.getElementById('password');
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                this.classList.toggle('bi-eye');
                this.classList.toggle('bi-eye-slash');
            });
        }

        // Password confirmation validation
        const form = document.querySelector('form');
        if (form && form.querySelector('#confirm_password')) {
            form.addEventListener('submit', function(e) {
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('confirm_password').value;
                
                if (password !== confirmPassword) {
                    e.preventDefault();
                    alert('Le password non coincidono!');
                    return false;
                }
            });
        }
    </script>
</body>
</html>
