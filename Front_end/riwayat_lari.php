<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['token'])) { header("Location: login.php"); exit; }

$user_name = $_SESSION['user_name'];
$token = $_SESSION['token']; 
$api_url = "http://localhost:3000/api/workouts";

// Ambil Data Workout dari API
$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token]);
$response = curl_exec($ch);
$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$workouts = [];
if ($http_status === 200 && $response) {
    $res_data = json_decode($response, true);
    if (is_array($res_data) && !isset($res_data['error'])) $workouts = $res_data;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Lari - FitTrack Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --bg-main: #121212; --bg-card: #1e1e24; --bg-sidebar: #17171d; --text-main: #e0e0e0; --text-muted: #8b8b93; --accent-purple: #8352FD; --border-color: #2c2c35; }
        body { background-color: var(--bg-main); color: var(--text-main); font-family: 'Segoe UI', Tahoma, sans-serif; overflow-x: hidden; }
        .wrapper { display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background-color: var(--bg-sidebar); border-right: 1px solid var(--border-color); display: flex; flex-direction: column; padding: 1.5rem; }
        .brand-logo { color: white; font-weight: bold; font-size: 1.2rem; margin-bottom: 2rem; display: flex; align-items: center; gap: 10px; }
        .sidebar-btn { background-color: transparent; color: var(--text-main); border: 1px solid var(--border-color); padding: 10px 15px; border-radius: 8px; margin-bottom: 10px; text-align: left; transition: 0.2s; cursor: pointer; display: flex; align-items: center; gap: 10px; text-decoration: none;}
        .sidebar-btn:hover { background-color: var(--bg-card); color: white; }
        .main-content { flex-grow: 1; padding: 2rem; height: 100vh; overflow-y: auto; }
        .custom-tabs { display: flex; gap: 15px; margin-bottom: 2rem; border-bottom: 1px solid var(--border-color); padding-bottom: 15px; }
        .custom-tab { color: var(--text-muted); text-decoration: none; font-weight: 600; padding: 5px 10px; border-radius: 6px; transition: 0.2s; }
        .custom-tab.active { background-color: var(--bg-card); color: white; border: 1px solid var(--border-color); }
        .dark-card { background-color: var(--bg-card); border-radius: 12px; padding: 1.5rem; border: 1px solid var(--border-color); margin-bottom: 1.5rem; }
        .table-dark-custom { color: var(--text-main); }
        .table-dark-custom th { background-color: rgba(255,255,255,0.05); color: white; border-bottom: 1px solid var(--border-color); font-weight: 600; }
        .table-dark-custom td { border-bottom: 1px solid var(--border-color); vertical-align: middle; }
        
        /* Input Dark Mode untuk Modal */
        .form-control-dark, .form-select-dark { background-color: var(--bg-main); border: 1px solid var(--border-color); color: white !important; }
        .form-control-dark:focus { background-color: var(--bg-main); color: white; border-color: var(--accent-purple); box-shadow: 0 0 0 0.25rem rgba(131, 82, 253, 0.25); }
    </style>
</head>
<body>
<div class="wrapper">
    <aside class="sidebar">
        <div class="brand-logo"><i class="fa-solid fa-bolt text-primary"></i> FitTrack Pro</div>
        <a href="dashboard.php" class="sidebar-btn"><i class="fa-solid fa-arrow-left text-info"></i> Kembali ke Analitik</a>
    </aside>
    <main class="main-content">
        <header class="mb-4">
            <h4 class="fw-bold mb-1">FitTrack Pro</h4>
        </header>

        <nav class="custom-tabs">
            <a href="dashboard.php" class="custom-tab">Dashboard</a>
            <a href="rencana_latihan.php" class="custom-tab">Rencana Latihan</a>
            <a href="race_prediction.php" class="custom-tab">Race Prediction</a>
            <a href="riwayat_lari.php" class="custom-tab active">Riwayat Lari</a>
        </nav>

        <div class="dark-card">
            <h6 class="fw-bold mb-4 text-white"><i class="fa-solid fa-list me-2 text-primary"></i>Semua Sesi Olahraga</h6>
            <div class="table-responsive">
                <table class="table table-dark-custom table-borderless w-100">
                    <thead>
                        <tr>
                            <th>Tipe</th>
                            <th>Jarak (km)</th>
                            <th>Elevasi (m)</th>
                            <th>Durasi (m)</th>
                            <th>Avg HR</th>
                            <th>Max HR</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($workouts)): ?>
                            <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada data riwayat lari.</td></tr>
                        <?php else: ?>
                            <?php foreach(array_reverse($workouts) as $w): ?>
                            <tr>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($w['type']) ?></span></td>
                                <td class="fw-bold text-white"><?= htmlspecialchars($w['distance_km']) ?></td>
                                <td><?= htmlspecialchars($w['elevation_m']) ?> <i class="fa-solid fa-arrow-trend-up text-success fa-xs"></i></td>
                                <td><?= htmlspecialchars($w['duration_minutes']) ?></td>
                                <td><?= htmlspecialchars($w['heart_rate_bpm'] ?? '-') ?> bpm</td>
                                <td><span class="text-warning fw-bold"><?= htmlspecialchars($w['max_heart_rate_bpm'] ?? '-') ?></span> bpm</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-info me-1" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $w['id'] ?>"><i class="fa-solid fa-pen"></i></button>
                                    
                                    <form method="POST" action="dashboard.php" style="display:inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="workout_id" value="<?= $w['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus sesi ini?');"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>

                            <div class="modal fade" id="modalEdit<?= $w['id'] ?>" tabindex="-1" aria-hidden="true">
                              <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content" style="background-color: var(--bg-card); color: white; border: 1px solid var(--border-color);">
                                  <div class="modal-header border-bottom-0">
                                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-pen text-info me-2"></i>Edit Aktivitas</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                  </div>
                                  <div class="modal-body">
                                    <form method="POST" action="dashboard.php">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="workout_id" value="<?= $w['id'] ?>">
                                        
                                        <div class="mb-3">
                                            <label class="form-label text-white small">Jenis Olahraga</label>
                                            <select name="type" class="form-select form-select-dark" required>
                                                <option value="Trail Run" <?= $w['type'] == 'Trail Run' ? 'selected' : '' ?>>Trail Run</option>
                                                <option value="Lari Jalan Raya" <?= $w['type'] == 'Lari Jalan Raya' ? 'selected' : '' ?>>Lari Jalan Raya</option>
                                                <option value="Hiking" <?= $w['type'] == 'Hiking' ? 'selected' : '' ?>>Hiking</option>
                                            </select>
                                        </div>
                                        <div class="row">
                                            <div class="col-6 mb-3">
                                                <label class="form-label text-white small">Jarak (km)</label>
                                                <input type="number" step="0.1" name="distance_km" class="form-control form-control-dark" value="<?= htmlspecialchars($w['distance_km']) ?>">
                                            </div>
                                            <div class="col-6 mb-3">
                                                <label class="form-label text-white small">Elevasi (m)</label>
                                                <input type="number" name="elevation_m" class="form-control form-control-dark" value="<?= htmlspecialchars($w['elevation_m']) ?>">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-6 mb-3">
                                                <label class="form-label text-white small">Durasi (Menit)</label>
                                                <input type="number" name="duration_minutes" class="form-control form-control-dark" value="<?= htmlspecialchars($w['duration_minutes']) ?>">
                                            </div>
                                            <div class="col-6 mb-3">
                                                <label class="form-label text-white small">Avg HR (BPM)</label>
                                                <input type="number" name="heart_rate_bpm" class="form-control form-control-dark" value="<?= htmlspecialchars($w['heart_rate_bpm'] ?? '') ?>">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-6 mb-4">
                                                <label class="form-label text-white small">Max HR (BPM)</label>
                                                <input type="number" name="max_heart_rate_bpm" class="form-control form-control-dark" value="<?= htmlspecialchars($w['max_heart_rate_bpm'] ?? '') ?>">
                                            </div>
                                            <div class="col-6 mb-4">
                                                <label class="form-label text-white small">ID Sepatu</label>
                                                <input type="number" name="shoe_id" class="form-control form-control-dark" value="<?= htmlspecialchars($w['shoe_id'] ?? '') ?>">
                                            </div>
                                        </div>
                                        <button type="submit" class="btn w-100 fw-bold" style="background-color: #38bdf8; color: white;">Simpan Perubahan</button>
                                    </form>
                                  </div>
                                </div>
                              </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>