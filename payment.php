<?php
require_once 'config.php';
require_once 'functions.php';

requireUser();

$pageTitle = 'Pembayaran';
$user = getCurrentUser();
$errors = [];

// Get booking ID from URL
$bookingId = intval($_GET['booking_id'] ?? 0);
$booking = getBookingById($bookingId);

// Validate booking ownership
if (!$booking || $booking['user_id'] != $user['id']) {
    setFlashMessage('Booking tidak ditemukan', 'error');
    redirect('user-dashboard.php');
}

// Payment methods
$paymentMethods = [
    'dana' => ['name' => 'Dana', 'number' => '081234567890'],
    'gopay' => ['name' => 'GoPay', 'number' => '081234567891'],
    'ovo' => ['name' => 'OVO', 'number' => '081234567892'],
    'bca' => ['name' => 'BCA', 'number' => '1234567890'],
    'mandiri' => ['name' => 'Mandiri', 'number' => '1234567890123']
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paymentMethod = sanitize($_POST['payment_method'] ?? '');
    $amount = floatval($_POST['amount'] ?? 0);

    // Validation
    if (empty($paymentMethod) || !array_key_exists($paymentMethod, $paymentMethods)) {
        $errors[] = 'Metode pembayaran tidak valid';
    } elseif ($amount <= 0) {
        $errors[] = 'Jumlah pembayaran harus lebih dari 0';
    } elseif ($amount != $booking['total_price']) {
        $errors[] = 'Jumlah pembayaran harus sesuai dengan total booking';
    } else {
        // Create payment record
        $paymentResult = createPayment($bookingId, $paymentMethod, $amount, $paymentMethods[$paymentMethod]['number']);

        if ($paymentResult['success']) {
            setFlashMessage('Pembayaran berhasil dibuat! Silakan upload bukti pembayaran.', 'success');
            redirect('upload-proof.php?id=' . $bookingId);
        } else {
            $errors[] = $paymentResult['message'];
        }
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

        .input-glow:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
        }

        .payment-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .payment-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }

        .payment-card.selected {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
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
                            <h1 class="text-2xl lg:text-3xl font-bold text-white">Pembayaran</h1>
                            <p class="text-slate-400 text-sm">Booking ID: <?php echo htmlspecialchars($booking['booking_id']); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Booking Summary -->
            <div class="glass-card rounded-2xl p-6 mb-6 slide-up" style="animation-delay: 0.1s;">
                <h2 class="text-xl font-bold text-white mb-4">Detail Booking</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-slate-400">Layanan:</span>
                                <span class="text-white"><?php echo ucfirst(str_replace('_', ' ', $booking['service_type'])); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Model Motor:</span>
                                <span class="text-white"><?php echo htmlspecialchars($booking['vehicle_model']); ?> (<?php echo $booking['vehicle_year']; ?>)</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Tanggal Booking:</span>
                                <span class="text-white"><?php echo date('d/m/Y', strtotime($booking['preferred_date'])); ?></span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="bg-slate-700 bg-opacity-50 rounded-xl p-4">
                            <div class="text-center">
                                <div class="text-sm text-slate-400 mb-1">Total Pembayaran</div>
                                <div class="text-3xl font-bold text-blue-400">Rp <?php echo number_format($booking['total_price'], 0, ',', '.'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Method Selection -->
            <div class="glass-card rounded-2xl p-6 mb-6 slide-up" style="animation-delay: 0.2s;">
                <h2 class="text-xl font-bold text-white mb-4">Pilih Metode Pembayaran</h2>

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

                <form method="POST" class="space-y-6">
                    <input type="hidden" name="amount" value="<?php echo $booking['total_price']; ?>">

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php foreach ($paymentMethods as $key => $method): ?>
                        <div class="payment-card glass-card rounded-xl p-4" data-method="<?php echo $key; ?>">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-slate-600 rounded-lg flex items-center justify-center">
                                    <span class="text-white font-bold text-sm"><?php echo strtoupper(substr($method['name'], 0, 2)); ?></span>
                                </div>
                                <div>
                                    <div class="text-white font-semibold"><?php echo $method['name']; ?></div>
                                    <div class="text-slate-400 text-sm"><?php echo $method['number']; ?></div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <input type="hidden" name="payment_method" id="selected_payment_method" value="<?php echo $_POST['payment_method'] ?? ''; ?>">

                    <!-- Payment Instructions -->
                    <div id="payment_instructions" class="hidden">
                        <div class="bg-blue-500 bg-opacity-10 border border-blue-500 border-opacity-30 rounded-xl p-4">
                            <div class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div>
                                    <h3 class="text-blue-400 font-semibold mb-2">Instruksi Pembayaran</h3>
                                    <div id="payment_details" class="text-blue-300 text-sm space-y-1">
                                        <!-- Payment details will be populated by JavaScript -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-4 pt-6 border-t border-slate-700">
                        <button
                            type="submit"
                            id="proceed_btn"
                            class="flex-1 px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-xl font-semibold transition disabled:opacity-50 disabled:cursor-not-allowed"
                            disabled
                        >
                            Lanjutkan ke Upload Bukti
                        </button>
                        <a
                            href="user-dashboard.php"
                            class="flex-1 px-6 py-3 bg-slate-600 hover:bg-slate-700 text-white rounded-xl font-semibold transition text-center"
                        >
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const paymentCards = document.querySelectorAll('.payment-card');
        const selectedPaymentInput = document.getElementById('selected_payment_method');
        const paymentInstructions = document.getElementById('payment_instructions');
        const paymentDetails = document.getElementById('payment_details');
        const proceedBtn = document.getElementById('proceed_btn');

        const paymentData = <?php echo json_encode($paymentMethods); ?>;

        paymentCards.forEach(card => {
            card.addEventListener('click', () => {
                // Remove selected class from all cards
                paymentCards.forEach(c => c.classList.remove('selected'));

                // Add selected class to clicked card
                card.classList.add('selected');

                // Update hidden input
                const method = card.dataset.method;
                selectedPaymentInput.value = method;

                // Show payment instructions
                const methodData = paymentData[method];
                paymentDetails.innerHTML = `
                    <div>Transfer ke: <strong>${methodData.name}</strong></div>
                    <div>Nomor: <strong>${methodData.number}</strong></div>
                    <div>Jumlah: <strong>Rp ${<?php echo $booking['total_price']; ?>.toLocaleString('id-ID')}</strong></div>
                    <div class="mt-2">Setelah transfer, upload bukti pembayaran untuk verifikasi.</div>
                `;
                paymentInstructions.classList.remove('hidden');
                proceedBtn.disabled = false;
            });
        });

        // Pre-select payment method if form was submitted with errors
        const currentMethod = selectedPaymentInput.value;
        if (currentMethod) {
            const selectedCard = document.querySelector(`[data-method="${currentMethod}"]`);
            if (selectedCard) {
                selectedCard.classList.add('selected');
                selectedCard.click(); // Trigger click to show instructions
            }
        }
    </script>
</body>
</html>
