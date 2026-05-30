<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['token'])) {
    header("Location: login.php");
    exit;
}

$user_name = $_SESSION['user_name'];
$token = $_SESSION['token']; 
$user_age = isset($_SESSION['age']) ? (int)$_SESSION['age'] : 0;
$estimasi_max_hr = ($user_age > 0) ? (220 - $user_age) : '...';

// Memperbaiki nama jika bentuknya masih email (contoh: budi@gmail.com menjadi Budi)
$display_name = $user_name;
if (strpos($display_name, '@') !== false) {
    $display_name = explode('@', $display_name)[0];
}
$display_name = ucfirst(strtolower($display_name));

$api_url = "http://localhost:3000/api/workouts";
$api_url_sleep = "http://localhost:3000/api/sleeps"; 

// ==========================================
// PROSES POST DATA 
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'create';
    $headers = ['Content-Type: application/json', 'Authorization: Bearer ' . $token];

    if ($action === 'create' || $action === 'update') {
        $data = [
            "type" => $_POST['type'],
            "distance_km" => !empty($_POST['distance_km']) ? (float)$_POST['distance_km'] : null,
            "elevation_m" => !empty($_POST['elevation_m']) ? (int)$_POST['elevation_m'] : null,
            "duration_minutes" => !empty($_POST['duration_minutes']) ? (int)$_POST['duration_minutes'] : null,
            "heart_rate_bpm" => !empty($_POST['heart_rate_bpm']) ? (int)$_POST['heart_rate_bpm'] : null,
            "max_heart_rate_bpm" => !empty($_POST['max_heart_rate_bpm']) ? (int)$_POST['max_heart_rate_bpm'] : null, 
            "shoe_id" => !empty($_POST['shoe_id']) ? (int)$_POST['shoe_id'] : null
        ];

        $ch = curl_init($action === 'create' ? $api_url : $api_url . '/' . (int)$_POST['workout_id']);
        if ($action === 'update') curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        else curl_setopt($ch, CURLOPT_POST, true);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $response = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_status === 201 || $http_status === 200) {
            $_SESSION['sweetalert'] = ['icon' => 'success', 'title' => 'Sukses!', 'text' => 'Data aktivitas tersimpan.'];
        } else {
            $error_data = json_decode($response, true);
            $pesan_error = isset($error_data['error']) ? $error_data['error'] : 'Terjadi masalah di server API';
            $_SESSION['sweetalert'] = ['icon' => 'error', 'title' => 'Gagal', 'text' => $pesan_error];
        }

    } elseif ($action === 'delete') {
        $ch = curl_init($api_url . '/' . (int)$_POST['workout_id']);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_exec($ch);
        curl_close($ch);
        $_SESSION['sweetalert'] = ['icon' => 'success', 'title' => 'Terhapus!', 'text' => 'Data dihapus.'];
    
    } elseif ($action === 'create_sleep') {
        $data_sleep = [
            "sleep_hours" => (int)$_POST['sleep_hours'],
            "sleep_minutes" => !empty($_POST['sleep_minutes']) ? (int)$_POST['sleep_minutes'] : 0,
            "sleep_quality" => (int)$_POST['sleep_quality'],
            "sleep_notes" => $_POST['sleep_notes'] ?? ''
        ];

        $ch = curl_init($api_url_sleep);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data_sleep));
        $response = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_status === 201 || $http_status === 200) {
            $_SESSION['sweetalert'] = ['icon' => 'success', 'title' => 'Mimpi Indah!', 'text' => 'Catatan tidur tersimpan.'];
        } else {
            $error_data = json_decode($response, true);
            $pesan_error = isset($error_data['error']) ? $error_data['error'] : 'Terjadi masalah di API';
            $_SESSION['sweetalert'] = ['icon' => 'error', 'title' => 'Gagal', 'text' => $pesan_error];
        }
    }
    header("Location: dashboard.php");
    exit();
}

// ==========================================
// AMBIL DATA DARI API
// ==========================================
$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token]);
$response = curl_exec($ch);
$http_status_w = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$workouts = [];
if ($http_status_w === 200 && $response) {
    $res_data = json_decode($response, true);
    if (is_array($res_data) && !isset($res_data['error'])) $workouts = $res_data;
}

