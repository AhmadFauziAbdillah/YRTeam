<?php
require_once 'config.php';
require_once 'functions.php';

$pageTitle = 'YR Team - Remap ECU Motor';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        @keyframes drawPath {
            to { stroke-dashoffset: 0; }
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .fade-in {
            animation: fadeIn 0.8s ease-out;
        }

        .slide-up {
            animation: slideUp 0.6s ease-out;
        }

        .float-element {
            animation: float 6s ease-in-out infinite;
        }

        .animated-path {
            stroke-dasharray: 1000;
            stroke-dashoffset: 1000;
            animation: drawPath 3s ease-out forwards;
        }

        .glass-morphism {
            background: rgba(45, 52, 70, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .service-card {
            transition: all 0.3s ease;
        }

        .service-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.4);
        }

        .btn-secondary {
            background: rgba(71, 85, 105, 0.8);
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background: rgba(71, 85, 105, 1);
        }

        .logo-pulse {
            animation: pulse 2s ease-in-out infinite;
        }

        .bg-pattern {
            background-image:
                radial-gradient(circle at 20% 30%, rgba(59, 130, 246, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(99, 102, 241, 0.1) 0%, transparent 50%);
        }
    </style>
</head>
<body class="bg-slate-900 overflow-x-hidden">
    <!-- Navigation -->
    <nav class="fixed top-0 w-full z-50 bg-slate-900 bg-opacity-95 backdrop-blur-md border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center logo-pulse">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <span class="text-white text-xl font-bold">YR Team</span>
                </div>
                <div class="hidden md:flex items-center gap-6">
                    <a href="#home" class="text-slate-300 hover:text-white transition">Beranda</a>
                    <a href="#warranty" class="text-slate-300 hover:text-white transition">Cek Garansi</a>
                    <a href="#services" class="text-slate-300 hover:text-white transition">Layanan</a>
                    <a href="#about" class="text-slate-300 hover:text-white transition">Tentang</a>
                    <a href="#contact" class="text-slate-300 hover:text-white transition">Kontak</a>
                    <a href="user-login.php" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg font-semibold transition">Login</a>
                </div>
                <div class="md:hidden">
                    <button class="text-white" id="mobile-menu-btn">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="min-h-screen flex items-center justify-center p-4 bg-pattern relative">
        <!-- Decorative SVG Path -->
        <svg class="absolute inset-0 w-full h-full opacity-10" xmlns="http://www.w3.org/2000/svg">
            <path class="animated-path" d="M 0,400 Q 250,300 500,400 T 1000,400" stroke="rgba(59, 130, 246, 0.5)" stroke-width="2" fill="none"/>
            <path class="animated-path" d="M 0,500 Q 250,450 500,500 T 1000,500" stroke="rgba(99, 102, 241, 0.5)" stroke-width="2" fill="none" style="animation-delay: 0.5s;"/>
        </svg>

        <!-- Floating Elements -->
        <div class="absolute top-20 left-10 opacity-20 float-element hidden lg:block">
            <svg width="100" height="100" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="50" cy="50" r="40" stroke="#64748b" stroke-width="2"/>
                <path d="M30 50 L45 35 L70 60" stroke="#64748b" stroke-width="3" stroke-linecap="round"/>
            </svg>
        </div>

        <div class="absolute bottom-20 right-10 opacity-20 float-element hidden lg:block" style="animation-delay: 2s;">
            <svg width="120" height="120" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="20" y="20" width="80" height="80" rx="10" stroke="#64748b" stroke-width="2"/>
                <circle cx="60" cy="60" r="20" stroke="#64748b" stroke-width="2"/>
                <path d="M45 60 L55 50 L75 70" stroke="#64748b" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </div>

        <div class="w-full max-w-4xl relative z-10">
            <div class="text-center fade-in">
                <h1 class="text-5xl lg:text-7xl font-bold text-white mb-6">
                    Remap<span class="text-blue-500">.</span> ECU
                </h1>
                <p class="text-xl lg:text-2xl text-slate-300 mb-8 max-w-2xl mx-auto">
                    Optimalkan performa motor dengan remapping ECU YR Team
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="user-register.php" class="px-8 py-4 btn-primary text-white rounded-xl font-semibold shadow-lg">
                        Daftar Sekarang
                    </a>
                    <a href="#services" class="px-8 py-4 btn-secondary text-white rounded-xl font-semibold">
                        Lihat Layanan
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Warranty Check Section -->
    <section id="warranty" class="py-20 px-4 bg-slate-800 bg-opacity-50">
        <div class="max-w-4xl mx-auto">
            <div class="text-center mb-16 slide-up">
                <h2 class="text-4xl lg:text-5xl font-bold text-white mb-6">
                    Cek<span class="text-blue-500">.</span> Garansi
                </h2>
                <p class="text-xl text-slate-300 max-w-2xl mx-auto">
                    Periksa status garansi remap ECU Anda dengan mudah
                </p>
            </div>

            <div class="glass-morphism rounded-2xl p-8 slide-up" style="animation-delay: 0.1s;">
                <form method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-white font-semibold mb-2">ID Garansi</label>
                            <input type="text" name="warranty_id" placeholder="Masukkan ID Garansi (contoh: ECU-ABC123-XYZ)"
                                   class="w-full px-4 py-3 bg-slate-700 border border-slate-600 rounded-lg text-white placeholder-slate-400 focus:outline-none focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-white font-semibold mb-2">Atau Nomor HP</label>
                            <input type="text" name="phone" placeholder="Masukkan nomor HP (contoh: 081234567890)"
                                   class="w-full px-4 py-3 bg-slate-700 border border-slate-600 rounded-lg text-white placeholder-slate-400 focus:outline-none focus:border-blue-500">
                        </div>
                    </div>
                    <div class="text-center">
                        <button type="submit" name="check_warranty"
                                class="px-8 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-lg font-semibold transition">
                            Cek Status Garansi
                        </button>
                    </div>
                </form>

                <?php
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['check_warranty'])) {
                    $warranty = null;

                    if (!empty($_POST['warranty_id'])) {
                        $warranty = getWarrantyById(trim($_POST['warranty_id']));
                    } elseif (!empty($_POST['phone'])) {
                        $warranty = getWarrantyByPhone(normalizePhone(trim($_POST['phone'])));
                    }

                    if ($warranty) {
                        echo '<div class="mt-8 p-6 bg-slate-700 rounded-lg border border-slate-600">';
                        echo '<h3 class="text-xl font-bold text-white mb-4">Hasil Pengecekan Garansi</h3>';
                        echo '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">';
                        echo '<div>';
                        echo '<p class="text-slate-400 text-sm">ID Garansi</p>';
                        echo '<p class="text-white font-mono">' . htmlspecialchars($warranty['id']) . '</p>';
                        echo '</div>';
                        echo '<div>';
                        echo '<p class="text-slate-400 text-sm">Nama</p>';
                        echo '<p class="text-white">' . htmlspecialchars($warranty['nama']) . '</p>';
                        echo '</div>';
                        echo '<div>';
                        echo '<p class="text-slate-400 text-sm">Model Motor</p>';
                        echo '<p class="text-white">' . htmlspecialchars($warranty['model']) . '</p>';
                        echo '</div>';
                        echo '<div>';
                        echo '<p class="text-slate-400 text-sm">Tanggal Registrasi</p>';
                        echo '<p class="text-white">' . date('d/m/Y', strtotime($warranty['registration_date'])) . '</p>';
                        echo '</div>';
                        echo '<div>';
                        echo '<p class="text-slate-400 text-sm">Masa Berlaku</p>';
                        echo '<p class="text-white">' . $warranty['warranty_days'] . ' Hari</p>';
                        echo '</div>';
                        echo '<div>';
                        echo '<p class="text-slate-400 text-sm">Berlaku s/d</p>';
                        echo '<p class="text-white">' . date('d/m/Y', strtotime($warranty['expiry_date'])) . '</p>';
                        echo '</div>';
                        echo '</div>';

                        echo '<div class="mt-6 p-4 rounded-lg ';
                        if ($warranty['is_active']) {
                            echo 'bg-green-500 bg-opacity-20 border border-green-500">';
                            echo '<div class="flex items-center gap-3">';
                            echo '<svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
                            echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
                            echo '</svg>';
                            echo '<div>';
                            echo '<p class="text-green-400 font-semibold">GARANSI AKTIF</p>';
                            echo '<p class="text-green-300 text-sm">Sisa waktu: ' . $warranty['days_remaining'] . ' hari</p>';
                            echo '</div>';
                        } else {
                            echo 'bg-red-500 bg-opacity-20 border border-red-500">';
                            echo '<div class="flex items-center gap-3">';
                            echo '<svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
                            echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
                            echo '</svg>';
                            echo '<div>';
                            echo '<p class="text-red-400 font-semibold">GARANSI EXPIRED</p>';
                            echo '<p class="text-red-300 text-sm">Garansi telah berakhir</p>';
                            echo '</div>';
                        }
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                    } else {
                        echo '<div class="mt-8 p-6 bg-red-500 bg-opacity-20 border border-red-500 rounded-lg">';
                        echo '<div class="flex items-center gap-3">';
                        echo '<svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
                        echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
                        echo '</svg>';
                        echo '<p class="text-red-300">Data garansi tidak ditemukan. Pastikan ID Garansi atau nomor HP sudah benar.</p>';
                        echo '</div>';
                        echo '</div>';
                    }
                }
                ?>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="py-20 px-4">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16 slide-up">
                <h2 class="text-4xl lg:text-5xl font-bold text-white mb-6">
                    Layanan<span class="text-blue-500">.</span> Kami
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-8">
                <!-- Remap ECU -->
                <div class="service-card glass-morphism rounded-2xl p-8 slide-up" style="animation-delay: 0.1s;">
                    <div class="text-center">
                        <div class="w-20 h-20 bg-blue-500 bg-opacity-20 rounded-full flex items-center justify-center mx-auto mb-6">
                            <svg class="w-10 h-10 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-white mb-4">Remap ECU</h3>
                        <p class="text-slate-300 mb-6">
                            Optimalisasi performa mesin motor melalui remapping ECU dengan teknologi terkini
                        </p>
                        <div class="text-3xl font-bold text-blue-400 mb-4">Rp 300.000</div>
                        <a href="user-register.php" class="inline-block px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-lg font-semibold transition">
                            Pesan Sekarang
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-20 px-4">
        <div class="max-w-4xl mx-auto text-center">
            <div class="slide-up">
                <h2 class="text-4xl lg:text-5xl font-bold text-white mb-6">
                    Hubungi<span class="text-blue-500">.</span> Kami
                </h2>
                <p class="text-xl text-slate-300 mb-12">
                    Siap untuk meningkatkan performa motor Anda? Hubungi kami sekarang!
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 slide-up" style="animation-delay: 0.2s;">
                <div class="glass-morphism rounded-2xl p-6">
                    <div class="w-16 h-16 bg-green-500 bg-opacity-20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-400" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">WhatsApp</h3>
                    <p class="text-slate-300 mb-4">Chat langsung dengan tim kami</p>
                    <a href="https://wa.me/62859106545737" target="_blank" class="inline-block px-6 py-3 bg-green-500 hover:bg-green-600 text-white rounded-lg font-semibold transition">
                        Hubungi WhatsApp
                    </a>
                </div>

                <div class="glass-morphism rounded-2xl p-6">
                    <div class="w-16 h-16 bg-blue-500 bg-opacity-20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Email</h3>
                    <p class="text-slate-300 mb-4">Kirim email untuk informasi detail</p>
                    <a href="mailto:fauji1543@gmail.com" class="inline-block px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-lg font-semibold transition">
                        Kirim Email
                    </a>
                </div>

                <div class="glass-morphism rounded-2xl p-6">
                    <div class="w-16 h-16 bg-purple-500 bg-opacity-20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Lokasi</h3>
                    <p class="text-slate-300 mb-4">Kunjungi workshop kami</p>
                    <a href="#" class="inline-block px-6 py-3 bg-purple-500 hover:bg-purple-600 text-white rounded-lg font-semibold transition">
                        Lihat Lokasi
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-slate-900 border-t border-slate-800 py-12 px-4">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="md:col-span-2">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </div>
                        <span class="text-white text-xl font-bold">YR Team</span>
                    </div>
                    <p class="text-slate-300 mb-4">
                        Remap Ecu.
                    </p>
                    <p class="text-slate-400 text-sm">
                        © 2025 YR Team. All rights reserved.
                    </p>
                </div>

                <div>
                    <h4 class="text-white font-semibold mb-4">Layanan</h4>
                    <ul class="space-y-2 text-slate-300">
                        <li><a href="#services" class="hover:text-white transition">Remap ECU</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-white font-semibold mb-4">Akun</h4>
                    <ul class="space-y-2 text-slate-300">
                        <li><a href="user-login.php" class="hover:text-white transition">Login</a></li>
                        <li><a href="user-register.php" class="hover:text-white transition">Daftar</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <!-- Mobile Menu -->
    <div class="fixed inset-0 bg-slate-900 bg-opacity-95 z-50 hidden" id="mobile-menu">
        <div class="flex flex-col items-center justify-center h-full">
            <button class="absolute top-6 right-6 text-white" id="close-mobile-menu">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
                <nav class="text-center">
                    <a href="#home" class="block text-2xl text-white py-4 hover:text-blue-400 transition">Beranda</a>
                    <a href="#warranty" class="block text-2xl text-white py-4 hover:text-blue-400 transition">Cek Garansi</a>
                    <a href="#services" class="block text-2xl text-white py-4 hover:text-blue-400 transition">Layanan</a>
                    <a href="#about" class="block text-2xl text-white py-4 hover:text-blue-400 transition">Tentang</a>
                    <a href="#contact" class="block text-2xl text-white py-4 hover:text-blue-400 transition">Kontak</a>
                    <a href="user-login.php" class="block text-2xl text-blue-400 py-4 font-semibold">Login</a>
                </nav>
        </div>
    </div>

    <script>
        // Mobile menu toggle
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        const closeMobileMenu = document.getElementById('close-mobile-menu');

        mobileMenuBtn.addEventListener('click', () => {
            mobileMenu.classList.remove('hidden');
        });

        closeMobileMenu.addEventListener('click', () => {
            mobileMenu.classList.add('hidden');
        });

        // Smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                    mobileMenu.classList.add('hidden');
                }
            });
        });

        // Intersection Observer for animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe all slide-up elements
        document.querySelectorAll('.slide-up').forEach(el => {
            observer.observe(el);
        });
    </script>
</body>
</html>
