<?php
session_start();

if (isset($_SESSION['user_id']) && isset($_SESSION['token'])) {
    header("Location: dashboard.php");
    exit;
}

$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Tembak ke API Login Node.js
    $api_url = "http://localhost:3000/api/login";
    $data = [
        "email" => $_POST['email'],
        "password" => $_POST['password']
    ];

    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    
    $response = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_status === 200) {
        $result = json_decode($response, true);
        
        $_SESSION['token'] = $result['token'];
        $_SESSION['user_id'] = $result['user']['id'];
        $_SESSION['user_name'] = $result['name']; 
        
        // ----------------------------------------------------
        // INI ADALAH KUNCI PENGAMBILAN DATA DARI NODE.JS
        // ----------------------------------------------------
        $_SESSION['weight_kg'] = isset($result['weight_kg']) ? $result['weight_kg'] : ''; 
        $_SESSION['age'] = isset($result['age']) ? $result['age'] : 0; 
        
        header("Location: dashboard.php");
        exit;
    } else {
        $res_data = json_decode($response, true);
        $error_msg = isset($res_data['error']) ? $res_data['error'] : "Koneksi ke API gagal atau email/password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FitTrack Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --bg-main: #121212; --bg-card: #1e1e24; --accent-purple: #8352FD; --border-color: #2c2c35; }
        body { background-color: var(--bg-main); min-height: 100vh; display: flex; align-items: center; font-family: 'Segoe UI', Tahoma, sans-serif; }
        .card { background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        .form-control-dark { background-color: var(--bg-main); border: 1px solid var(--border-color); color: white !important; }
        .form-control-dark:focus { background-color: var(--bg-main); color: white; border-color: var(--accent-purple); box-shadow: 0 0 0 0.25rem rgba(131, 82, 253, 0.25); }
        .form-control-dark::placeholder { color: #6b7280; }
        .btn-custom { background: var(--accent-purple); color: white; border-radius: 8px; font-weight: bold; }
        .btn-custom:hover { background: #7040e5; color: white; }
        .brand-logo-icon { background: linear-gradient(135deg, var(--accent-purple), #4a90e2); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card p-4">
                    <div class="text-center mb-4">
                        <i class="fa-solid fa-person-running fa-3x brand-logo-icon mb-2"></i>
                        <h3 class="fw-bold text-white">Selamat Datang</h3>
                        <p class="text-light opacity-75 small">Login untuk melanjutkan ke FitTrack Pro</p>
                    </div>
                    
                    <?php if (!empty($error_msg)): ?>
                        <div class="alert alert-danger text-center shadow-sm" style="background-color: rgba(248, 79, 119, 0.1); color: #F84F77; border-color: rgba(248, 79, 119, 0.2);" role="alert">
                            <i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($error_msg) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label" style="color: #cccccc;"><i class="fa-solid fa-envelope me-2"></i>Email</label>
                            <input type="email" name="email" class="form-control form-control-dark form-control-lg" required placeholder="Masukkan email">
                        </div>
                        <div class="mb-4">
                            <label class="form-label" style="color: #cccccc;"><i class="fa-solid fa-lock me-2"></i>Password</label>
                            <input type="password" name="password" class="form-control form-control-dark form-control-lg" required placeholder="••••••••">
                        </div>
                        <button type="submit" class="btn btn-custom btn-lg w-100 mb-3">Login</button>
                        <div class="text-center">
                            <span class="text-light opacity-75 small">Belum punya akun? </span>
                            <a href="register.php" class="text-info text-decoration-none fw-bold small">Daftar sekarang</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>