<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['token'])) { header("Location: login.php"); exit; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Race Prediction - FitTrack Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --bg-main: #121212; --bg-card: #1e1e24; --bg-sidebar: #17171d; --text-main: #e0e0e0; --text-muted: #8b8b93; --accent-purple: #8352FD; --border-color: #2c2c35; }
        body { background-color: var(--bg-main); color: var(--text-main); font-family: 'Segoe UI', Tahoma, sans-serif; overflow-x: hidden; }
        .wrapper { display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background-color: var(--bg-sidebar); border-right: 1px solid var(--border-color); display: flex; flex-direction: column; padding: 1.5rem; }
        .brand-logo { color: white; font-weight: bold; font-size: 1.2rem; margin-bottom: 2rem; display: flex; align-items: center; gap: 10px; }
        .sidebar-btn { background-color: transparent; color: var(--text-main); border: 1px solid var(--border-color); padding: 10px 15px; border-radius: 8px; margin-bottom: 10px; text-align: left; text-decoration: none;}
        .main-content { flex-grow: 1; padding: 2rem; height: 100vh; overflow-y: auto; }
        .custom-tabs { display: flex; gap: 15px; margin-bottom: 2rem; border-bottom: 1px solid var(--border-color); padding-bottom: 15px; }
        .custom-tab { color: var(--text-muted); text-decoration: none; font-weight: 600; padding: 5px 10px; border-radius: 6px; }
        .custom-tab.active { background-color: var(--bg-card); color: white; border: 1px solid var(--border-color); }
        .dark-card { background-color: var(--bg-card); border-radius: 12px; padding: 1.5rem; border: 1px solid var(--border-color); margin-bottom: 1.5rem; text-align: center; }
    </style>
</head>
<body>
<div class="wrapper">
    <aside class="sidebar">
        <div class="brand-logo"><i class="fa-solid fa-bolt text-primary"></i> FitTrack Pro</div>
        <a href="dashboard.php" class="sidebar-btn"><i class="fa-solid fa-arrow-left text-info"></i> Kembali</a>
    </aside>
    <main class="main-content">
        <nav class="custom-tabs mt-4">
            <a href="dashboard.php" class="custom-tab">Dashboard</a>
            <a href="rencana_latihan.php" class="custom-tab">Rencana Latihan</a>
            <a href="race_prediction.php" class="custom-tab active">Race Prediction</a>
            <a href="riwayat_lari.php" class="custom-tab">Riwayat Lari</a>
        </nav>
        
        <div class="dark-card">
            <i class="fa-solid fa-stopwatch fa-3x text-info mb-3"></i>
            <h5 class="text-white fw-bold">Prediksi Waktu Lari (Segera Hadir)</h5>
            <p class="text-muted mt-2">Modul AI Machine Learning untuk memprediksi waktu penyelesaian lomba berdasarkan riwayat metrik (Jarak, Elevasi, dan Heart Rate) Anda sedang dikembangkan.</p>
        </div>
    </main>
</div>
</body>
</html>