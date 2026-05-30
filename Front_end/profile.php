<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['token'])) { header("Location: login.php"); exit; }

$user_id = $_SESSION['user_id'];
$token = $_SESSION['token']; 
$api_url_user = "http://localhost:3000/api/users/" . $user_id;

// 1. JIKA ADA POST (Proses Update Data)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $data = [
        "name" => $_POST['name'],
        "age" => !empty($_POST['age']) ? (int)$_POST['age'] : null,
        "height_cm" => !empty($_POST['height_cm']) ? (float)$_POST['height_cm'] : null,
        "weight_kg" => !empty($_POST['weight_kg']) ? (float)$_POST['weight_kg'] : null
    ];

    $ch = curl_init($api_url_user);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Bearer ' . $token]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $response = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_status === 200) {
        $_SESSION['user_name'] = $_POST['name'];
        $_SESSION['age'] = $_POST['age'];
        $_SESSION['weight_kg'] = $_POST['weight_kg'];
        
        $_SESSION['sweetalert'] = ['icon' => 'success', 'title' => 'Tersimpan!', 'text' => 'Profil Anda berhasil diperbarui.'];
        header("Location: profile.php");
        exit;
    } else {
        $error_data = json_decode($response, true);
        // Jika API membalas error, tampilkan. Jika membalas HTML (404), tampilkan status kodenya
        $pesan_error = isset($error_data['error']) ? $error_data['error'] : "Gagal memperbarui profil (Status Server: $http_status). Pastikan rute API sudah benar.";
        $_SESSION['sweetalert'] = ['icon' => 'error', 'title' => 'Gagal', 'text' => $pesan_error];
    }
}

// 2. AMBIL DATA USER TERBARU DARI DATABASE
$ch = curl_init($api_url_user);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token]);
$response = curl_exec($ch);
$http_status_get = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$user_data = [];
$peringatan_koneksi = "";
if ($http_status_get === 200 && $response) {
    $user_data = json_decode($response, true);
} else {
    $peringatan_koneksi = "Gagal mengambil data dari server (Kode: $http_status_get). Pastikan server Node.js menyala.";
}

