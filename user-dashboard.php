<?php
require_once 'config.php';
require_once 'functions.php';

requireUser();

$pageTitle = 'Dashboard Customer';
$user = getCurrentUser();
$bookings = getBookingsByUser($user['id']);

// Handle logout
if (isset($_GET['logout'])) {
    logoutUser();
    setFlashMessage('Anda telah logout', 'success');
    redirect('user-login.php');
}
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
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .fade-in {
            animation: fadeIn 0.5s ease-out;
        }

        .slide-up {
            animation: slideUp 0.6s ease-out;
        }

        .glass-card {
            background: rgba(30, 41, 59, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(148, 163, 184, 0.1);
        }

        .stat-card {
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .btn-action {
            transition: all 0.2s ease;
        }

        .btn-action:hover {
            transform: scale(1.1);
        }

        .pulse-dot {
            animation: pulse 2s ease-in-out infinite;
        }
    </style>
</head>
<body class="bg-slate-900 min-h-screen fade-in">
    <?php
    $flash = getFlashMessage();
    if ($flash):
    ?>
    <div class="fixed top-6 right-6 z-50 max-w-md">
        <div class="<?php echo $flash['type'] === 'success' ? 'bg-green-500' : 'bg-red-500'; ?> text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-3">
            <?php if ($flash['type'] === 'success'): ?>
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            <?php else: ?>
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            <?php endif; ?>
            <span class="font-semibold"><?php echo htmlspecialchars($flash['message']); ?></span>
        </div>
    </div>
    <script>
        setTimeout(() => {
            const notification = document.querySelector('.fixed.top-6.right-6');
            if (notification) {
                notification.style.opacity = '0';
                setTimeout(() => notification.remove(), 300);
            }
        }, 4000);
    </script>
    <?php endif; ?>

    <div class="p-4 lg:p-8">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="glass-card rounded-2xl p-6 mb-6 slide-up">
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-purple-500 rounded-xl flex items-center justify-center shadow-lg">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-2xl lg:text-3xl font-bold text-white">Dashboard Customer</h1>
                            <p class="text-slate-400 text-sm">Selamat datang, <?php echo htmlspecialchars($user['full_name']); ?></p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <a href="booking.php" class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg font-semibold transition flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Booking Baru
                        </a>
                        <a href="?logout=1" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg font-semibold transition">
                            Logout
                        </a>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <?php
            $stats = [
                'total' => count($bookings),
                'pending' => count(array_filter($bookings, fn($b) => $b['status'] === 'pending')),
                'confirmed' => count(array_filter($bookings, fn($b) => $b['status'] === 'confirmed')),
                'completed' => count(array_filter($bookings, fn($b) => $b['status'] === 'completed'))
            ];
            ?>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="glass-card rounded-2xl p-6 stat-card slide-up" style="animation-delay: 0.1s;">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-400 text-sm font-semibold mb-1">Total Booking</p>
                            <p class="text-3xl font-bold text-white"><?php echo $stats['total']; ?></p>
                        </div>
                        <div class="w-12 h-12 bg-purple-500 bg-opacity-20 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="glass-card rounded-2xl p-6 stat-card slide-up" style="animation-delay: 0.2s;">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-400 text-sm font-semibold mb-1">Menunggu</p>
                            <p class="text-3xl font-bold text-yellow-400"><?php echo $stats['pending']; ?></p>
                        </div>
                        <div class="w-12 h-12 bg-yellow-500 bg-opacity-20 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="glass-card rounded-2xl p-6 stat-card slide-up" style="animation-delay: 0.3s;">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-400 text-sm font-semibold mb-1">Dikonfirmasi</p>
                            <p class="text-3xl font-bold text-blue-400"><?php echo $stats['confirmed']; ?></p>
                        </div>
                        <div class="w-12 h-12 bg-blue-500 bg-opacity-20 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="glass-card rounded-2xl p-6 stat-card slide-up" style="animation-delay: 0.4s;">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-400 text-sm font-semibold mb-1">Selesai</p>
                            <p class="text-3xl font-bold text-green-400"><?php echo $stats['completed']; ?></p>
                        </div>
                        <div class="w-12 h-12 bg-green-500 bg-opacity-20 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bookings Table -->
            <div class="glass-card rounded-2xl p-6 slide-up" style="animation-delay: 0.5s;">
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-6">
                    <h2 class="text-xl font-bold text-white">Riwayat Booking</h2>
                </div>

                <?php if (empty($bookings)): ?>
                    <div class="text-center py-12">
                        <div class="w-16 h-16 bg-slate-700 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <p class="text-slate-400 mb-4">Belum ada booking</p>
                        <a href="booking.php" class="px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-lg font-semibold transition">
                            Buat Booking Pertama
                        </a>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-slate-700">
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase">ID Booking</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase">Layanan</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase">Motor</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase">Tanggal</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase">Pembayaran</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase">Garansi</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bookings as $booking): ?>
                                <tr class="border-b border-slate-700 border-opacity-50 hover:bg-slate-700 hover:bg-opacity-30 transition">
                                    <td class="px-4 py-3 text-sm font-mono text-slate-300"><?php echo htmlspecialchars($booking['booking_id']); ?></td>
                                    <td class="px-4 py-3 text-sm text-white"><?php echo ucfirst(str_replace('_', ' ', $booking['service_type'])); ?></td>
                                    <td class="px-4 py-3 text-sm text-slate-300"><?php echo htmlspecialchars($booking['vehicle_model']); ?> (<?php echo $booking['vehicle_year']; ?>)</td>
                                    <td class="px-4 py-3 text-sm text-slate-300"><?php echo date('d/m/Y', strtotime($booking['preferred_date'])); ?></td>
                                    <td class="px-4 py-3">
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold
                                            <?php
                                            switch($booking['status']) {
                                                case 'pending': echo 'bg-yellow-500 bg-opacity-20 text-yellow-400'; break;
                                                case 'confirmed': echo 'bg-blue-500 bg-opacity-20 text-blue-400'; break;
                                                case 'in_progress': echo 'bg-orange-500 bg-opacity-20 text-orange-400'; break;
                                                case 'completed': echo 'bg-green-500 bg-opacity-20 text-green-400'; break;
                                                case 'cancelled': echo 'bg-red-500 bg-opacity-20 text-red-400'; break;
                                            }
                                            ?>">
                                            <?php
                                            switch($booking['status']) {
                                                case 'pending': echo 'Menunggu'; break;
                                                case 'confirmed': echo 'Dikonfirmasi'; break;
                                                case 'in_progress': echo 'Diproses'; break;
                                                case 'completed': echo 'Selesai'; break;
                                                case 'cancelled': echo 'Dibatalkan'; break;
                                            }
                                            ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <?php if ($booking['payment_status']): ?>
                                            <span class="px-3 py-1 rounded-full text-xs font-semibold
                                                <?php
                                                switch($booking['payment_status']) {
                                                    case 'pending': echo 'bg-yellow-500 bg-opacity-20 text-yellow-400'; break;
                                                    case 'verified': echo 'bg-green-500 bg-opacity-20 text-green-400'; break;
                                                    case 'rejected': echo 'bg-red-500 bg-opacity-20 text-red-400'; break;
                                                }
                                                ?>">
                                                <?php
                                                switch($booking['payment_status']) {
                                                    case 'pending': echo 'Menunggu'; break;
                                                    case 'verified': echo 'Diverifikasi'; break;
                                                    case 'rejected': echo 'Ditolak'; break;
                                                }
                                                ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-slate-500 text-xs">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3">
                                        <?php if ($booking['warranty_id']): ?>
                                            <div class="text-center">
                                                <div class="text-xs font-mono text-slate-300 mb-1"><?php echo htmlspecialchars($booking['warranty_id']); ?></div>
                                                <span class="px-2 py-1 rounded-full text-xs font-semibold
                                                    <?php echo $booking['warranty_active'] ? 'bg-green-500 bg-opacity-20 text-green-400' : 'bg-red-500 bg-opacity-20 text-red-400'; ?>">
                                                    <?php echo $booking['warranty_active'] ? $booking['warranty_days_remaining'] . ' hari' : 'Expired'; ?>
                                                </span>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-slate-500 text-xs">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex gap-2">
                                            <a href="booking-detail.php?id=<?php echo $booking['id']; ?>" class="p-2 text-blue-400 hover:bg-blue-500 hover:bg-opacity-20 rounded-lg transition btn-action" title="Detail">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                            </a>
                                            <?php if ($booking['status'] === 'pending' && (!$booking['payment_status'] || $booking['payment_status'] === 'rejected')): ?>
                                            <a href="payment.php?id=<?php echo $booking['id']; ?>" class="p-2 text-green-400 hover:bg-green-500 hover:bg-opacity-20 rounded-lg transition btn-action" title="Bayar">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                                </svg>
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
