<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Contoh mengambil total pengguna dari database untuk ditampilkan di kartu statistik
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Utama</title>
    <style>
        * { box-sizing: border-box; font-family: 'Segoe UI', sans-serif; margin: 0; padding: 0; }
        body { display: flex; min-height: 100vh; background: #f8fafc; color: #1e293b; }
        .sidebar { width: 250px; background: #0f172a; color: #fff; padding: 1.5rem; display: flex; flex-direction: column; }
        .sidebar h3 { font-size: 1.3rem; margin-bottom: 2rem; color: #38bdf8; }
        .sidebar a { color: #cbd5e1; text-decoration: none; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 0.5rem; display: block; }
        .sidebar a:hover, .sidebar a.active { background: #1e293b; color: #fff; }
        .sidebar a.logout { margin-top: auto; background: #dc2626; color: #fff; text-align: center; }
        .sidebar a.logout:hover { background: #b91c1c; }
        .main { flex: 1; padding: 2rem; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; background: #fff; padding: 1.2rem 1.5rem; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.04); }
        .badge { background: #dbeafe; color: #1e40af; padding: 0.3rem 0.75rem; border-radius: 999px; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; }
        .stat-card { background: #fff; padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.04); }
        .stat-card p { color: #64748b; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .stat-card h2 { font-size: 2rem; color: #0f172a; }
    </style>
</head>
<body>
    <aside class="sidebar">
        <h3>AdminPanel</h3>
        <a href="dashboard.php" class="active">Dashboard</a>
        <a href="#">Data Pengguna</a>
        <a href="#">Laporan</a>
        <a href="#">Pengaturan</a>
        <a href="logout.php" class="logout">Keluar</a>
    </aside>

    <main class="main">
        <div class="header">
            <div>
                <h2>Selamat datang, <?= htmlspecialchars($_SESSION['user_nama']) ?>!</h2>
                <p style="color: #64748b; font-size: 0.9rem; margin-top: 4px;">Ringkasan aktivitas sistem hari ini.</p>
            </div>
            <span class="badge"><?= htmlspecialchars($_SESSION['user_role']) ?></span>
        </div>

        <div class="grid">
            <div class="stat-card">
                <p>Total Pengguna Terdaftar</p>
                <h2><?= (int)$totalUsers ?></h2>
            </div>
            <div class="stat-card">
                <p>Status Sesi</p>
                <h2 style="color: #16a34a;">Aktif</h2>
            </div>
            <div class="stat-card">
                <p>ID Pengguna Anda</p>
                <h2>#<?= (int)$_SESSION['user_id'] ?></h2>
            </div>
        </div>
    </main>
</body>
</html>