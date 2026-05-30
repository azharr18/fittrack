<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['token'])) { header("Location: login.php"); exit; }

$user_name = $_SESSION['user_name'];
$display_name = explode('@', $user_name)[0];
$display_name = ucfirst(strtolower($display_name));

// ==========================================
// DATABASE PROGRAM LATIHAN
// ==========================================
$programs = [
    'trail_10k_ekstrem' => [
        'title' => 'Trail 10K Ekstrem (Elevasi 1600m)',
        'desc' => 'Disusun khusus untuk persiapan rute menanjak ekstrem jalur pegunungan curam.',
        'icon' => 'fa-mountain-sun text-danger',
        'fase1' => ['Senin: Rest / Aktivitas ringan', 'Selasa: Easy Run 4-5 km (Zona 2)', 'Rabu: Latihan Beban Lower Body', 'Kamis: Hill Repeats (Lari menanjak pendek)', 'Jumat: Rest', 'Sabtu/Minggu: Long Hike / Trail ringan 6-7 km.'],
        'fase2' => ['Senin: Latihan Beban Upper Body & Core', 'Selasa: Easy Run 5 km', 'Rabu: Latihan anak tangga 30 menit', 'Kamis: Tempo Run 4 km', 'Jumat: Rest Total', 'Sabtu/Minggu: Simulasi Trail 8-10km (Cari elevasi minimum 800m).'],
        'fase3' => ['H-7: Kurangi volume latihan hingga 50%', 'H-5: Jalan santai / Jogging ringan 3 km', 'H-3: Istirahat penuh, fokus hidrasi', 'H-2: Persiapkan perlengkapan trail (Sepatu, Vest)', 'H-1: Carbo-loading & tidur prima!']
    ],
    'trail_10k_menengah' => [
        'title' => 'Trail 10K Perbukitan (Elevasi 600m)',
        'desc' => 'Program untuk rute perbukitan menengah dan jalur makadam bergelombang.',
        'icon' => 'fa-tree text-success',
        'fase1' => ['Senin: Rest', 'Selasa: Easy Run 5 km (Zona 2)', 'Rabu: Latihan Beban Keseluruhan', 'Kamis: Lari interval 400m x 4', 'Jumat: Rest', 'Sabtu/Minggu: Trail Run santai 8 km.'],
        'fase2' => ['Senin: Core Training', 'Selasa: Fartlek (Lari cepat-lambat) 5 km', 'Rabu: Easy Run 4 km', 'Kamis: Hill Repeats', 'Jumat: Rest Total', 'Sabtu/Minggu: Trail Run 10-12km dengan elevasi 500m+.'],
        'fase3' => ['H-7: Kurangi intensitas, tetap jaga ritme', 'H-5: Easy Run 3 km', 'H-3: Rest total', 'H-2: Cek rute dan perlengkapan', 'H-1: Carbo-loading secukupnya.']
    ],
    'road_10k' => [
        'title' => 'Road Race 10K (Flat/Jalan Raya)',
        'desc' => 'Fokus pada kecepatan (Pace) dan ketahanan menahan asam laktat di jalan raya rata.',
        'icon' => 'fa-road text-secondary',
        'fase1' => ['Senin: Rest', 'Selasa: Easy Run 5 km', 'Rabu: Strength Training', 'Kamis: Tempo Run 4 km (Pace target)', 'Jumat: Rest', 'Sabtu/Minggu: Long Run 8 km (Pace santai).'],
        'fase2' => ['Senin: Rest', 'Selasa: Interval 1km x 4 (Pace cepat)', 'Rabu: Easy Run 5 km', 'Kamis: Tempo Run 6 km', 'Jumat: Rest', 'Sabtu/Minggu: Long Run 10-12 km.'],
        'fase3' => ['H-7: Long run terakhir (8km santai)', 'H-5: Interval pendek 400m x 4', 'H-3: Jogging ringan 2 km', 'H-2: Rest Total', 'H-1: Siapkan outfit dan hidrasi.']
    ],
    'road_5k' => [
        'title' => 'Pemula 5K (Jalan Raya)',
        'desc' => 'Program ringan untuk membiasakan tubuh berlari tanpa henti sejauh 5 kilometer.',
        'icon' => 'fa-child-reaching text-info',
        'fase1' => ['Senin: Rest', 'Selasa: Jalan cepat / Jogging pelan 2 km', 'Rabu: Rest', 'Kamis: Jogging pelan 3 km', 'Jumat: Rest', 'Sabtu/Minggu: Lari/Jalan kombinasi 4 km.'],
        'fase2' => ['Senin: Rest', 'Selasa: Lari non-stop 3 km', 'Rabu: Rest', 'Kamis: Fartlek ringan 3 km', 'Jumat: Rest', 'Sabtu/Minggu: Lari target 5 km santai.'],
        'fase3' => ['H-7: Lari 4 km', 'H-5: Jogging 2 km', 'H-3: Rest', 'H-2: Rest', 'H-1: Tidur cukup, jangan makan berlebihan.']
    ],
    'half_marathon' => [
        'title' => 'Half Marathon 21.1K (Jalan Raya)',
        'desc' => 'Program ketahanan tingkat lanjut untuk menyelesaikan jarak 21.1 km dengan aman.',
        'icon' => 'fa-medal text-warning',
        'fase1' => ['Senin: Rest / Cross Training', 'Selasa: Easy Run 6 km', 'Rabu: Tempo Run 5 km', 'Kamis: Easy Run 6 km', 'Jumat: Rest', 'Sabtu/Minggu: Long Run 12 km.'],
        'fase2' => ['Senin: Latihan Beban', 'Selasa: Interval 1km x 6', 'Rabu: Easy Run 8 km', 'Kamis: Tempo Run 8 km', 'Jumat: Rest', 'Sabtu/Minggu: Long Run 16-18 km.'],
        'fase3' => ['H-14: Long run terakhir 15km', 'H-7: Volume turun (Tapering) 8km', 'H-5: Easy run 5km', 'H-2: Rest Total', 'H-1: Carbo-loading wajib!']
    ]
];