$ch_sleep = curl_init($api_url_sleep);
curl_setopt($ch_sleep, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch_sleep, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token]);
$res_sleep = curl_exec($ch_sleep);
$http_status_s = curl_getinfo($ch_sleep, CURLINFO_HTTP_CODE);
curl_close($ch_sleep);

$sleeps = [];
if ($http_status_s === 200 && $res_sleep) {
    $res_sleep_data = json_decode($res_sleep, true);
    if (is_array($res_sleep_data) && !isset($res_sleep_data['error'])) $sleeps = $res_sleep_data;
}

// ==========================================
// KALKULASI METRIK LARI & GRAFIK
// ==========================================
$total_jarak = 0; $total_sesi = count($workouts);
$total_hr = 0; $hr_count = 0; $max_hr = 0;
$chart_labels = []; $chart_distance_data = [];
$zone_counts = [0, 0, 0, 0, 0]; 

foreach ($workouts as $index => $w) {
    $total_jarak += $w['distance_km'] ?? 0;
    
    // Kalkulasi AVG HR & ZONA HR
    $hr_sesi = $w['heart_rate_bpm'] ?? 0;
    if ($hr_sesi > 0) {
        $total_hr += $hr_sesi;
        $hr_count++;

        if ($hr_sesi <= 130) $zone_counts[0]++;
        elseif ($hr_sesi <= 150) $zone_counts[1]++;
        elseif ($hr_sesi <= 165) $zone_counts[2]++;
        elseif ($hr_sesi <= 180) $zone_counts[3]++;
        else $zone_counts[4]++;
    }

    // Kalkulasi Max HR
    $val_max_hr = isset($w['max_heart_rate_bpm']) ? (int)$w['max_heart_rate_bpm'] : 0;
    if ($val_max_hr > $max_hr) {
        $max_hr = $val_max_hr;
    }

    if ($index < 10) {
        $chart_labels[] = "Sesi " . ($index + 1);
        $chart_distance_data[] = $w['distance_km'] ?? 0;
    }
}

$avg_hr = $hr_count > 0 ? round($total_hr / $hr_count) : 0;
$display_max_hr = ($max_hr > 0) ? $max_hr : ($avg_hr > 0 ? $avg_hr : 0);


// ==========================================
// LOGIKA TIDUR (PERBAIKAN)
// ==========================================
$jumlah_tidur = count($sleeps);
$total_kualitas = 0;
if ($jumlah_tidur > 0) {
    foreach ($sleeps as $s) { $total_kualitas += $s['sleep_quality']; }
    $rata_kualitas = $total_kualitas / $jumlah_tidur;
} else {
    $rata_kualitas = 0;
}

// LOGIKA KESIAPAN FISIK YANG BENAR-BENAR MASUK AKAL
$skor_kesiapan = 0;
$status = "";
$warna = "";
$pesan_kesiapan = "";

