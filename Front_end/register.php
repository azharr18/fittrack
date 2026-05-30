<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $api_url = "http://localhost:3000/api/register";
    
    $data = [
        "name" => $_POST['name'],
        "email" => $_POST['email'],
        "password" => $_POST['password'],
        "age" => (int)$_POST['age'],
        "height_cm" => (float)$_POST['height_cm'],
        "weight_kg" => (float)$_POST['weight_kg']
    ];

    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $response = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_status === 201 || $http_status === 200) {
        $_SESSION['sweetalert'] = ['icon' => 'success', 'title' => 'Berhasil!', 'text' => 'Akun berhasil dibuat. Silakan login.'];
        header("Location: login.php");
        exit();
    } else {
        $error_data = json_decode($response, true);
        $pesan_error = isset($error_data['error']) ? $error_data['error'] : 'Gagal mendaftar';
        $error_msg = $pesan_error;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Akun - FitTrack Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --bg-main: #121212; --bg-card: #1e1e24; --text-main: #e0e0e0; 
            --accent-purple: #8352FD; --border-color: #2c2c35;
        }
        body { 
            background-color: var(--bg-main); color: var(--text-main); 
            font-family: 'Segoe UI', Tahoma, sans-serif;
            display: flex; align-items: center; justify-content: center; min-height: 100vh;
        }
        .register-card {
            background-color: var(--bg-card); border-radius: 12px; padding: 2.5rem; 
            width: 100%; max-width: 500px; border: 1px solid var(--border-color);
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        .brand-logo-icon {
            background: linear-gradient(135deg, var(--accent-purple), #4a90e2);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .form-control-dark { background-color: var(--bg-main); border: 1px solid var(--border-color); color: white !important; padding-left: 40px; }
        .form-control-dark:focus { background-color: var(--bg-main); color: white; border-color: var(--accent-purple); box-shadow: 0 0 0 0.25rem rgba(131, 82, 253, 0.25); }
        .form-control-dark::placeholder { color: #8b8b93; opacity: 1; }
        
        .input-group-custom { position: relative; margin-bottom: 1.2rem; }
        .input-icon { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #a1a1aa; z-index: 10; }
        
        /* LABEL DIBUAT JADI ABU-ABU TERANG AGAR JELAS */
        .input-label { font-size: 0.85rem; color: #cccccc; margin-bottom: 5px; display: block; font-weight: 500; }
        
        .btn-primary-custom { background-color: var(--accent-purple); border: none; color: white; padding: 12px; font-weight: bold; border-radius: 8px; transition: 0.2s; }
        .btn-primary-custom:hover { background-color: #7040e5; color: white; }
    </style>
</head>
<body>

<div class="register-card">
    <div class="text-center mb-4">
        <i class="fa-solid fa-person-running fa-3x brand-logo-icon mb-3"></i>
        <h3 class="fw-bold text-white">Buat Akun</h3>
        <!-- TEKS DIUBAH MENJADI PUTIH TERANG -->
        <p class="text-light opacity-75 small">Mulai lacak perjalanan olahragamu</p>
    </div>

    <?php if(isset($error_msg)): ?>
        <div class="alert alert-danger" style="background-color: rgba(248, 79, 119, 0.1); color: #F84F77; border-color: rgba(248, 79, 119, 0.2); font-size: 0.9rem;">
            <i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($error_msg) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <label class="input-label">Nama Lengkap</label>
        <div class="input-group-custom">
            <i class="fa-solid fa-user input-icon"></i>
            <input type="text" name="name" class="form-control form-control-dark" placeholder="Contoh: Budi" required>
        </div>

        <label class="input-label">Email</label>
        <div class="input-group-custom">
            <i class="fa-solid fa-envelope input-icon"></i>
            <input type="email" name="email" class="form-control form-control-dark" placeholder="budi@example.com" required>
        </div>

        <div class="row">
            <div class="col-4">
                <label class="input-label">Umur</label>
                <div class="input-group-custom">
                    <i class="fa-solid fa-calendar input-icon"></i>
                    <input type="number" name="age" class="form-control form-control-dark" placeholder="25" required>
                </div>
            </div>
            <div class="col-4">
                <label class="input-label">Tinggi (cm)</label>
                <div class="input-group-custom">
                    <i class="fa-solid fa-ruler-vertical input-icon"></i>
                    <input type="number" step="0.1" name="height_cm" class="form-control form-control-dark" placeholder="170" required>
                </div>
            </div>
            <div class="col-4">
                <label class="input-label">Berat (Kg)</label>
                <div class="input-group-custom">
                    <i class="fa-solid fa-weight-scale input-icon"></i>
                    <input type="number" step="0.1" name="weight_kg" class="form-control form-control-dark" placeholder="90" required>
                </div>
            </div>
        </div>

        <label class="input-label">Password</label>
        <div class="input-group-custom">
            <i class="fa-solid fa-lock input-icon"></i>
            <input type="password" name="password" class="form-control form-control-dark" placeholder="Buat password aman" required>
        </div>

        <button type="submit" class="btn btn-primary-custom w-100 mt-2">Daftar Sekarang</button>
        
        <div class="text-center mt-4">
            <!-- TEKS BAWAH DIUBAH MENJADI PUTIH TERANG -->
            <span class="text-light opacity-75 small">Sudah punya akun? <a href="login.php" class="text-info text-decoration-none fw-bold">Login di sini</a></span>
        </div>
    </form>
</div>

</body>
</html>