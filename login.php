<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

// Redirect if already logged in
if (is_logged_in()) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        $stmt = $conn->prepare("SELECT id, username, password, full_name, role, status FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            if ($user['status'] === 'disabled') {
                $error = "Your account has been disabled. Please contact the Super Admin.";
            } elseif (password_verify($password, $user['password'])) {
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];

                // Update last login
                $conn->query("UPDATE users SET last_login = NOW() WHERE id = " . $user['id']);

                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "User not found.";
        }
    } else {
        $error = "Please enter both username and password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | CertGen</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-light: #818cf8;
            --dark: #0f172a;
            --glass: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: radial-gradient(circle at top right, #1e1b4b, #0f172a);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            overflow: hidden;
            color: white;
        }

        /* Animated background circles */
        .circle {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            z-index: -1;
            opacity: 0.5;
        }

        .c1 {
            width: 400px;
            height: 400px;
            background: #4f46e5;
            top: -100px;
            right: -100px;
            animation: float 10s infinite alternate;
        }

        .c2 {
            width: 300px;
            height: 300px;
            background: #7c3aed;
            bottom: -50px;
            left: -50px;
            animation: float 12s infinite alternate-reverse;
        }

        @keyframes float {
            0% {
                transform: translate(0, 0);
            }

            100% {
                transform: translate(50px, 50px);
            }
        }

        .login-card {
            background: var(--glass);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            padding: 3rem;
            border-radius: 2rem;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo-section {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .logo-circle {
            width: 120px;
            height: 120px;
            background: transparent;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            border: 2px solid rgba(255, 255, 255, 0.15);
            transition: transform 0.3s ease;
        }

        .logo-circle:hover {
            transform: scale(1.05);
        }

        .logo-circle img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            filter: drop-shadow(0 0 10px rgba(255, 255, 255, 0.1));
        }

        h1 {
            font-size: 2rem;
            font-weight: 800;
            margin: 0;
            letter-spacing: -1px;
            background: linear-gradient(to bottom, #fff, #94a3b8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        p.subtitle {
            color: #94a3b8;
            font-size: 0.95rem;
            margin-top: 0.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.85rem;
            font-weight: 500;
            color: #cbd5e1;
            margin-left: 0.5rem;
        }

        input {
            width: 100%;
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 1rem 1.25rem;
            border-radius: 1rem;
            color: white;
            font-family: inherit;
            font-size: 1rem;
            box-sizing: border-box;
            transition: all 0.3s;
        }

        input:focus {
            outline: none;
            border-color: var(--primary-light);
            background: rgba(0, 0, 0, 0.4);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }

        .error-box {
            background: rgba(239, 68, 68, 0.1);
            border-left: 4px solid #ef4444;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            color: #fca5a5;
        }

        button {
            width: 100%;
            background: linear-gradient(to right, var(--primary), var(--primary-light));
            color: white;
            border: none;
            padding: 1rem;
            border-radius: 1rem;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 1rem;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.4);
            filter: brightness(1.1);
        }

        button:active {
            transform: translateY(0);
        }

        .footer-text {
            text-align: center;
            margin-top: 2rem;
            font-size: 0.85rem;
            color: #64748b;
        }
    </style>
</head>

<body>
    <div class="circle c1"></div>
    <div class="circle c2"></div>

    <div class="login-card">
        <div class="logo-section">
            <div class="logo-circle">
                <img src="assets/img/OCNHS LOGO.png" alt="OCNHS Logo">
            </div>
            <h1>Welcome</h1>
            <p class="subtitle">Please enter your credentials.</p>
        </div>

        <?php if ($error): ?>
            <div class="error-box"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required autofocus placeholder="Admin identity">
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="••••••••">
            </div>

            <button type="submit">Sign In</button>
        </form>

        <p class="footer-text">&copy; <?php echo date('Y'); ?> OCNHS Certificate Management System</p>
    </div>
</body>

</html>