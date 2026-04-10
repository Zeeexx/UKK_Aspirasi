<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['admin'])) { 
    header("location:index.php"); 
    exit();
}

if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header("Location: index.php");
    exit();
}

$total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as jml FROM aspirasi"))['jml'];
$menunggu = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as jml FROM aspirasi WHERE status='Menunggu'"))['jml'];
$proses = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as jml FROM aspirasi WHERE status='Proses'"))['jml'];
$selesai = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as jml FROM aspirasi WHERE status='Selesai'"))['jml'];

$persen_selesai = ($total > 0) ? round(($selesai / $total) * 100) : 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - AspirasiAdmin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <style>
        :root {
            --primary-blue: #4e73df;
            --sidebar-width: 260px;
            --bg-light: #f8f9fc;
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: var(--bg-light);
            margin: 0;
            color: #1e293b;
            overflow-x: hidden;
        }

        /* --- SIDEBAR STYLE --- */
.sidebar {
    width: var(--sidebar-width);
    height: 100vh;
    position: fixed;
    top: 0; /* Pastikan menempel ke paling atas */
    left: 0;
    background: white;
    display: flex;
    flex-direction: column;
    padding: 30px 20px;
    border-right: 1px solid #edf2f7;
    z-index: 1100; /* Harus lebih tinggi dari mobile-header */
    transition: transform 0.3s ease-in-out;
}

/* --- MOBILE NAVIGATION --- */
.mobile-header {
    display: none;
    background: white;
    padding: 15px 20px;
    border-bottom: 1px solid #edf2f7;
    position: sticky;
    top: 0;
    z-index: 1000; /* Di bawah sidebar agar sidebar bisa menutupinya saat terbuka */
}

/* Tambahkan ini untuk memastikan overlay menutupi seluruh layar */
.sidebar-overlay {
    display: none;
    position: fixed;
    top: 0; 
    left: 0; 
    right: 0; 
    bottom: 0;
    background: rgba(0,0,0,0.4); /* Gelapkan sedikit agar fokus ke sidebar */
    backdrop-filter: blur(4px);
    z-index: 1050;
}

        .brand-logo {
            display: flex;
            align-items: center;
            font-weight: 700;
            font-size: 1.2rem;
            color: #1a73e8;
            text-decoration: none;
            margin-bottom: 40px;
            padding-left: 10px;
        }

        /* --- SEMUA ANIMASI ASLI --- */
        @keyframes feather-float {
            0% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-4px) rotate(10deg); }
            100% { transform: translateY(0) rotate(0deg); }
        }
        .feather-anim { display: inline-block; animation: feather-float 3s ease-in-out infinite; }

        @keyframes fa-spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(359deg); }
        }
        .fa-spin-fast { animation: fa-spin 1s infinite linear; }

        @keyframes pulse-slow {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        .animate-pulse-slow { animation: pulse-slow 3s infinite; }

        /* --- NAV STYLE --- */
        .nav-link {
            color: #718096;
            font-weight: 600;
            padding: 12px 15px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
            text-decoration: none;
            margin-bottom: 5px;
        }
        .nav-link i { margin-right: 12px; width: 20px; text-align: center; }
        .nav-link:hover { background: #f7fafc; color: var(--primary-blue); }
        .nav-link.active {
            background: #6366f1;
            color: white !important;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
        }
        .logout-link { color: #ef4444 !important; background: #fef2f2; margin-top: 10px; }

        /* --- MAIN CONTENT RESPONSIVE --- */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 40px;
            transition: margin 0.3s ease-in-out;
        }

        .welcome-banner {
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            border-radius: 30px;
            padding: 40px;
            color: white;
            box-shadow: 0 15px 30px rgba(99, 102, 241, 0.2);
        }

        .glass-card {
            background: white;
            border-radius: 25px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.02);
            border: 1px solid #f1f5f9;
            transition: all 0.3s ease;
            height: 100%;
        }
        .glass-card:hover { transform: translateY(-5px); box-shadow: 0 12px 25px rgba(0,0,0,0.05); }

        .stat-icon {
            width: 55px; height: 55px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 18px; font-size: 22px; margin-bottom: 20px;
            transition: all 0.5s ease;
        }
        .glass-card:hover .stat-icon { transform: scale(1.1) rotate(10deg); }

        

        /* --- RESPONSIVE LOGIC --- */
        @media (max-width: 992px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0; padding: 20px; }
            .mobile-header { display: flex; justify-content: space-between; align-items: center; }
            .sidebar-overlay.show { display: block; }
            .welcome-banner { padding: 30px 20px; border-radius: 20px; }
        }

        .progress-container {
            background: white;
            border-radius: 25px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.02);
        }
    </style>
