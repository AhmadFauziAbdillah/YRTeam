<?php
require_once 'config.php';
require_once 'functions.php';

requireAdmin();

$pageTitle = 'Kelola Bookings';
$admin = getCurrentAdmin();

// Pagination
$page = intval($_GET['page'] ?? 1);
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Filters
$status = $_GET['status'] ?? '';
$search = trim($_GET['search'] ?? '');

// Get bookings
$bookings = getAllBookings($status, $search);
$totalBookings = count($bookings);
$totalPages = ceil($totalBookings / $perPage);

// Slice for pagination
$paginatedBookings = array_slice($bookings, $offset, $perPage);

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $bookingId = intval($_POST['booking_id']);
    $newStatus = sanitize($_POST['status']);

    $result = updateBookingStatus($bookingId, $newStatus);

    if ($result['success']) {
        setFlashMessage($result['message'], 'success');
    } else {
        setFlashMessage($result['message'], 'error');
    }

    redirect('admin-bookings.php' . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''));
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
            padding: 0.25rem 0.75rem;
            border-radius: 0.375rem;
            font-weight: 600;
            font-size: 0.75rem;
        }

        .btn-action {
            transition: all 0.2s ease;
        }

        .btn-action:hover {
            transform: scale(1.1);
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
                        <a href="admin.php" class="p-2 text-slate-400 hover:text-white hover:bg-slate-700 rounded-lg transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </a>
                        <div>
                            <h1 class="text-2xl lg:text-3xl font-bold text-white">Kelola Bookings</h1>
                            <p class="text-slate-400 text-sm">Pantau dan kelola semua booking pelanggan</p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <a href="admin.php" class="px-4 py-2 bg-slate-600 hover:bg-slate-700 text-white rounded-lg font-semibold transition">
                            Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="glass-card rounded-2xl p-6 mb-6 slide-up" style="animation-delay: 0.1s;">
                <form method="GET" class="flex flex-col lg:flex-row gap-4">
                    <div class="flex-1">
                        <label class="block text-slate-300 text-sm font-semibold mb-2">Cari Booking</label>
                        <input
                            type="text"
                            name="search"
                            placeholder="Cari berdasarkan ID booking, nama, nomor HP, atau model motor..."
                            value="<?php echo htmlspecialchars($search); ?>"
                            class="w-full px-4 py-3 bg-slate-700 bg-opacity-50 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                        />
                    </div>
                    <div class="lg:w-48">
                        <label class="block text-slate-300 text-sm font-semibold mb-2">Status</label>
                        <select name="status" class="w-full px-4 py-3 bg-slate-700 bg-opacity-50 border border-slate-600 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                            <option value="">Semua Status</option>
                            <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Menunggu</option>
                            <option value="confirmed" <?php echo $status === 'confirmed' ? 'selected' : ''; ?>>Dikonfirmasi</option>
                            <option value="in_progress" <?php echo $status === 'in_progress' ? 'selected' : ''; ?>>Diproses</option>
                            <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Selesai</option>
                            <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Dibatalkan</option>
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-xl font-semibold transition">
                            Filter
                        </button>
                        <?php if ($status || $search): ?>
                        <a href="admin-bookings.php" class="px-6 py-3 bg-slate-600 hover:bg-slate-700 text-white rounded-xl font-semibold transition">
                            Reset
                        </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Bookings Table -->
            <div class="glass-card rounded-2xl p-6 slide-up" style="animation-delay: 0.2s;">
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-6">
                    <h2 class="text-xl font-bold text-white">Daftar Booking (<?php echo $totalBookings; ?>)</h2>
                </div>

                <?php if (empty($paginatedBookings)): ?>
                    <div class="text-center py-12">
                        <div class="w-16 h-16 bg-slate-700 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <p class="text-slate-400 mb-4">Tidak ada booking ditemukan</p>
                        <a href="admin-bookings.php" class="px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-lg font-semibold transition">
                            Reset Filter
                        </a>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-slate-700">
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase">ID Booking</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase">Customer</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase">Layanan</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase">Motor</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase">Tanggal</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase">Pembayaran</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($paginatedBookings as $booking): ?>
                                <tr class="border-b border-slate-700 border-opacity-50 hover:bg-slate-700 hover:bg-opacity-30 transition">
                                    <td class="px-4 py-3 text-sm font-mono text-slate-300"><?php echo htmlspecialchars($booking['booking_id']); ?></td>
                                    <td class="px-4 py-3 text-sm">
                                        <div>
                                            <div class="text-white font-semibold"><?php echo htmlspecialchars($booking['full_name']); ?></div>
                                            <div class="text-slate-400 text-xs"><?php echo htmlspecialchars($booking['phone']); ?></div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-white"><?php echo ucfirst(str_replace('_', ' ', $booking['service_type'])); ?></td>
                                    <td class="px-4 py-3 text-sm text-slate-300"><?php echo htmlspecialchars($booking['vehicle_model']); ?> (<?php echo $booking['vehicle_year']; ?>)</td>
                                    <td class="px-4 py-3 text-sm text-slate-300"><?php echo date('d/m/Y', strtotime($booking['preferred_date'])); ?></td>
                                    <td class="px-4 py-3">
                                        <form method="POST" class="inline-block">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                            <select name="status" onchange="this.form.submit()" class="bg-slate-700 border border-slate-600 rounded px-2 py-1 text-xs text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                                                <option value="pending" <?php echo $booking['status'] === 'pending' ? 'selected' : ''; ?>>Menunggu</option>
                                                <option value="confirmed" <?php echo $booking['status'] === 'confirmed' ? 'selected' : ''; ?>>Dikonfirmasi</option>
                                                <option value="in_progress" <?php echo $booking['status'] === 'in_progress' ? 'selected' : ''; ?>>Diproses</option>
                                                <option value="completed" <?php echo $booking['status'] === 'completed' ? 'selected' : ''; ?>>Selesai</option>
                                                <option value="cancelled" <?php echo $booking['status'] === 'cancelled' ? 'selected' : ''; ?>>Dibatalkan</option>
                                            </select>
                                            <input type="hidden" name="update_status" value="1">
                                        </form>
                                    </td>
                                    <td class="px-4 py-3">
                                        <?php if ($booking['payment_status']): ?>
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
                                                    case 'pending': echo 'Menunggu'; break;
                                                    case 'verified': echo 'Verified'; break;
                                                    case 'rejected': echo 'Rejected'; break;
                                                }
                                                ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-slate-500 text-xs">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex gap-2">
                                            <a href="admin-booking-detail.php?id=<?php echo $booking['id']; ?>" class="p-2 text-blue-400 hover:bg-blue-500 hover:bg-opacity-20 rounded-lg transition btn-action" title="Detail">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                            </a>
                                            <?php if ($booking['payment_status'] === 'pending' && $booking['proof_image']): ?>
                                            <a href="admin-payment-verify.php?id=<?php echo $booking['id']; ?>" class="p-2 text-green-400 hover:bg-green-500 hover:bg-opacity-20 rounded-lg transition btn-action" title="Verifikasi Pembayaran">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
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

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                    <div class="flex justify-center mt-6">
                        <div class="flex gap-2">
                            <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?><?php echo $status ? '&status=' . urlencode($status) : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="px-3 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </a>
                            <?php endif; ?>

                            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <a href="?page=<?php echo $i; ?><?php echo $status ? '&status=' . urlencode($status) : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="px-3 py-2 <?php echo $i === $page ? 'bg-blue-500' : 'bg-slate-700 hover:bg-slate-600'; ?> text-white rounded-lg transition">
                                <?php echo $i; ?>
                            </a>
                            <?php endfor; ?>

                            <?php if ($page < $totalPages): ?>
                            <a href="?page=<?php echo $page + 1; ?><?php echo $status ? '&status=' . urlencode($status) : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="px-3 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
