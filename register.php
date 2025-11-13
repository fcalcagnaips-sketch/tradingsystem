<?php
session_start();
require_once __DIR__ . '/includes/otp.php';
require_once __DIR__ . '/config/database.php';

$error = '';
$success = '';
$step = $_SESSION['registration_step'] ?? 1;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Step 1: Send OTP
    if (isset($_POST['action']) && $_POST['action'] === 'send_otp') {
        $phone = trim($_POST['phone'] ?? '');
        
        if (empty($phone)) {
            $error = 'Inserisci un numero di telefono';
        } else {
            $_SESSION['registration_phone'] = $phone;
            $result = createOTP($phone);
            
            if ($result['success']) {
                $success = $result['message'] . ' (OTP: ' . $result['otp'] . ')'; // Remove OTP display in production
                $_SESSION['registration_step'] = 2;
                $step = 2;
            } else {
                $error = $result['message'];
            }
        }
    }
    
    // Step 2: Verify OTP
    elseif (isset($_POST['action']) && $_POST['action'] === 'verify_otp') {
        $otp = trim($_POST['otp'] ?? '');
        $phone = $_SESSION['registration_phone'] ?? '';
        
        if (empty($otp)) {
            $error = 'Inserisci il codice OTP';
        } else {
            $result = verifyOTP($phone, $otp);
            
            if ($result['success']) {
                $_SESSION['phone_verified'] = true;
                $_SESSION['registration_step'] = 3;
                $step = 3;
                $success = $result['message'];
            } else {
                $error = $result['message'];
            }
        }
    }
    
    // Step 3: Complete registration
    elseif (isset($_POST['action']) && $_POST['action'] === 'register') {
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $phone = $_SESSION['registration_phone'] ?? '';
        
        // Validation
        if (empty($firstName) || empty($lastName) || empty($email) || empty($username) || empty($password)) {
            $error = 'Tutti i campi sono obbligatori';
        } elseif ($password !== $confirmPassword) {
            $error = 'Le password non coincidono';
        } elseif (strlen($password) < 6) {
            $error = 'La password deve essere di almeno 6 caratteri';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Email non valida';
        } elseif (!isset($_SESSION['phone_verified']) || !$_SESSION['phone_verified']) {
            $error = 'Devi verificare il numero di telefono';
        } else {
            // Check if username or email already exists
            $conn = getDBConnection();
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->bind_param("ss", $username, $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = 'Username o email già registrati';
                $stmt->close();
                $conn->close();
            } else {
                $stmt->close();
                
                // Create user
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("
                    INSERT INTO users (username, first_name, last_name, email, phone, password, phone_verified, full_name) 
                    VALUES (?, ?, ?, ?, ?, ?, 1, ?)
                ");
                $fullName = $firstName . ' ' . $lastName;
                $stmt->bind_param("sssssss", $username, $firstName, $lastName, $email, $phone, $hashedPassword, $fullName);
                
                if ($stmt->execute()) {
                    $userId = $conn->insert_id;
                    $stmt->close();
                    
                    // Assegna ruolo 'user' al nuovo utente
                    require_once __DIR__ . '/includes/auth.php';
                    assignRole($userId, 'user');
                    
                    $conn->close();
                    
                    // Clear session
                    unset($_SESSION['registration_step']);
                    unset($_SESSION['registration_phone']);
                    unset($_SESSION['phone_verified']);
                    
                    $success = 'Registrazione completata! Ora puoi effettuare il login';
                    header('refresh:2;url=login.php');
                } else {
                    $error = 'Errore durante la registrazione';
                    $stmt->close();
                    $conn->close();
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrazione - Trading AI</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700|Roboto:300,400,500,600,700" />
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
        #kt_register_wrapper {
            height: 100%;
        }
        .register {
            display: flex;
            height: 100%;
            background: #ffffff;
        }
        .register-aside {
            flex: 1;
            background-image: url('./assets/js/media/bg-2.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            position: relative;
            display: none;
        }
        @media (min-width: 992px) {
            .register-aside {
                display: block;
                max-width: 600px;
            }
        }
        .register-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            background: #ffffff;
            position: relative;
            overflow-y: auto;
        }
        .kt-register-body {
            width: 100%;
            max-width: 500px;
        }
        .kt-register-title {
            margin-bottom: 30px;
        }
        .kt-register-title h3 {
            font-size: 28px;
            font-weight: 600;
            color: #181C32;
            margin: 0 0 10px 0;
        }
        .kt-register-title p {
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
        .password-toggle-icon:hover {
            color: #3F4254;
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
        .btn-secondary {
            width: 100%;
            height: 48px;
            background: #E4E6EF;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            color: #3F4254;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-secondary:hover {
            background: #D1D3E0;
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
        .register-footer {
            margin-top: 25px;
            text-align: center;
            font-size: 13px;
            color: #B5B5C3;
        }
        .register-footer a {
            color: #5d78ff;
            text-decoration: none;
            font-weight: 600;
        }
        .register-footer a:hover {
            text-decoration: underline;
        }
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
            gap: 10px;
        }
        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #E4E6EF;
            color: #B5B5C3;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
        }
        .step.active {
            background: #5d78ff;
            color: #ffffff;
        }
        .step.completed {
            background: #1BC5BD;
            color: #ffffff;
        }
        .row-cols-2 > * {
            width: 48%;
        }
        .row-cols-2 {
            display: flex;
            gap: 4%;
        }
    </style>
</head>
<body>
    <div id="kt_register_wrapper">
        <div class="register d-flex flex-lg-row flex-column-fluid bg-white">
            <div class="register-aside"></div>
            
            <div class="register-content">
                <div class="kt-register-body">
                    <div class="kt-register-title">
                        <h3>Crea il tuo account</h3>
                        <p>Registrati per accedere a Trading AI</p>
                    </div>

                    <!-- Step Indicator -->
                    <div class="step-indicator">
                        <div class="step <?php echo $step >= 1 ? ($step > 1 ? 'completed' : 'active') : ''; ?>">1</div>
                        <div class="step <?php echo $step >= 2 ? ($step > 2 ? 'completed' : 'active') : ''; ?>">2</div>
                        <div class="step <?php echo $step >= 3 ? 'active' : ''; ?>">3</div>
                    </div>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>

                    <?php if ($step === 1): ?>
                        <!-- Step 1: Phone Number -->
                        <form method="POST">
                            <input type="hidden" name="action" value="send_otp">
                            <div class="form-group">
                                <label for="phone">Numero di Telefono</label>
                                <input type="tel" 
                                       class="form-control" 
                                       id="phone" 
                                       name="phone" 
                                       placeholder="+39 123 456 7890"
                                       required
                                       autofocus>
                                <small class="text-muted">Inserisci il numero con prefisso internazionale (es. +39 per Italia)</small>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                Invia OTP su Telegram
                            </button>
                        </form>

                    <?php elseif ($step === 2): ?>
                        <!-- Step 2: Verify OTP -->
                        <form method="POST">
                            <input type="hidden" name="action" value="verify_otp">
                            <div class="form-group">
                                <label for="otp">Codice OTP</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="otp" 
                                       name="otp" 
                                       placeholder="Inserisci il codice a 6 cifre"
                                       maxlength="6"
                                       required
                                       autofocus>
                                <small class="text-muted">Controlla Telegram sul numero <?php echo htmlspecialchars($_SESSION['registration_phone'] ?? ''); ?></small>
                            </div>
                            <button type="submit" class="btn btn-primary mb-2">
                                Verifica OTP
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="location.href='?restart=1'">
                                Cambia numero
                            </button>
                        </form>

                    <?php elseif ($step === 3): ?>
                        <!-- Step 3: Complete Registration -->
                        <form method="POST">
                            <input type="hidden" name="action" value="register">
                            
                            <div class="row-cols-2">
                                <div class="form-group">
                                    <label for="first_name">Nome</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="first_name" 
                                           name="first_name" 
                                           placeholder="Nome"
                                           required
                                           autofocus>
                                </div>
                                
                                <div class="form-group">
                                    <label for="last_name">Cognome</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="last_name" 
                                           name="last_name" 
                                           placeholder="Cognome"
                                           required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       placeholder="email@example.com"
                                       required>
                            </div>

                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="username" 
                                       name="username" 
                                       placeholder="Scegli un username"
                                       required>
                            </div>

                            <div class="form-group password-toggle">
                                <label for="password">Password</label>
                                <input type="password" 
                                       class="form-control" 
                                       id="password" 
                                       name="password" 
                                       placeholder="Password (min 6 caratteri)"
                                       required>
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
                                Completa Registrazione
                            </button>
                        </form>
                    <?php endif; ?>

                    <div class="register-footer">
                        <p class="mb-0">Hai già un account? <a href="login.php">Accedi</a></p>
                    </div>
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

        // Restart registration
        <?php if (isset($_GET['restart'])): ?>
            <?php 
            unset($_SESSION['registration_step']);
            unset($_SESSION['registration_phone']);
            unset($_SESSION['phone_verified']);
            header('Location: register.php');
            exit;
            ?>
        <?php endif; ?>
    </script>
</body>
</html>
