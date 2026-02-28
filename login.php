<?php
// Ensure session is started if not already in config.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'config.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: /url/dashboard");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // NOTE: Make sure your table name and columns match your actual database!
    // This assumes a table named 'admins' with 'username' and 'password' (hashed) columns.
    $stmt = $pdo->prepare("SELECT id, password FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    // Secure password verification (Assumes you used password_hash() when creating the admin)
    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        header("Location: /url/dashboard");
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Tag.re URL</title>
    <link rel="icon" href="image/tag_re_favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        /* Theme Variables */
        :root {
            --md-bg: #121413;
            --md-surface: #1E221F;
            --md-on-surface: #E8EFEA;
            --md-primary: #ffff00; /* Yellow Accent */
            --md-on-primary: #000000;
            --md-outline: #4A534D;
            --md-error: #FF5A5F;
            --card-border: rgba(255,255,255,0.03);
            --link-secondary: #A0ABA5;
        }

        [data-theme="light"] {
            --md-bg: #F7F9F8;
            --md-surface: #FFFFFF;
            --md-on-surface: #121413;
            --md-primary: #ffff00; 
            --md-on-primary: #000000; 
            --md-outline: #D1D8D4;
            --md-error: #BA1A1A;
            --card-border: rgba(0,0,0,0.08);
            --link-secondary: #5A635D;
        }

        body { 
            padding: 0; margin: 0;
            background-color: var(--md-bg); 
            color: var(--md-on-surface);
            font-family: 'Inter', sans-serif; 
            transition: background-color 0.3s ease, color 0.3s ease;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        h2 { font-weight: 800; letter-spacing: -0.5px; margin-top: 0; text-align: center; }

        .top-nav {
            width: 100%; 
            padding: 15px 30px; box-sizing: border-box;
            display: flex; justify-content: flex-end; align-items: center;
            position: absolute; top: 0;
        }
        
        .btn-theme {
            background: transparent; color: var(--md-on-surface);
            border: 1px solid var(--md-outline); border-radius: 100px;
            padding: 8px 16px; font-size: 0.9rem; font-weight: 600; font-family: 'Inter', sans-serif;
            cursor: pointer; transition: all 0.2s;
        }
        .btn-theme:hover { background-color: var(--md-outline); color: var(--md-bg); }

        .login-wrapper {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .login-card {
            background: var(--md-surface); border-radius: 24px;
            padding: 40px; width: 100%; max-width: 400px; box-sizing: border-box;
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
            border: 1px solid var(--card-border);
            transition: background-color 0.3s ease;
        }
        
        .logo-container { text-align: center; margin-bottom: 30px; }
        .logo-container img { height: 40px; object-fit: contain; }

        .input-group { margin-bottom: 20px; display: flex; flex-direction: column; gap: 8px; }
        .input-group label { font-size: 0.9rem; font-weight: 600; color: var(--link-secondary); }

        input[type="text"], input[type="password"] {
            font-family: 'Inter', sans-serif;
            font-size: 1.1rem; padding: 16px 20px; border-radius: 12px;
            background: var(--md-bg); border: 2px solid var(--md-outline);
            color: var(--md-on-surface);
            transition: border-color 0.3s cubic-bezier(0.2, 0, 0, 1), box-shadow 0.3s, background-color 0.3s ease;
            box-sizing: border-box; width: 100%;
        }
        
        input[type="text"]:focus, input[type="password"]:focus { 
            border-color: var(--md-primary); 
            outline: none; 
            box-shadow: 0 0 0 4px rgba(255, 255, 0, 0.15); 
        }

        .error-message {
            color: var(--md-error);
            background: rgba(255, 90, 95, 0.1);
            padding: 12px; border-radius: 8px;
            font-size: 0.9rem; font-weight: 600; text-align: center;
            margin-bottom: 20px;
        }

        /* General M3 Primary Button */
        .m3-btn {
            font-family: 'Inter', sans-serif; font-size: 1.1rem; font-weight: 600;
            background-color: var(--md-primary); color: var(--md-on-primary);
            border: none; padding: 16px; border-radius: 100px; width: 100%;
            cursor: pointer; position: relative; overflow: hidden; margin-top: 10px;
            transition: transform 0.2s cubic-bezier(0.2, 0, 0, 1), box-shadow 0.2s;
        }
        .m3-btn:active { transform: scale(0.97); } 

        .footer { text-align: center; padding: 20px; color: var(--link-secondary); font-size: 0.9rem; font-weight: 600; }
        
        /* Mobile Optimizations */
        @media (max-width: 600px) {
            .login-card { padding: 30px 20px; }
            .top-nav { padding: 15px 20px; }
        }
    </style>
</head>
<body>

    <div class="top-nav">
        <button id="themeToggle" class="btn-theme">☀️ Light</button>
    </div>

    <div class="login-wrapper">
        <div class="login-card">
            <div class="logo-container">
                <img id="brandLogo" src="image/tag_re_dark_logo.png" alt="Tag.re">
            </div>
            <h2>Welcome Back</h2>

            <?php if ($error): ?>
                <div class="error-message"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="input-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required autofocus>
                </div>
                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="m3-btn">Log In</button>
            </form>
        </div>
    </div>

    <div class="footer">
        Crafted with care from Keralam by Tagrelate 🌴
    </div>

    <script>
        // --- Theme Toggle & Logo Swap Logic ---
        const themeToggle = document.getElementById('themeToggle');
        const brandLogo = document.getElementById('brandLogo');
        
        if (localStorage.getItem('theme') === 'light') {
            document.documentElement.setAttribute('data-theme', 'light');
            themeToggle.innerText = '🌙 Dark';
            brandLogo.src = 'image/tag_re_light_logo.png'; 
        }

        themeToggle.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            if (currentTheme === 'light') {
                document.documentElement.removeAttribute('data-theme');
                localStorage.setItem('theme', 'dark');
                themeToggle.innerText = '☀️ Light';
                brandLogo.src = 'image/tag_re_dark_logo.png'; 
            } else {
                document.documentElement.setAttribute('data-theme', 'light');
                localStorage.setItem('theme', 'light');
                themeToggle.innerText = '🌙 Dark';
                brandLogo.src = 'image/tag_re_light_logo.png'; 
            }
        });
    </script>
</body>
</html>