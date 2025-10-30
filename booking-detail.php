<?php
require_once 'config.php';
require_once 'functions.php';

requireUser();

$pageTitle = 'Detail Booking';
$user = getCurrentUser();

// Get booking ID from URL
$bookingId = intval($_GET['id'] ?? 0);
$booking = getBookingById($bookingId);

// Validate booking ownership
if (!$booking || $booking['user_id'] != $user['id']) {
    setFlashMessage('Booking tidak ditemukan', 'error');
    redirect('user-dashboard.php');
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

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.875rem;
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
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="glass-card rounded-2xl p-6 mb-6 slide-up">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <a href="user-dashboard.php" class="p-2 text-slate-400 hover:text-white hover:bg-slate-700 rounded-lg transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </a>
                        <div>
                            <h1 class="text-2xl lg:text-3xl font-bold text-white">Detail Booking</h1>
                            <p class="text-slate-400 text-sm">ID: <?php echo htmlspecialchars($booking['booking_id']); ?></p>
                        </div>
                    </div>
                    <div class="status-badge
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
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Details -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Booking Information -->
                    <div class="glass-card rounded-2xl p-6 slide-up" style="animation-delay: 0.1s;">
                        <h2 class="text-xl font-bold text-white mb-4">Informasi Booking</h2>
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-slate-400 text-sm mb-1">Layanan</label>
                                    <p class="text-white font-semibold"><?php echo ucfirst(str_replace('_', ' ', $booking['service_type'])); ?></p>
                                </div>
                                <div>
                                    <label class="block text-slate-400 text-sm mb-1">Tanggal Booking</label>
                                    <p class="text-white"><?php echo date('d/m/Y H:i', strtotime($booking['booking_date'])); ?></p>
                                </div>
                                <div>
                                    <label class="block text-slate-400 text-sm mb-1">Tanggal Preferred</label>
                                    <p class="text-white"><?php echo date('d/m/Y', strtotime($booking['preferred_date'])); ?></p>
                                </div>
                                <div>
                                    <label class="block text-slate-400 text-sm mb-1">Total Harga</label>
                                    <p class="text-blue-400 font-bold text-lg">Rp <?php echo number_format($booking['total_price'], 0, ',', '.'); ?></p>
                                </div>
                            </div>

                            <div>
                                <label class="block text-slate-400 text-sm mb-1">Model Motor</label>
                                <p class="text-white"><?php echo htmlspecialchars($booking['vehicle_model']); ?> (<?php echo $booking['vehicle_year']; ?>)</p>
                            </div>

                            <?php if ($booking['description']): ?>
                            <div>
                                <label class="block text-slate-400 text-sm mb-1">Deskripsi</label>
                                <p class="text-white"><?php echo nl2br(htmlspecialchars($booking['description'])); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Payment Information -->
                    <?php if ($booking['payment_id']): ?>
                    <div class="glass-card rounded-2xl p-6 slide-up" style="animation-delay: 0.2s;">
                        <h2 class="text-xl font-bold text-white mb-4">Informasi Pembayaran</h2>
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-slate-400 text-sm mb-1">Metode Pembayaran</label>
                                    <p class="text-white font-semibold"><?php echo ucfirst($booking['payment_method']); ?></p>
                                </div>
                                <div>
                                    <label class="block text-slate-400 text-sm mb-1">Nomor Pembayaran</label>
                                    <p class="text-white"><?php echo htmlspecialchars($booking['payment_number']); ?></p>
                                </div>
                                <div>
                                    <label class="block text-slate-400 text-sm mb-1">Jumlah</label>
                                    <p class="text-white font-semibold">Rp <?php echo number_format($booking['amount'], 0, ',', '.'); ?></p>
                                </div>
                                <div>
                                    <label class="block text-slate-400 text-sm mb-1">Status Pembayaran</label>
                                    <span class="status-badge
                                        <?php
                                        switch($booking['payment_status']) {
                                            case 'pending': echo 'bg-yellow-500 bg-opacity-20 text-yellow-400'; break;
                                            case 'verified': echo 'bg-green-500 bg-opacity-20 text-green-400'; break;
                                            case 'rejected': echo 'bg-red-500 bg-opacity-20 text-red-400'; break;
                                        }
                                        ?>">
                                        <?php
                                        switch($booking['payment_status']) {
                                            case 'pending': echo 'Menunggu Verifikasi'; break;
                                            case 'verified': echo 'Terverifikasi'; break;
                                            case 'rejected': echo 'Ditolak'; break;
                                        }
                                        ?>
                                    </span>
                                </div>
                            </div>

                            <?php if ($booking['verified_at']): ?>
                            <div>
                                <label class="block text-slate-400 text-sm mb-1">Diverifikasi Pada</label>
                                <p class="text-white"><?php echo date('d/m/Y H:i', strtotime($booking['verified_at'])); ?></p>
                            </div>
                            <?php endif; ?>

                            <?php if ($booking['notes']): ?>
                            <div>
                                <label class="block text-slate-400 text-sm mb-1">Catatan Admin</label>
                                <div class="bg-slate-700 bg-opacity-50 rounded-xl p-4">
                                    <p class="text-slate-300"><?php echo nl2br(htmlspecialchars($booking['notes'])); ?></p>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if ($booking['proof_image']): ?>
                            <div>
                                <label class="block text-slate-400 text-sm mb-2">Bukti Pembayaran</label>
                                <a href="uploads/<?php echo htmlspecialchars($booking['proof_image']); ?>" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    Lihat Bukti
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Actions Sidebar -->
                <div class="space-y-6">
                    <!-- Quick Actions -->
                    <div class="glass-card rounded-2xl p-6 slide-up" style="animation-delay: 0.3s;">
                        <h3 class="text-lg font-bold text-white mb-4">Aksi Cepat</h3>
                        <div class="space-y-3">
                            <?php if ($booking['status'] === 'pending' && (!$booking['payment_id'] || $booking['payment_status'] === 'rejected')): ?>
                            <a href="payment.php?id=<?php echo $booking['id']; ?>" class="w-full flex items-center gap-3 px-4 py-3 bg-green-500 hover:bg-green-600 text-white rounded-xl transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                Bayar Sekarang
                            </a>
                            <?php endif; ?>

                            <?php if ($booking['payment_status'] === 'pending' && $booking['proof_image']): ?>
                            <a href="upload-proof.php?id=<?php echo $booking['id']; ?>" class="w-full flex items-center gap-3 px-4 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-xl transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                Upload Ulang Bukti
                            </a>
                            <?php endif; ?>

                            <a href="user-dashboard.php" class="w-full flex items-center gap-3 px-4 py-3 bg-slate-600 hover:bg-slate-700 text-white rounded-xl transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"></path>
                                </svg>
                                Kembali ke Dashboard
                            </a>
                        </div>
                    </div>

                    <!-- Contact Info -->
                    <div class="glass-card rounded-2xl p-6 slide-up" style="animation-delay: 0.4s;">
                        <h3 class="text-lg font-bold text-white mb-4">Butuh Bantuan?</h3>
                        <div class="space-y-3">
                            <a href="https://wa.me/6281234567890" target="_blank" class="w-full flex items-center gap-3 px-4 py-3 bg-green-500 hover:bg-green-600 text-white rounded-xl transition">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/>
                                </svg>
                                Hubungi via WhatsApp
                            </a>
                            <p class="text-slate-400 text-sm text-center">
                                Tim support kami siap membantu 24/7
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