// Sederhanakan nama panggilan
$display_name = $_SESSION['user_name'];
if (strpos($display_name, '@') !== false) {
    $display_name = explode('@', $display_name)[0];
}
$display_name = ucfirst(strtolower($display_name));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Akun - FitTrack Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root { --bg-main: #121212; --bg-card: #1e1e24; --bg-sidebar: #17171d; --text-main: #e0e0e0; --text-muted: #8b8b93; --accent-purple: #8352FD; --border-color: #2c2c35; }
        body { background-color: var(--bg-main); color: var(--text-main); font-family: 'Segoe UI', Tahoma, sans-serif; overflow-x: hidden; }
        .wrapper { display: flex; min-height: 100vh; }
        .sidebar { width: 260px; min-width: 260px; flex-shrink: 0; background-color: var(--bg-sidebar); border-right: 1px solid var(--border-color); display: flex; flex-direction: column; padding: 1.5rem; }
        .brand-logo { color: white; font-weight: bold; font-size: 1.2rem; margin-bottom: 2rem; display: flex; align-items: center; gap: 10px; }
        .sidebar-btn { background-color: transparent; color: var(--text-main); border: 1px solid var(--border-color); padding: 10px 15px; border-radius: 8px; margin-bottom: 10px; text-align: left; text-decoration: none; display: flex; align-items: center; gap: 10px;}
        .sidebar-btn:hover { background-color: var(--bg-card); color: white; }
        .main-content { flex-grow: 1; padding: 2rem; height: 100vh; overflow-y: auto; }
        .dark-card { background-color: var(--bg-card); border-radius: 12px; padding: 2rem; border: 1px solid var(--border-color); margin-bottom: 1.5rem; }
        .avatar-large { width: 100px; height: 100px; background: linear-gradient(135deg, var(--accent-purple), #4a90e2); border-radius: 50%; display: flex; justify-content: center; align-items: center; font-size: 2.5rem; font-weight: bold; margin: 0 auto 1.5rem auto; box-shadow: 0 10px 20px rgba(131, 82, 253, 0.3); }
        .form-control-dark { background-color: var(--bg-main); border: 1px solid var(--border-color); color: white !important; }
        .form-control-dark:focus { background-color: var(--bg-main); color: white; border-color: var(--accent-purple); box-shadow: 0 0 0 0.25rem rgba(131, 82, 253, 0.25); }
        .form-control-dark:disabled { background-color: rgba(255,255,255,0.05); color: var(--text-muted) !important; cursor: not-allowed; }
        .input-label { font-size: 0.85rem; color: #cccccc; margin-bottom: 5px; font-weight: 500; }
        .btn-custom { background-color: var(--accent-purple); color: white; font-weight: bold; border-radius: 8px; padding: 10px 20px; transition: 0.2s; border: none; }
        .btn-custom:hover { background-color: #7040e5; }
    </style>
</head>
<body>
<div class="wrapper">
    <aside class="sidebar">
        <div class="brand-logo"><i class="fa-solid fa-bolt text-primary"></i> FitTrack Pro</div>
        <a href="dashboard.php" class="sidebar-btn"><i class="fa-solid fa-arrow-left text-info"></i> Kembali ke Dashboard</a>
    </aside>

    <main class="main-content">
        <header class="mb-4">
            <h4 class="fw-bold mb-1">Pengaturan Akun</h4>
            <div class="text-muted small">Kelola informasi pribadi dan metrik tubuh Anda di sini.</div>
        </header>

        <?php if(!empty($peringatan_koneksi)): ?>
            <div class="alert alert-danger" style="background-color: rgba(248, 79, 119, 0.1); color: #F84F77; border-color: rgba(248, 79, 119, 0.2);">
                <i class="fa-solid fa-triangle-exclamation me-2"></i><?= $peringatan_koneksi ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-4">
                <div class="dark-card text-center">
                    <div class="avatar-large"><?= strtoupper(substr($display_name, 0, 1)) ?></div>
                    <h5 class="fw-bold text-white mb-1"><?= htmlspecialchars($user_data['name'] ?? $display_name) ?></h5>
                    <p class="text-muted small mb-4"><?= htmlspecialchars($user_data['email'] ?? 'Email tidak ditemukan') ?></p>
                    
                    <div class="d-flex justify-content-around border-top pt-4" style="border-color: var(--border-color) !important;">
                        <div>
                            <div class="text-white fw-bold fs-5"><?= htmlspecialchars($user_data['age'] ?? '-') ?></div>
                            <div class="text-muted small">Tahun</div>
                        </div>
                        <div>
                            <div class="text-white fw-bold fs-5"><?= htmlspecialchars($user_data['weight_kg'] ?? '-') ?></div>
                            <div class="text-muted small">Kg</div>
                        </div>
                        <div>
                            <div class="text-white fw-bold fs-5"><?= htmlspecialchars($user_data['height_cm'] ?? '-') ?></div>
                            <div class="text-muted small">Cm</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="dark-card">
                    <h6 class="fw-bold text-white mb-4"><i class="fa-solid fa-pen-to-square text-primary me-2"></i>Edit Informasi Profil</h6>
                    
                    <form method="POST">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="input-label">Nama Lengkap</label>
                                <input type="text" name="name" class="form-control form-control-dark" value="<?= htmlspecialchars($user_data['name'] ?? $display_name) ?>" required>
                            </div>
                            <div class="col-md-6 mt-3 mt-md-0">
                                <label class="input-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control form-control-dark" value="<?= htmlspecialchars($user_data['email'] ?? '') ?>" disabled title="Email tidak dapat diubah">
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4 mt-3 mt-md-0">
                                <label class="input-label">Umur (Tahun)</label>
                                <input type="number" name="age" class="form-control form-control-dark" value="<?= htmlspecialchars($user_data['age'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-4 mt-3 mt-md-0">
                                <label class="input-label">Tinggi Badan (cm)</label>
                                <input type="number" step="0.1" name="height_cm" class="form-control form-control-dark" value="<?= htmlspecialchars($user_data['height_cm'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-4 mt-3 mt-md-0">
                                <label class="input-label">Berat Badan (kg)</label>
                                <input type="number" step="0.1" name="weight_kg" class="form-control form-control-dark" value="<?= htmlspecialchars($user_data['weight_kg'] ?? '') ?>" required>
                            </div>
                        </div>

                        <div class="text-end border-top pt-3" style="border-color: var(--border-color) !important;">
                            <button type="submit" class="btn btn-custom"><i class="fa-solid fa-floppy-disk me-2"></i>Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

<?php if (isset($_SESSION['sweetalert'])): ?>
    <script>
        Swal.fire({
            icon: '<?= $_SESSION['sweetalert']['icon'] ?>',
            title: '<?= $_SESSION['sweetalert']['title'] ?>',
            text: '<?= $_SESSION['sweetalert']['text'] ?>',
            background: '#1e1e24', color: '#fff', confirmButtonColor: '#8352FD'
        });
    </script>
    <?php unset($_SESSION['sweetalert']); ?>
<?php endif; ?>
</body>
</html>