if ($jumlah_tidur == 0) {
    // Memaksa user input tidur, skor tidak boleh dikarang 70%
    $status = "BUTUH DATA"; 
    $warna = "#8b8b93"; 
    $pesan_kesiapan = "Sistem membutuhkan data kualitas tidur terakhirmu untuk menghitung kesiapan fisik. Jangan lupa catat waktu tidur semalam ya!";
} else {
    $last_sleep = end($sleeps);
    $skor_kesiapan = $last_sleep['sleep_quality']; 
    
    // Potong skor jika lari terakhir terlalu ekstrem
    if ($total_sesi > 0) {
        $last_workout = end($workouts);
        if (($last_workout['distance_km'] > 15) || ($last_workout['elevation_m'] > 500)) {
            $skor_kesiapan -= 15;
        }
    }
    
    $skor_kesiapan = min(100, max(0, $skor_kesiapan));

    if ($skor_kesiapan >= 85) { $status = "PRIMA"; $warna = "#37E39F"; $pesan_kesiapan = "Kondisi fisikmu terpantau pulih maksimal. Tubuhmu fit dan siap untuk sesi lari interval atau jarak jauh hari ini!"; }
    elseif ($skor_kesiapan >= 60) { $status = "CUKUP"; $warna = "#facc15"; $pesan_kesiapan = "Kondisimu cukup baik, namun belum pulih 100%. Sangat disarankan untuk mengambil porsi lari ringan (Zona 2) saja hari ini."; }
    else { $status = "RECOVERY"; $warna = "#F84F77"; $pesan_kesiapan = "Tubuhmu sedang kelelahan dan sangat membutuhkan pemulihan. Utamakan rehat total atau sekadar jalan santai hari ini!"; }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitTrack Pro - Analitik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> 
    <style>
        :root {
            --bg-main: #121212; --bg-card: #1e1e24; --bg-sidebar: #17171d;
            --text-main: #e0e0e0; --text-muted: #8b8b93;
            --accent-purple: #8352FD; --accent-green: #37E39F;
            --accent-pink: #F84F77; --border-color: #2c2c35;
        }

        body { background-color: var(--bg-main); color: var(--text-main); font-family: 'Segoe UI', Tahoma, sans-serif; overflow-x: hidden; }
        .wrapper { display: flex; min-height: 100vh; }
        .sidebar { width: 260px; min-width: 260px; flex-shrink: 0; background-color: var(--bg-sidebar); border-right: 1px solid var(--border-color); display: flex; flex-direction: column; padding: 1.5rem; }
        .brand-logo { color: white; font-weight: bold; font-size: 1.2rem; margin-bottom: 2rem; display: flex; align-items: center; gap: 10px; }
        .user-profile { background-color: var(--bg-card); padding: 10px; border-radius: 12px; display: flex; align-items: center; gap: 12px; margin-bottom: 2rem; border: 1px solid var(--border-color); }
        .avatar { width: 40px; height: 40px; background: linear-gradient(135deg, var(--accent-purple), #4a90e2); border-radius: 50%; display: flex; justify-content: center; align-items: center; font-weight: bold; }
        .menu-label { font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; font-weight: bold; }
        .sidebar-btn { background-color: transparent; color: var(--text-main); border: 1px solid var(--border-color); padding: 10px 15px; border-radius: 8px; margin-bottom: 10px; text-align: left; transition: 0.2s; cursor: pointer; display: flex; align-items: center; gap: 10px; text-decoration: none;}
        .sidebar-btn:hover { background-color: var(--bg-card); color: white; }
        .logout-btn { margin-top: auto; border: none; background-color: var(--bg-card); color: var(--text-main); text-align: center; justify-content: center; padding: 12px;}
        
        /* Tambahan padding bottom agar text panjang AI Coach tidak menabrak batas layar */
        .main-content { flex-grow: 1; padding: 2rem; padding-bottom: 5rem; height: 100vh; overflow-y: auto; }
        
        .custom-tabs { display: flex; gap: 15px; margin-bottom: 2rem; border-bottom: 1px solid var(--border-color); padding-bottom: 15px; }
        .custom-tab { color: var(--text-muted); text-decoration: none; font-weight: 600; padding: 5px 10px; border-radius: 6px; transition: 0.2s; }
        .custom-tab.active { background-color: var(--bg-card); color: white; border: 1px solid var(--border-color); }
        .dark-card { background-color: var(--bg-card); border-radius: 12px; padding: 1.5rem; border: 1px solid var(--border-color); margin-bottom: 1.5rem; }
        .metric-title { font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: bold; letter-spacing: 0.5px; }
        .metric-value { font-size: 2rem; font-weight: 800; color: white; display: flex; align-items: baseline; gap: 5px; }
        .metric-unit { font-size: 0.9rem; font-weight: normal; color: var(--text-muted); }
        .progress-circle { min-width: 80px; width: 80px; height: 80px; border-radius: 50%; border: 4px solid; display: flex; flex-direction: column; justify-content: center; align-items: center; transition: 0.3s; }
        .form-control-dark, .form-select-dark { background-color: var(--bg-main); border: 1px solid var(--border-color); color: white !important; }
        .form-control-dark:focus { background-color: var(--bg-main); color: white; border-color: var(--accent-purple); box-shadow: 0 0 0 0.25rem rgba(131, 82, 253, 0.25); }
        .form-control-dark::placeholder, textarea.form-control-dark::placeholder { color: #9ca3af; opacity: 1; }
        .ai-text { color: var(--text-muted); font-size: 0.9rem; line-height: 1.6; }
        .h-100-custom { height: 100%; display: flex; flex-direction: column; justify-content: center; }
    </style>
</head>
<body>

<div class="wrapper">
    <aside class="sidebar">
        <div class="brand-logo"><i class="fa-solid fa-bolt text-primary"></i> FitTrack Pro</div>
        
        <div class="user-profile">
            <div class="avatar"><?= strtoupper(substr($display_name, 0, 1)) ?></div>
            <div>
                <div style="font-weight: bold; font-size: 0.9rem;"><?= htmlspecialchars($display_name) ?></div>
                <div style="font-size: 0.75rem; color: var(--text-muted);"><?= isset($_SESSION['weight_kg']) && $_SESSION['weight_kg'] !== '' ? $_SESSION['weight_kg'] . ' Kg Runner' : 'Runner' ?></div>
            </div>
        </div>

        <div class="menu-label">Menu Data</div>
        <button class="sidebar-btn" data-bs-toggle="modal" data-bs-target="#modalCatat"><i class="fa-solid fa-plus text-success"></i> Tambah Lari Manual</button>
        <button class="sidebar-btn" data-bs-toggle="modal" data-bs-target="#modalTidur"><i class="fa-solid fa-bed text-info"></i> Catat Tidur Semalam</button>
        
        <!-- Tombol Reset dihapus karena terlalu berisiko, diganti Pengaturan -->
        <div class="mt-4 menu-label">Pengaturan</div>
        <a href="profile.php" class="sidebar-btn"><i class="fa-solid fa-gear text-secondary"></i> Profil Akun</a>
        <a href="logout.php" class="sidebar-btn logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Keluar</a>
    </aside>

    <main class="main-content">
        <header class="mb-4">
            <h4 class="fw-bold mb-1">FitTrack Pro</h4>
            <div class="text-muted small">Halo, <?= htmlspecialchars($display_name) ?> — <?= date('l, d F Y') ?></div>
        </header>

        <nav class="custom-tabs">
            <a href="dashboard.php" class="custom-tab active">Dashboard</a>
            <a href="rencana_latihan.php" class="custom-tab">Rencana Latihan</a>
            <a href="race_prediction.php" class="custom-tab">Race Prediction</a>
            <a href="riwayat_lari.php" class="custom-tab">Riwayat Lari</a>
        </nav>

        <div class="row mb-2">
            <div class="col-md-3">
                <div class="dark-card">
                    <div class="metric-title">Total Jarak</div>
                    <div class="metric-value"><?= number_format($total_jarak, 1) ?> <span class="metric-unit">km</span></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dark-card">
                    <div class="metric-title">Total Sesi</div>
                    <div class="metric-value"><?= $total_sesi ?> <span class="metric-unit">kali</span></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dark-card">
                    <div class="metric-title">Avg Heart Rate</div>
                    <div class="metric-value"><?= $avg_hr ?> <span class="metric-unit">bpm</span></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dark-card">
                    <div class="metric-title">Max HR Aktual</div>
                    <div class="metric-value"><?= $display_max_hr ?> <span class="metric-unit">bpm</span></div>
                </div>
            </div>
        </div>

        <!-- KARTU KESIAPAN LATIHAN -->
        <div class="row">
            <div class="col-md-12 mb-3">
                <div class="dark-card d-flex align-items-center gap-4">
                    <div class="progress-circle" style="border-color: <?= $warna ?>;">
                        <span class="fs-4 fw-bold <?= $jumlah_tidur > 0 ? 'text-white' : 'text-muted' ?>">
                            <?= $jumlah_tidur > 0 ? $skor_kesiapan . '%' : '-' ?>
                        </span>
                        <span style="font-size: 0.6rem; color: <?= $warna ?>; font-weight: bold; text-align: center;"><?= $status ?></span>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-2"><i class="fa-solid fa-bolt text-success me-2"></i>Kesiapan Latihan Terkini</h6>
                        <p class="ai-text mb-0"><?= $pesan_kesiapan ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-7">
                <div class="dark-card">
                    <div class="d-flex justify-content-between mb-3">
                        <!-- Judul Grafik Diperbaiki -->
                        <div class="metric-title">Tren Jarak Lari (Per Sesi)</div>
                    </div>
                    <div style="height: 250px;"><canvas id="lineChart"></canvas></div>
                </div>
                
                <div class="dark-card">
                    <h6 class="fw-bold mb-3"><i class="fa-solid fa-wand-magic-sparkles text-info me-2"></i>AI Coach Personal</h6>
                    <?php if($total_sesi == 0): ?>
                        <p class="ai-text mb-0">Halo! Ayo catat sesi lari pertama kamu melalui menu di sebelah kiri, nanti saya akan memberikan rekomendasi program dan target latihan yang sesuai untukmu.</p>
                    <?php else: ?>
                        <p class="ai-text mb-0">Melihat target elevasi jalur menanjak ke depan, tingkatkan porsi latihan di zona 2. Pastikan hidrasi cukup dan nutrisi terjaga. Kamu cenderung memiliki detak jantung lebih stabil setelah beristirahat penuh.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-md-5">
                <div class="dark-card">
                    <div class="metric-title mb-3">Zona Detak Jantung</div>
                    <div style="height: 180px;"><canvas id="barChart"></canvas></div>
                    
                    <div class="mt-4 ai-text" style="font-size: 0.85rem;">
                        <span class="text-white fw-bold">Detak Jantung:</span> Estimasi Max HR berdasarkan umur adalah <?= $estimasi_max_hr ?> bpm, tapi data mencatat hingga <strong class="text-warning"><?= $display_max_hr ?> bpm</strong>. Zona latihan dikalkulasi pakai data aktual yang lebih akurat.
                    </div>
                </div>
                
                <!-- METRIK TIDUR DIPERBAIKI (Lebih Jelas & Berguna) -->
                <div class="row">
                    <div class="col-6">
                        <div class="dark-card text-center p-3 h-100-custom">
                            <div class="metric-title" style="font-size: 0.65rem;">Rata-rata Kualitas Tidur</div>
                            <div class="fs-3 fw-bold text-white mt-2"><?= $jumlah_tidur > 0 ? round($rata_kualitas) : 0 ?><span class="metric-unit">/100</span></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="dark-card text-center p-3 h-100-custom">
                            <div class="metric-title" style="font-size: 0.65rem;">Total Catatan Tidur</div>
                            <div class="fs-3 fw-bold text-white mt-2"><?= $jumlah_tidur ?><span class="metric-unit"> Hari</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- MODAL CATAT AKTIVITAS (TETAP SAMA) -->
<div class="modal fade" id="modalCatat" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="background-color: var(--bg-card); color: white; border: 1px solid var(--border-color);">
      <div class="modal-header border-bottom-0">
        <h5 class="modal-title fw-bold"><i class="fa-solid fa-plus-circle text-primary me-2"></i>Catat Aktivitas</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form method="POST">
            <input type="hidden" name="action" value="create">
            <div class="mb-3">
                <label class="form-label text-white small">Jenis Olahraga</label>
                <select name="type" class="form-select form-select-dark" required>
                    <option value="Trail Run">Trail Run</option>
                    <option value="Lari Jalan Raya">Lari Jalan Raya</option>
                    <option value="Hiking">Hiking</option>
                </select>
            </div>
            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label text-white small">Jarak (km)</label>
                    <input type="number" step="0.1" name="distance_km" class="form-control form-control-dark" placeholder="0.0">
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label text-white small">Elevasi (m)</label>
                    <input type="number" name="elevation_m" class="form-control form-control-dark" placeholder="0">
                </div>
            </div>
            
            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label text-white small">Durasi (Menit)</label>
                    <input type="number" name="duration_minutes" class="form-control form-control-dark" placeholder="0">
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label text-white small">Avg HR (BPM)</label>
                    <input type="number" name="heart_rate_bpm" class="form-control form-control-dark" placeholder="Contoh: 150">
                </div>
            </div>

            <div class="row">
                <div class="col-6 mb-4">
                    <label class="form-label text-white small">Max HR (BPM)</label>
                    <input type="number" name="max_heart_rate_bpm" class="form-control form-control-dark" placeholder="Contoh: 180">
                </div>
                <div class="col-6 mb-4">
                    <label class="form-label text-white small">ID Sepatu</label>
                    <input type="number" name="shoe_id" class="form-control form-control-dark" placeholder="Opsional">
                </div>
            </div>
            
            <button type="submit" class="btn w-100 fw-bold" style="background-color: var(--accent-purple); color: white;">Simpan Data</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- MODAL CATAT TIDUR (TETAP SAMA) -->
<div class="modal fade" id="modalTidur" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="background-color: var(--bg-card); color: white; border: 1px solid var(--border-color);">
      <div class="modal-header border-bottom-0">
        <h5 class="modal-title fw-bold"><i class="fa-solid fa-bed text-info me-2"></i>Catat Tidur Semalam</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form method="POST">
            <input type="hidden" name="action" value="create_sleep">
            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label text-white small">Durasi (Jam)</label>
                    <input type="number" name="sleep_hours" class="form-control form-control-dark" placeholder="Contoh: 7" required>
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label text-white small">Menit</label>
                    <input type="number" name="sleep_minutes" class="form-control form-control-dark" placeholder="Contoh: 30">
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label text-white small">Kualitas Tidur (Skor 1-100)</label>
                <input type="number" name="sleep_quality" class="form-control form-control-dark" placeholder="Contoh: 85" min="1" max="100" required>
            </div>
            <div class="mb-4">
                <label class="form-label text-white small">Catatan (Opsional)</label>
                <textarea name="sleep_notes" class="form-control form-control-dark" rows="2" placeholder="Contoh: Sering terbangun tengah malam..."></textarea>
            </div>
            <button type="submit" class="btn w-100 fw-bold" style="background-color: #38bdf8; color: white;">Simpan Data Tidur</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    Chart.defaults.color = '#8b8b93';
    Chart.defaults.font.family = "'Segoe UI', Tahoma, sans-serif";

    const ctxLine = document.getElementById('lineChart').getContext('2d');
    new Chart(ctxLine, {
        type: 'line',
        data: {
            labels: <?= json_encode($chart_labels) ?>,
            datasets: [{
                label: 'Jarak (km)',
                data: <?= json_encode($chart_distance_data) ?>,
                borderColor: '#8352FD', borderWidth: 2, pointBackgroundColor: '#8352FD', pointRadius: 4, tension: 0.4
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { grid: { color: 'rgba(255, 255, 255, 0.05)' } }, y: { grid: { color: 'rgba(255, 255, 255, 0.05)' }, beginAtZero: true } } }
    });

    const ctxBar = document.getElementById('barChart').getContext('2d');
    new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: ['Z1', 'Z2', 'Z3', 'Z4', 'Z5'],
            datasets: [{
                data: <?= json_encode($zone_counts) ?>,
                backgroundColor: ['#38bdf8', '#4ade80', '#facc15', '#fb923c', '#f87171'],
                borderRadius: 4
            }]
        },
        options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { display: false }, y: { grid: { display: false }, border: { display: false } } } }
    });
</script>

<?php if (isset($_SESSION['sweetalert'])): ?>
    <script>
        Swal.fire({
            icon: '<?= $_SESSION['sweetalert']['icon'] ?>',
            title: '<?= $_SESSION['sweetalert']['title'] ?>',
            text: '<?= $_SESSION['sweetalert']['text'] ?>',
            timer: 3000, showConfirmButton: false, toast: true, position: 'top-end', background: '#1e1e24', color: '#fff'
        });
    </script>
    <?php unset($_SESSION['sweetalert']); ?>
<?php endif; ?>

</body>
</html>