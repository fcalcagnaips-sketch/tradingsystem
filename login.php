<?php
require_once __DIR__ . '/includes/auth.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    header('Location: /tradingai/dashboard.php');
    exit;
}

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Username and password are required';
    } else {
        if (loginUser($username, $password)) {
            header('Location: /tradingai/dashboard.php');
            exit;
        } else {
            $error = 'Invalid username or password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trading AI - Sign In</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700|Roboto:300,400,500,600,700" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
        #kt_login_wrapper {
            height: 100%;
        }
        .login {
            display: flex;
            height: 100%;
            background: #ffffff;
        }
        /* Left Side - Background Image */
        .login-aside {
            flex: 1;
            background-image: url('./assets/js/media/bg-2.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            position: relative;
            display: none;
        }
        @media (min-width: 992px) {
            .login-aside {
                display: block;
                max-width: 600px;
            }
        }
        /* Right Side - Login Form */
        .login-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            background: #ffffff;
            position: relative;
        }
        .kt-login-body {
            width: 100%;
            max-width: 450px;
        }
        .kt-login-form {
            width: 100%;
        }
        .kt-login-title {
            margin-bottom: 40px;
        }
        .kt-login-title h3 {
            font-size: 28px;
            font-weight: 600;
            color: #181C32;
            margin: 0;
        }
        .form-group {
            margin-bottom: 25px;
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
        .form-control::placeholder {
            color: #B5B5C3;
        }
        .form-control:focus {
            color: #3F4254;
            background-color: #ffffff;
            border-color: #69b3ff;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0, 158, 247, 0.1);
        }
        .kt-login-actions {
            margin-top: 30px;
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
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .btn-primary:hover {
            background: #4c68e6;
            box-shadow: 0 4px 12px rgba(93, 120, 255, 0.4);
        }
        .btn-primary:active {
            background: #3d58cc;
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
        .login-footer {
            margin-top: 30px;
            text-align: center;
            font-size: 13px;
            color: #B5B5C3;
        }
        .login-footer strong {
            color: #3F4254;
        }
    </style>
</head>
<body>
    <div id="kt_login_wrapper">
        <div class="login login-1 login-signin-on d-flex flex-lg-row flex-column-fluid bg-white">
            <!-- Left Side - Background -->
            <div class="login-aside d-flex flex-row-auto bgi-size-cover bgi-no-repeat p-10 p-lg-10"></div>
            
            <!-- Right Side - Login Form -->
            <div class="login-content flex-row-fluid d-flex flex-column position-relative p-7 overflow-hidden">
                <div class="d-flex flex-column-fluid flex-center mt-30 mt-lg-0">
                    <div class="kt-login-body">
                        <div class="kt-login-form">
                            <div class="kt-login-title">
                                <h3>Trading AI, Sign In</h3>
                            </div>

                            <?php if (!empty($error)): ?>
                                <div class="alert alert-danger" role="alert">
                                    <div class="alert-text"><?php echo htmlspecialchars($error); ?></div>
                                </div>
                            <?php endif; ?>

                            <form class="kt-form" method="POST" action="" autocomplete="off" novalidate>
                                <div class="form-group">
                                    <label for="username">Username</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="username" 
                                           name="username" 
                                           placeholder="Username"
                                           autocomplete="off"
                                           required 
                                           autofocus>
                                </div>

                                <div class="form-group">
                                    <label for="password">Password</label>
                                    <input type="password" 
                                           class="form-control" 
                                           id="password" 
                                           name="password" 
                                           placeholder="Password"
                                           autocomplete="off"
                                           required>
                                </div>

                                <div class="kt-login-actions">
                                    <button type="submit" class="btn btn-primary btn-elevate kt-login__btn-primary">
                                        Sign In
                                    </button>
                                </div>
                            </form>

                            <div class="login-footer">
                                <p class="mb-0">Default credentials: <strong>admin</strong> / <strong>admin123</strong></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
