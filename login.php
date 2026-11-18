<?php
session_start();
require 'koneksi.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token    = $_POST['csrf_token'] ?? '';
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $error = "Permintaan tidak valid (CSRF token mismatch).";
    } else {
        $stmt = $pdo->prepare("SELECT id, nama, email, password, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_nama'] = $user['nama'];
            $_SESSION['user_role'] = $user['role'];
            
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Email atau password salah.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Dashboard</title>
    <style>
        * { box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body {
            background-image: url('assets/bg 1.jpeg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .card { background: #fff; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); width: 100%; max-width: 400px; }
        h2 { margin-top: 0; color: #1e293b; text-align: center; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.4rem; color: #475569; font-size: 0.9rem; }
        input { width: 100%; padding: 0.7rem; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 1rem; }
        input:focus { outline: none; border-color: #2563eb; }
        button { width: 100%; padding: 0.75rem; background: #9E1538; color: #fff; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; }
        button:hover { background: #F39C00; }
        .alert { padding: 0.75rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.9rem; background: #fee2e2; color: #991b1b; }
        .link { text-align: center; margin-top: 1rem; font-size: 0.9rem; color: #64748b; }
        .link a { color: #2563eb; text-decoration: none; }

        .brand-logo {
            position: fixed;
            top: 24px;
            left: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: #1e293b;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .brand-logo img {
        height: 64px;
        width: auto;
        }
    </style>
</head>
<body>
    <a href="login.php" class="brand-logo">
        <img src="assets/logo 1.png" alt="Logo Aplikasi">
    </a>
<div class="card">
    <h2>Login</h2>
    <?php if ($error): ?><div class="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required placeholder="nama@email.com">
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required placeholder="Masukkan password">
        </div>
        <button type="submit">Masuk</button>
    </form>
    <div class="link">Belum punya akun? <a href="register.php">Daftar sekarang</a></div>
</div>
</body>
</html>