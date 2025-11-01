<?php
require_once 'config.php';
require_once 'functions.php';

requireUser();

$pageTitle = 'Booking Layanan';
$user = getCurrentUser();
$errors = [];

// Service prices
$servicePrices = [
    'remap_ecu' => 3000000,
    'tuning' => 200000,
    'maintenance' => 100000
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $serviceType = sanitize($_POST['service_type'] ?? '');
    $vehicleModel = sanitize($_POST['vehicle_model'] ?? '');
    $vehicleYear = intval($_POST['vehicle_year'] ?? 0);
    $description = sanitize($_POST['description'] ?? '');
    $preferredDate = $_POST['preferred_date'] ?? '';
    $totalPrice = $servicePrices[$serviceType] ?? 0;

    // Validation
    if (empty($serviceType) || empty($vehicleModel) || empty($preferredDate)) {
        $errors[] = 'Semua field wajib diisi';
    } elseif (!array_key_exists($serviceType, $servicePrices)) {
        $errors[] = 'Tipe layanan tidak valid';
    } elseif (strtotime($preferredDate) < strtotime('today')) {
        $errors[] = 'Tanggal booking tidak boleh di masa lalu';
    } else {
        $bookingResult = createBooking($user['id'], $serviceType, $vehicleModel, $vehicleYear, $description, $preferredDate, $totalPrice);

        if ($bookingResult['success']) {
            setFlashMessage('Booking berhasil dibuat! Silakan lakukan pembayaran.', 'success');
            redirect('payment.php?booking_id=' . $bookingResult['booking_id']);
        } else {
            $errors[] = $bookingResult['message'];
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

        .service-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .service-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .service-card.selected {
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
                            <h1 class="text-2xl lg:text-3xl font-bold text-white">Booking Layanan</h1>
                            <p class="text-slate-400 text-sm">Pilih layanan remap ECU yang Anda butuhkan</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Service Selection -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="service-card glass-card rounded-2xl p-6 slide-up" style="animation-delay: 0.1s;" data-service="remap_ecu">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-blue-500 bg-opacity-20 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-2">Remap ECU</h3>
                        <p class="text-slate-400 text-sm mb-4">Optimalisasi performa mesin motor melalui remapping ECU</p>
                        <div class="text-2xl font-bold text-blue-400">Rp <?php echo number_format($servicePrices['remap_ecu'], 0, ',', '.'); ?></div>
                    </div>
                </div>

            <!-- Booking Form -->
            <div class="glass-card rounded-2xl p-6 slide-up" style="animation-delay: 0.4s;">
                <h2 class="text-xl font-bold text-white mb-6">Detail Booking</h2>

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
                    <input type="hidden" name="service_type" id="selected_service" value="<?php echo $_POST['service_type'] ?? ''; ?>">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-slate-300 text-sm font-semibold mb-2">Model Motor *</label>
                            <input
                                type="text"
                                name="vehicle_model"
                                placeholder="Contoh: Honda CBR 150R"
                                class="w-full px-4 py-3 bg-slate-700 bg-opacity-50 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:outline-none input-glow transition"
                                value="<?php echo isset($_POST['vehicle_model']) ? htmlspecialchars($_POST['vehicle_model']) : ''; ?>"
                                required
                            />
                        </div>

                        <div>
                            <label class="block text-slate-300 text-sm font-semibold mb-2">Tahun Motor</label>
                            <input
                                type="number"
                                name="vehicle_year"
                                placeholder="2020"
                                min="2000"
                                max="<?php echo date('Y') + 1; ?>"
                                class="w-full px-4 py-3 bg-slate-700 bg-opacity-50 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:outline-none input-glow transition"
                                value="<?php echo isset($_POST['vehicle_year']) ? htmlspecialchars($_POST['vehicle_year']) : ''; ?>"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-slate-300 text-sm font-semibold mb-2">Tanggal Booking *</label>
                        <input
                            type="date"
                            name="preferred_date"
                            min="<?php echo date('Y-m-d'); ?>"
                            class="w-full px-4 py-3 bg-slate-700 bg-opacity-50 border border-slate-600 rounded-xl text-white focus:outline-none input-glow transition"
                            value="<?php echo isset($_POST['preferred_date']) ? htmlspecialchars($_POST['preferred_date']) : ''; ?>"
                            required
                        />
                        <p class="text-slate-400 text-xs mt-1">Pilih tanggal yang Anda inginkan untuk servis</p>
                    </div>

                    <div>
                        <label class="block text-slate-300 text-sm font-semibold mb-2">Deskripsi Tambahan</label>
                        <textarea
                            name="description"
                            rows="4"
                            placeholder="Jelaskan keluhan atau permintaan khusus Anda..."
                            class="w-full px-4 py-3 bg-slate-700 bg-opacity-50 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:outline-none input-glow transition resize-none"
                        ><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-4 pt-6 border-t border-slate-700">
                        <button
                            type="submit"
                            class="flex-1 px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-xl font-semibold transition"
                        >
                            Buat Booking
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
        // Service selection
        const serviceCards = document.querySelectorAll('.service-card');
        const selectedServiceInput = document.getElementById('selected_service');

        serviceCards.forEach(card => {
            card.addEventListener('click', () => {
                // Remove selected class from all cards
                serviceCards.forEach(c => c.classList.remove('selected'));

                // Add selected class to clicked card
                card.classList.add('selected');

                // Update hidden input
                const service = card.dataset.service;
                selectedServiceInput.value = service;
            });
        });

        // Pre-select service if form was submitted with errors
        const currentService = selectedServiceInput.value;
        if (currentService) {
            const selectedCard = document.querySelector(`[data-service="${currentService}"]`);
            if (selectedCard) {
                selectedCard.classList.add('selected');
            }
        }
    </script>
</body>
</html>