</head>
<body>

<div class="sidebar-overlay" id="overlay"></div>

<div class="mobile-header shadow-sm">
    <a href="#" class="fw-bold text-primary text-decoration-none">
        <i class="fas fa-feather-alt me-2"></i> Aspirasi
    </a>
    <button class="btn btn-primary" id="btnToggle">
        <i class="fas fa-bars"></i>
    </button>
</div>

<div class="sidebar shadow-sm" id="sidebar">
    <a href="#" class="brand-logo">
        <i class="fas fa-feather-alt me-2 feather-anim"></i> AspirasiAdmin
    </a>
    <nav class="nav flex-column">
        <a href="admin_dashboard.php" class="nav-link active">
            <i class="fas fa-th-large"></i> Dashboard
        </a>
        <a href="data_siswa.php" class="nav-link">
            <i class="fas fa-user-graduate"></i> Data Siswa
        </a>
        <a href="laporan.php" class="nav-link">
            <i class="fas fa-file-alt"></i> Laporan Masuk
        </a>
        <hr style="border-color: #edf2f7; border-width: 2px; margin: 15px 10px;">
        <a href="admin_dashboard.php?action=logout" class="nav-link logout-link">
            <i class="fas fa-power-off"></i> Keluar
        </a>
    </nav>
</div>

<div class="main-content">
    <div class="welcome-banner animate__animated animate__fadeInDown mb-5">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="fw-bold mb-2">Selamat Datang, Admin! ✨</h1>
                <p class="opacity-75 mb-4">Sistem berjalan normal. Ada <b><?= $menunggu ?> laporan baru</b>.</p>
                <a href="laporan.php" class="btn btn-light fw-bold rounded-pill px-4 shadow-sm text-primary py-2">
                    Cek Laporan <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-6 col-md-3">
            <div class="glass-card animate__animated animate__fadeInUp" style="animation-delay: 0.1s;">
                <div class="stat-icon bg-primary text-white shadow-sm animate-pulse-slow">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div class="small fw-bold text-muted text-uppercase mb-1" style="font-size: 0.7rem;">Total Aspirasi</div>
                <h2 class="fw-bold mb-0"><?= $total ?></h2>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="glass-card animate__animated animate__fadeInUp" style="animation-delay: 0.2s;">
                <div class="stat-icon bg-warning text-white shadow-sm">
                    <i class="fas fa-clock fa-spin" style="--fa-animation-duration: 5s;"></i>
                </div>
                <div class="small fw-bold text-muted text-uppercase mb-1" style="font-size: 0.7rem;">Menunggu</div>
                <h2 class="fw-bold mb-0"><?= $menunggu ?></h2>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="glass-card animate__animated animate__fadeInUp" style="animation-delay: 0.3s;">
                <div class="stat-icon bg-info text-white shadow-sm">
                    <i class="fas fa-spinner fa-spin-fast"></i>
                </div>
                <div class="small fw-bold text-muted text-uppercase mb-1" style="font-size: 0.7rem;">Proses</div>
                <h2 class="fw-bold mb-0"><?= $proses ?></h2>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="glass-card animate__animated animate__fadeInUp" style="animation-delay: 0.4s;">
                <div class="stat-icon bg-success text-white shadow-sm">
                    <i class="fas fa-check-double animate__animated animate__heartBeat animate__infinite"></i>
                </div>
                <div class="small fw-bold text-muted text-uppercase mb-1" style="font-size: 0.7rem;">Selesai</div>
                <h2 class="fw-bold mb-0"><?= $selesai ?></h2>
            </div>
        </div>
    </div>

    <div class="progress-container animate__animated animate__fadeInUp" style="animation-delay: 0.5s;">
        <h5 class="fw-bold mb-4">Penyelesaian Laporan</h5>
        <div class="d-flex justify-content-between mb-2">
            <span class="text-muted small fw-bold">Target Selesai</span>
            <span class="fw-bold text-primary"><?= $persen_selesai ?>%</span>
        </div>
        <div class="progress" style="height: 14px; border-radius: 20px; background: #f1f5f9;">
            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" 
                 style="width: <?= $persen_selesai ?>%; background: #6366f1;"></div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const btnToggle = document.getElementById('btnToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');

    btnToggle.addEventListener('click', () => {
        sidebar.classList.add('show');
        overlay.classList.add('show');
    });

    overlay.addEventListener('click', () => {
        sidebar.classList.remove('show');
        overlay.classList.remove('show');
    });
</script>
</body>
</html>