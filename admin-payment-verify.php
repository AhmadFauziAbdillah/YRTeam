<?php
require_once 'config.php';
require_once 'functions.php';

requireAdmin();

$pageTitle = 'Verifikasi Pembayaran';
$admin = getCurrentAdmin();

// Get booking ID from URL
$bookingId = intval($_GET['id'] ?? 0);
$booking = getBookingById($bookingId);

// Validate booking exists and has pending payment
if (!$booking || $booking['payment_status'] !== 'pending' || !$booking['proof_image']) {
    setFlashMessage('Booking tidak valid untuk verifikasi pembayaran', 'error');
    redirect('admin-bookings.php');
}

// Handle verification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $notes = sanitize($_POST['notes'] ?? '');

    if ($action === 'verify') {
        $result = verifyPayment($booking['payment_id'], 'verified', $notes);
    } elseif ($action === 'reject') {
        $result = verifyPayment($booking['payment_id'], 'rejected', $notes);
    } else {
        $result = ['success' => false, 'message' => 'Aksi tidak valid'];
    }

    if ($result['success']) {
        setFlashMessage($result['message'], 'success');
        redirect('admin-bookings.php');
    } else {
        setFlashMessage($result['message'], 'error');
    }
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

        .proof-image {
            max-width: 100%;
            max-height: 500px;
            object-fit: contain;
            border-radius: 0.75rem;
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
                        <a href="admin-bookings.php" class="p-2 text-slate-400 hover:text-white hover:bg-slate-700 rounded-lg transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </a>
                        <div>
                            <h1 class="text-2xl lg:text-3xl font-bold text-white">Verifikasi Pembayaran</h1>
                            <p class="text-slate-400 text-sm">Booking ID: <?php echo htmlspecialchars($booking['booking_id']); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Payment Proof Image -->
                <div class="glass-card rounded-2xl p-6 slide-up" style="animation-delay: 0.1s;">
                    <h2 class="text-xl font-bold text-white mb-4">Bukti Pembayaran</h2>
                    <div class="space-y-4">
                        <div class="bg-slate-700 bg-opacity-50 rounded-xl p-4 flex justify-center">
                            <img src="uploads/<?php echo htmlspecialchars($booking['proof_image']); ?>" alt="Bukti Pembayaran" class="proof-image">
                        </div>
                        <div class="text-center">
                            <a href="uploads/<?php echo htmlspecialchars($booking['proof_image']); ?>" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                </svg>
                                Buka di Tab Baru
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Payment Details & Actions -->
                <div class="space-y-6">
                    <!-- Payment Information -->
                    <div class="glass-card rounded-2xl p-6 slide-up" style="animation-delay: 0.2s;">
                        <h2 class="text-xl font-bold text-white mb-4">Detail Pembayaran</h2>
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <label class="block text-slate-400 text-sm mb-1">Customer</label>
                                    <p class="text-white font-semibold"><?php echo htmlspecialchars($booking['full_name']); ?></p>
                                </div>
                                <div>
                                    <label class="block text-slate-400 text-sm mb-1">Metode Pembayaran</label>
                                    <p class="text-white"><?php echo ucfirst($booking['payment_method']); ?></p>
                                </div>
                                <div>
                                    <label class="block text-slate-400 text-sm mb-1">Nomor Pembayaran</label>
                                    <p class="text-white font-mono"><?php echo htmlspecialchars($booking['payment_number']); ?></p>
                                </div>
                                <div>
                                    <label class="block text-slate-400 text-sm mb-1">Jumlah</label>
                                    <p class="text-blue-400 font-bold text-xl">Rp <?php echo number_format($booking['amount'], 0, ',', '.'); ?></p>
                                </div>
                                <div>
                                    <label class="block text-slate-400 text-sm mb-1">Tanggal Upload</label>
                                    <p class="text-white"><?php echo date('d/m/Y H:i', strtotime($booking['proof_uploaded_at'])); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Verification Actions -->
                    <div class="glass-card rounded-2xl p-6 slide-up" style="animation-delay: 0.3s;">
                        <h2 class="text-xl font-bold text-white mb-4">Aksi Verifikasi</h2>

                        <?php if (!empty($errors)): ?>
                        <div class="mb-6 p-4 bg-red-500 bg-opacity-10 border border-red-500 border-opacity-30 rounded-xl">
                            <div class="flex items-center gap-2 mb-2">
                                <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="text-red-400 font-semibold">Error</span>
                            </div>
                            <ul class="text-red-300 text-sm space-y-1">
                                <?php foreach ($errors as $error): ?>
                                <li>• <?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>

                        <form method="POST" class="space-y-4">
                            <div>
                                <label class="block text-slate-300 text-sm font-semibold mb-2">Catatan (Opsional)</label>
                                <textarea
                                    name="notes"
                                    rows="3"
                                    placeholder="Tambahkan catatan untuk customer..."
                                    class="w-full px-4 py-3 bg-slate-700 bg-opacity-50 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition resize-none"
                                ><?php echo isset($_POST['notes']) ? htmlspecialchars($_POST['notes']) : ''; ?></textarea>
                            </div>

                            <div class="flex gap-3">
                                <button
                                    type="submit"
                                    name="action"
                                    value="verify"
                                    class="flex-1 px-6 py-3 bg-green-500 hover:bg-green-600 text-white rounded-xl font-semibold transition"
                                >
                                    ✓ Verifikasi Pembayaran
                                </button>
                                <button
                                    type="submit"
                                    name="action"
                                    value="reject"
                                    class="flex-1 px-6 py-3 bg-red-500 hover:bg-red-600 text-white rounded-xl font-semibold transition"
                                    onclick="return confirm('Apakah Anda yakin ingin menolak pembayaran ini?')"
                                >
                                    ✗ Tolak Pembayaran
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Quick Actions -->
                    <div class="glass-card rounded-2xl p-6 slide-up" style="animation-delay: 0.4s;">
                        <h3 class="text-lg font-bold text-white mb-4">Aksi Cepat</h3>
                        <div class="space-y-3">
                            <a href="admin-booking-detail.php?id=<?php echo $booking['id']; ?>" class="w-full flex items-center gap-3 px-4 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-xl transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                Lihat Detail Booking
                            </a>
                            <a href="admin-bookings.php" class="w-full flex items-center gap-3 px-4 py-3 bg-slate-600 hover:bg-slate-700 text-white rounded-xl transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                                </svg>
                                Kembali ke Daftar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
