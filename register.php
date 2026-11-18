<?php
session_start();
require 'koneksi.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
$sukses = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $fotoPng  = $_POST['foto_png'] ?? '';

    if (empty($nama) || empty($email) || empty($password)) {
        $error = "Semua kolom wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter.";
    } elseif (empty($fotoPng)) {
        $error = "Silakan ambil atau pilih foto terlebih dahulu.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $error = "Email sudah terdaftar.";
        } else {
            // Validasi & simpan data Base64 langsung menjadi file .png
            if (preg_match('/^data:image\/png;base64,/', $fotoPng)) {
                $base64Data = substr($fotoPng, strpos($fotoPng, ',') + 1);
                $binaryData = base64_decode($base64Data);

                if ($binaryData !== false) {
                    $uploadDir = __DIR__ . '/uploads';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }

                    $namaFile = 'foto_' . uniqid() . '.png';
                    file_put_contents($uploadDir . '/' . $namaFile, $binaryData);

                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $insert = $pdo->prepare("INSERT INTO users (nama, email, password, foto) VALUES (?, ?, ?, ?)");
                    $insert->execute([$nama, $email, $hashedPassword, $namaFile]);

                    $sukses = "Registrasi berhasil! Foto tersimpan sebagai <b>$namaFile</b>. Silakan <a href='login.php'>login di sini</a>.";
                } else {
                    $error = "Gagal memproses file gambar PNG.";
                }
            } else {
                $error = "Format foto harus berupa PNG.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun</title>
    <style>
        * { box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { background: #f1f5f9; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; padding: 1rem; }
        .card { background: #fff; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); width: 100%; max-width: 400px; }
        h2 { margin-top: 0; color: #1e293b; text-align: center; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.4rem; color: #475569; font-size: 0.9rem; }
        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%; padding: 0.7rem; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 1rem;
        }
        input:focus { outline: none; border-color: #2563eb; }

        /* Bar khusus untuk tombol foto agar seragam dengan input lain */
        .photo-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            width: 100%;
            padding: 0.45rem 0.6rem;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #fff;
        }
        .photo-status {
            font-size: 0.88rem;
            color: #94a3b8;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .photo-status.ready {
            color: #16a34a;
            font-weight: 600;
        }
        .photo-actions {
            display: flex;
            gap: 6px;
            flex-shrink: 0;
        }
        .btn-mini {
            width: auto;
            padding: 0.4rem 0.7rem;
            font-size: 0.82rem;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-weight: 600;
        }
        .btn-cam { background: #0f172a; color: #fff; }
        .btn-cam:hover { background: #1e293b; }
        .btn-capture { background: #16a34a; color: #fff; }
        .btn-file { background: #e2e8f0; color: #334155; }
        .btn-file:hover { background: #cbd5e1; }

        .media-box {
            margin-top: 0.6rem;
            text-align: center;
            display: none;
        }
        .media-box video, .media-box img {
            width: 100%;
            max-height: 200px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
        }

        .btn-submit {
            width: 100%; padding: 0.75rem; background: #2563eb; color: #fff;
            border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; margin-top: 0.25rem;
        }
        .btn-submit:hover { background: #1d4ed8; }
        .alert { padding: 0.75rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.9rem; }
        .error { background: #fee2e2; color: #991b1b; }
        .success { background: #dcfce7; color: #166534; }
        .link { text-align: center; margin-top: 1rem; font-size: 0.9rem; color: #64748b; }
        .link a { color: #2563eb; text-decoration: none; }
    </style>
</head>
<body>
<div class="card">
    <h2>Buat Akun Baru</h2>
    <?php if ($error): ?><div class="alert error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($sukses): ?><div class="alert success"><?= $sukses ?></div><?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Nama Lengkap</label>
            <input type="text" name="nama" required placeholder="Masukkan nama">
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required placeholder="nama@email.com">
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required placeholder="Minimal 6 karakter">
        </div>

        <!-- Bar Tambahan untuk Foto Langsung Jadi PNG -->
        <div class="form-group">
            <label>Foto Profil (.PNG)</label>
            <div class="photo-bar">
                <span id="photoStatus" class="photo-status">Belum ada foto</span>
                <div class="photo-actions">
                    <button type="button" id="btnCam" class="btn-mini btn-cam">Ambil Foto</button>
                </div>
            </div>

            <!-- Input file tersembunyi & input Base64 PNG -->
            <input type="file" id="fileInput" accept="image/*" style="display: none;">
            <input type="hidden" name="foto_png" id="fotoPng">

            <!-- Area Pratinjau Kamera / Hasil PNG -->
            <div id="mediaBox" class="media-box">
                <video id="videoPreview" autoplay muted playsinline style="display: none;"></video>
                <img id="imgPreview" alt="Preview PNG" style="display: none;">
            </div>
        </div>

        <button type="submit" class="btn-submit">Daftar</button>
    </form>
    <div class="link">Sudah punya akun? <a href="login.php">Login</a></div>
</div>

<canvas id="convertCanvas" style="display: none;"></canvas>

<script>
const btnCam       = document.getElementById('btnCam');
const btnFile      = document.getElementById('btnFile');
const fileInput    = document.getElementById('fileInput');
const fotoPngInput = document.getElementById('fotoPng');
const photoStatus  = document.getElementById('photoStatus');
const mediaBox     = document.getElementById('mediaBox');
const videoPreview = document.getElementById('videoPreview');
const imgPreview   = document.getElementById('imgPreview');
const canvas       = document.getElementById('convertCanvas');

let activeStream = null;
let isCameraOpen = false;

function stopCamera() {
    if (activeStream) {
        activeStream.getTracks().forEach(track => track.stop());
        activeStream = null;
    }
    videoPreview.style.display = 'none';
    isCameraOpen = false;
    btnCam.textContent = 'Ambil Foto';
    btnCam.classList.remove('btn-capture');
    btnCam.classList.add('btn-cam');
}

function setResultPng(dataUrlPng) {
    fotoPngInput.value = dataUrlPng;
    imgPreview.src = dataUrlPng;
    imgPreview.style.display = 'block';
    mediaBox.style.display = 'block';
    photoStatus.textContent = 'foto_siap.png';
    photoStatus.classList.add('ready');
}

// 1. Ambil foto langsung dari kamera -> konversi ke PNG
btnCam.addEventListener('click', async () => {
    if (!isCameraOpen) {
        try {
            activeStream = await navigator.mediaDevices.getUserMedia({ video: true });
            videoPreview.srcObject = activeStream;
            imgPreview.style.display = 'none';
            videoPreview.style.display = 'block';
            mediaBox.style.display = 'block';

            isCameraOpen = true;
            btnCam.textContent = 'Jepret PNG';
            btnCam.classList.remove('btn-cam');
            btnCam.classList.add('btn-capture');
            photoStatus.textContent = 'Kamera aktif...';
            photoStatus.classList.remove('ready');
        } catch (err) {
            alert('Tidak dapat mengakses kamera: ' + err.message);
        }
    } else {
        // Tangkap frame video saat ini dan jadikan PNG
        canvas.width = videoPreview.videoWidth || 640;
        canvas.height = videoPreview.videoHeight || 480;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(videoPreview, 0, 0, canvas.width, canvas.height);

        const pngDataUrl = canvas.toDataURL('image/png');
        stopCamera();
        setResultPng(pngDataUrl);
    }
});

// 2. Pilih file gambar (JPG/WEBP/PNG) -> otomatis konversi ke PNG
btnFile.addEventListener('click', () => {
    stopCamera();
    fileInput.click();
});

fileInput.addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = (event) => {
        const img = new Image();
        img.onload = () => {
            canvas.width = img.width;
            canvas.height = img.height;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0);

            const pngDataUrl = canvas.toDataURL('image/png');
            setResultPng(pngDataUrl);
        };
        img.src = event.target.result;
    };
    reader.readAsDataURL(file);
});
</script>
</body>
</html>