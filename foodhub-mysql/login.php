<?php
// ============================================================
// LOGIN PAGE - login.php
// Ito ang unang page na makikita ng user.
// Kino-check kung may session na, kung wala, ipapakita ang form.
// ============================================================

session_start();

// Kung naka-login na, i-redirect agad sa main page
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/config/database.php';

$error = '';

// Pag may POST request (nag-submit ng form)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $db   = getDB();
        // Hanapin ang user sa database, active lang
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        // Suriin kung tama ang password gamit ang password_verify
        if ($user && password_verify($password, $user['password'])) {
            // I-update ang last login time
            $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")
               ->execute([$user['id']]);

            // I-save ang user info sa session
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role']      = $user['role'];

            // Redirect sa main page
            header('Location: index.php');
            exit;
        } else {
            $error = 'Mali ang username o password!';
        }
    } else {
        $error = 'Punan ang lahat ng fields!';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Login — Bron Michael's FoodHub</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
<style>
/* Reset at color variables */
*,*::before,*::after { box-sizing: border-box; margin: 0; padding: 0; }
:root {
    --bg: #0d0d0f;
    --bg2: #141416;
    --bg3: #1c1c1f;
    --border: #2a2a30;
    --border2: #3a3a42;
    --text: #f0ede8;
    --text2: #9d9a94;
    --text3: #5a5750;
    --accent: #ff6b35;
    --accent2: #ff8c5a;
    --red: #ef4444;
    --redbg: rgba(239,68,68,.12);
    --font-head: 'Syne', sans-serif;
    --font-body: 'DM Sans', sans-serif;
}

html, body {
    height: 100%;
    background: var(--bg);
    font-family: var(--font-body);
    color: var(--text);
}

/* Centered layout para sa login form */
body {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
}

/* Subtle background glow effect */
.bg-design {
    position: fixed;
    inset: 0;
    z-index: 0;
    background:
        radial-gradient(ellipse at 20% 50%, rgba(255,107,53,.08) 0%, transparent 60%),
        radial-gradient(ellipse at 80% 20%, rgba(255,107,53,.05) 0%, transparent 50%);
}

.login-wrap {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 420px;
    padding: 20px;
}

/* Logo section sa taas ng form */
.login-logo {
    text-align: center;
    margin-bottom: 32px;
}
.login-logo img {
    width: 80px;
    height: 80px;
    border-radius: 16px;
    object-fit: cover;
    margin-bottom: 16px;
}
.login-logo h1 {
    font-family: var(--font-head);
    font-size: 22px;
    font-weight: 800;
}
.login-logo p {
    font-size: 13px;
    color: var(--text3);
    margin-top: 4px;
    text-transform: uppercase;
    letter-spacing: .08em;
}

/* Card na naglalaman ng form */
.login-card {
    background: var(--bg2);
    border: 1px solid var(--border2);
    border-radius: 16px;
    padding: 32px;
    box-shadow: 0 8px 40px rgba(0,0,0,.5);
}

.login-title {
    font-family: var(--font-head);
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 6px;
}
.login-sub {
    font-size: 13px;
    color: var(--text3);
    margin-bottom: 28px;
}

/* Form fields */
.form-group { margin-bottom: 18px; }
.form-group label {
    display: block;
    font-size: 11px;
    font-weight: 600;
    color: var(--text2);
    text-transform: uppercase;
    letter-spacing: .05em;
    margin-bottom: 7px;
}
.form-group input {
    width: 100%;
    background: var(--bg3);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 12px 14px;
    color: var(--text);
    font-family: var(--font-body);
    font-size: 14px;
    outline: none;
    transition: border-color .2s;
}
.form-group input:focus { border-color: var(--accent); }

/* Submit button */
.btn-login {
    width: 100%;
    background: var(--accent);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 13px;
    font-family: var(--font-head);
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    transition: all .2s;
    margin-top: 8px;
}
.btn-login:hover {
    background: var(--accent2);
    transform: translateY(-1px);
}

/* Error message */
.error-msg {
    background: var(--redbg);
    border: 1px solid rgba(239,68,68,.3);
    border-radius: 8px;
    padding: 10px 14px;
    font-size: 13px;
    color: var(--red);
    margin-bottom: 18px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.login-footer {
    text-align: center;
    margin-top: 24px;
    font-size: 12px;
    color: var(--text3);
}
</style>
</head>
<body>
<div class="bg-design"></div>

<div class="login-wrap">
    <!-- Logo at pangalan ng business -->
    <div class="login-logo">
        <img src="logo.png" alt="FoodHub Logo"/>
        <h1>Bron Michael's</h1>
        <p>FoodHub Inventory</p>
    </div>

    <!-- Login form card -->
    <div class="login-card">
        <div class="login-title">Welcome back</div>
        <div class="login-sub">Sign in to your account to continue</div>

        <!-- Ipapakita lang ang error kung may mali -->
        <?php if ($error): ?>
        <div class="error-msg">❌ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username"
                       placeholder="Enter username"
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                       autofocus required/>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password"
                       placeholder="Enter password" required/>
            </div>
            <button type="submit" class="btn-login">Sign In →</button>
        </form>
    </div>

    <div class="login-footer">
        Makipag-ugnayan sa inyong administrator para makakuha ng account.
    </div>
</div>
</body>
</html>