// Menangkap pilihan user, default ke trail 10k ekstrem jika belum memilih
$selected_key = $_GET['program'] ?? 'trail_10k_ekstrem';
if (!array_key_exists($selected_key, $programs)) {
    $selected_key = 'trail_10k_ekstrem';
}
$current_program = $programs[$selected_key];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rencana Latihan - FitTrack Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --bg-main: #121212; --bg-card: #1e1e24; --bg-sidebar: #17171d; --text-main: #e0e0e0; --text-muted: #8b8b93; --accent-purple: #8352FD; --border-color: #2c2c35; }
        body { background-color: var(--bg-main); color: var(--text-main); font-family: 'Segoe UI', Tahoma, sans-serif; overflow-x: hidden; }
        .wrapper { display: flex; min-height: 100vh; }
        .sidebar { width: 260px; min-width: 260px; flex-shrink: 0; background-color: var(--bg-sidebar); border-right: 1px solid var(--border-color); display: flex; flex-direction: column; padding: 1.5rem; }
        .brand-logo { color: white; font-weight: bold; font-size: 1.2rem; margin-bottom: 2rem; display: flex; align-items: center; gap: 10px; }
        .sidebar-btn { background-color: transparent; color: var(--text-main); border: 1px solid var(--border-color); padding: 10px 15px; border-radius: 8px; margin-bottom: 10px; text-align: left; text-decoration: none;}
        .main-content { flex-grow: 1; padding: 2rem; padding-bottom: 5rem; height: 100vh; overflow-y: auto; }
        .custom-tabs { display: flex; gap: 15px; margin-bottom: 2rem; border-bottom: 1px solid var(--border-color); padding-bottom: 15px; }
        .custom-tab { color: var(--text-muted); text-decoration: none; font-weight: 600; padding: 5px 10px; border-radius: 6px; }
        .custom-tab.active { background-color: var(--bg-card); color: white; border: 1px solid var(--border-color); }
        .dark-card { background-color: var(--bg-card); border-radius: 12px; padding: 1.5rem; border: 1px solid var(--border-color); margin-bottom: 1.5rem; }
        .text-readable { color: #cccccc !important; }
        .form-select-dark { background-color: var(--bg-main); border: 1px solid var(--border-color); color: white !important; cursor: pointer; }
        .form-select-dark:focus { background-color: var(--bg-main); color: white; border-color: var(--accent-purple); box-shadow: 0 0 0 0.25rem rgba(131, 82, 253, 0.25); }
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
            <h4 class="fw-bold mb-1">FitTrack Pro</h4>
            <div class="text-muted small">Halo, <?= htmlspecialchars($display_name) ?> — Rencana Latihan</div>
        </header>

        <nav class="custom-tabs">
            <a href="dashboard.php" class="custom-tab">Dashboard</a>
            <a href="rencana_latihan.php" class="custom-tab active">Rencana Latihan</a>
            <a href="race_prediction.php" class="custom-tab">Race Prediction</a>
            <a href="riwayat_lari.php" class="custom-tab">Riwayat Lari</a>
        </nav>

        <div class="row">
            <div class="col-md-12">
                <div class="dark-card d-flex justify-content-between align-items-center" style="background-color: rgba(131, 82, 253, 0.05); border-color: rgba(131, 82, 253, 0.2);">
                    <div>
                        <h6 class="text-white fw-bold mb-1"><i class="fa-solid fa-bullseye text-primary me-2"></i>Target Program Saat Ini</h6>
                        <p class="text-muted small mb-0">Pilih target latihan atau jenis lomba yang ingin Anda capai.</p>
                    </div>
                    <div>
                        <form method="GET" action="rencana_latihan.php">
                            <select name="program" class="form-select form-select-dark fw-bold" onchange="this.form.submit()">
                                <option value="trail_10k_ekstrem" <?= $selected_key == 'trail_10k_ekstrem' ? 'selected' : '' ?>>Trail 10K (Elevasi > 1500m)</option>
                                <option value="trail_10k_menengah" <?= $selected_key == 'trail_10k_menengah' ? 'selected' : '' ?>>Trail 10K (Elevasi 600m)</option>
                                <option value="road_10k" <?= $selected_key == 'road_10k' ? 'selected' : '' ?>>Road Race 10K (Flat)</option>
                                <option value="road_5k" <?= $selected_key == 'road_5k' ? 'selected' : '' ?>>Road Race 5K (Pemula)</option>
                                <option value="half_marathon" <?= $selected_key == 'half_marathon' ? 'selected' : '' ?>>Half Marathon (21.1K)</option>
                            </select>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="dark-card" style="border-left: 4px solid var(--accent-purple);">
                    <h5 class="fw-bold text-white mb-2"><i class="fa-solid <?= $current_program['icon'] ?> me-2"></i><?= $current_program['title'] ?></h5>
                    <p class="text-readable small mb-0"><?= $current_program['desc'] ?></p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="dark-card">
                    <h6 class="text-white fw-bold"><i class="fa-solid fa-fire text-warning me-2"></i>Fase 1: Base & Strength</h6>
                    <ul class="text-readable small mt-3" style="line-height:1.8;">
                        <?php foreach($current_program['fase1'] as $item): ?>
                            <?php $parts = explode(':', $item, 2); ?>
                            <li><strong class="text-white"><?= $parts[0] ?>:</strong> <?= $parts[1] ?? '' ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <div class="col-md-4">
                <div class="dark-card">
                    <h6 class="text-white fw-bold"><i class="fa-solid fa-person-running text-success me-2"></i>Fase 2: Intensitas & Target</h6>
                    <ul class="text-readable small mt-3" style="line-height:1.8;">
                        <?php foreach($current_program['fase2'] as $item): ?>
                            <?php $parts = explode(':', $item, 2); ?>
                            <li><strong class="text-white"><?= $parts[0] ?>:</strong> <?= $parts[1] ?? '' ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <div class="col-md-4">
                <div class="dark-card">
                    <h6 class="text-white fw-bold"><i class="fa-solid fa-flag-checkered text-info me-2"></i>Fase 3: Tapering (Jelang Lomba)</h6>
                    <ul class="text-readable small mt-3" style="line-height:1.8;">
                        <?php foreach($current_program['fase3'] as $item): ?>
                            <?php $parts = explode(':', $item, 2); ?>
                            <li><strong class="text-white"><?= $parts[0] ?>:</strong> <?= $parts[1] ?? '' ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>