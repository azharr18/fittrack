<?php
session_start();

// Jika user sudah login sebelumnya, langsung arahkan ke dashboard
if (isset($_SESSION['user_id']) && isset($_SESSION['token'])) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitTrack Pro - Aplikasi Tracker Olahraga</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .hero-section {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 2rem;
        }
        .hero-card {
            background: white;
            border-radius: 20px;
            padding: 4rem 2rem;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            max-width: 650px;
            width: 100%;
        }
        .icon-wrapper {
            font-size: 4.5rem;
            background: -webkit-linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1.5rem;
        }
        .btn-primary-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            transition: transform 0.2s;
        }
        .btn-primary-custom:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            color: white;
            transform: translateY(-2px);
        }
        .btn-outline-custom {
            border: 2px solid #764ba2;
            color: #764ba2;
            transition: transform 0.2s;
        }
        .btn-outline-custom:hover {
            background-color: #f4f1f8;
            color: #764ba2;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light bg-transparent py-3">
        <div class="container">
            <a class="navbar-brand fw-bold fs-4" href="#" style="color: #4a5568;">
                <i class="fa-solid fa-stopwatch me-2" style="color: #764ba2;"></i>FitTrack Pro
            </a>
        </div>
    </nav>

    <div class="hero-section">
        <div class="hero-card">
            <div class="icon-wrapper">
                <i class="fa-solid fa-person-running"></i>
            </div>
            <h1 class="fw-bold mb-3" style="color: #2d3748;">Lacak Setiap Langkahmu!</h1>
            <p class="text-muted mb-5 fs-5 px-md-4">
                Catat aktivitas lari, trail run, hingga sesi angkat bebanmu dalam satu aplikasi modern yang terintegrasi. Analisis performa dan capai targetmu!
            </p>
            <div class="d-grid gap-3 d-sm-flex justify-content-sm-center">
                <a href="login.php" class="btn btn-primary-custom btn-lg px-5 py-3 rounded-pill fw-bold shadow-sm">
                    <i class="fa-solid fa-right-to-bracket me-2"></i> Masuk Sekarang
                </a>
                <a href="register.php" class="btn btn-outline-custom btn-lg px-5 py-3 rounded-pill fw-bold bg-white">
                    <i class="fa-solid fa-user-plus me-2"></i> Buat Akun
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>