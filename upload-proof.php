<?php
require_once 'config.php';
require_once 'functions.php';

requireUser();

$pageTitle = 'Upload Bukti Pembayaran';
$user = getCurrentUser();
$errors = [];

// Get booking ID from URL
$bookingId = intval($_GET['id'] ?? 0);
$booking = getBookingById($bookingId);

// Validate booking ownership
if (!$booking || $booking['user_id'] != $user['id']) {
    setFlashMessage('Booking tidak ditemukan', 'error');
    redirect('user-dashboard.php');
}

// Check if payment exists
if (!$booking['payment_id']) {
    setFlashMessage('Pembayaran belum dibuat', 'error');
    redirect('payment.php?id=' . $bookingId);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['proof_image']) && $_FILES['proof_image']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = uploadPaymentProof($bookingId, $_FILES['proof_image']);

        if ($uploadResult['success']) {
            setFlashMessage('Bukti pembayaran berhasil diupload! Menunggu verifikasi admin.', 'success');
            redirect('user-dashboard.php');
        } else {
            $errors[] = $uploadResult['message'];
        }
    } else {
        $errors[] = 'Silakan pilih file bukti pembayaran';
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

        .upload-area {
            border: 2px dashed rgba(148, 163, 184, 0.3);
            transition: all 0.3s ease;
        }

        .upload-area:hover {
            border-color: rgba(59, 130, 246, 0.5);
            background: rgba(59, 130, 246, 0.05);
        }

        .upload-area.dragover {
            border-color: #3b82f6;
            background: rgba(59, 130, 246, 0.1);
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
        <div class="max-w-2xl mx-auto">
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
                            <h1 class="text-2xl lg:text-3xl font-bold text-white">Upload Bukti Pembayaran</h1>
                            <p class="text-slate-400 text-sm">Booking ID: <?php echo htmlspecialchars($booking['booking_id']); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Summary -->
            <div class="glass-card rounded-2xl p-6 mb-6 slide-up" style="animation-delay: 0.1s;">
                <h2 class="text-xl font-bold text-white mb-4">Detail Pembayaran</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Metode Pembayaran:</span>
                            <span class="text-white"><?php echo ucfirst($booking['payment_method']); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Nomor Pembayaran:</span>
                            <span class="text-white"><?php echo htmlspecialchars($booking['payment_number']); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Jumlah:</span>
                            <span class="text-white font-semibold">Rp <?php echo number_format($booking['amount'], 0, ',', '.'); ?></span>
                        </div>
                    </div>
                    <div>
                        <div class="bg-slate-700 bg-opacity-50 rounded-xl p-4">
                            <div class="text-center">
                                <div class="text-sm text-slate-400 mb-1">Status Pembayaran</div>
                                <div class="text-lg font-bold
                                    <?php
                                    switch($booking['payment_status']) {
                                        case 'pending': echo 'text-yellow-400'; break;
                                        case 'verified': echo 'text-green-400'; break;
                                        case 'rejected': echo 'text-red-400'; break;
                                    }
                                    ?>">
                                    <?php
                                    switch($booking['payment_status']) {
                                        case 'pending': echo 'Menunggu Verifikasi'; break;
                                        case 'verified': echo 'Terverifikasi'; break;
                                        case 'rejected': echo 'Ditolak'; break;
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upload Form -->
            <?php if ($booking['payment_status'] === 'pending' && empty($booking['proof_image'])): ?>
            <div class="glass-card rounded-2xl p-6 slide-up" style="animation-delay: 0.2s;">
                <h2 class="text-xl font-bold text-white mb-4">Upload Bukti Pembayaran</h2>

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

                <form method="POST" enctype="multipart/form-data" class="space-y-6">
                    <div>
                        <label class="block text-slate-300 text-sm font-semibold mb-4">Pilih File Bukti Pembayaran</label>
                        <div class="upload-area rounded-xl p-8 text-center cursor-pointer" id="upload_area">
                            <input type="file" name="proof_image" id="proof_image" accept="image/*" class="hidden" required>
                            <div class="space-y-4">
                                <div class="w-16 h-16 bg-slate-600 rounded-full flex items-center justify-center mx-auto">
                                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-white font-semibold mb-1">Klik untuk memilih file</p>
                                    <p class="text-slate-400 text-sm">atau drag & drop file gambar di sini</p>
                                    <p class="text-slate-500 text-xs mt-2">Format: JPG, PNG, GIF. Maksimal 5MB</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Preview Area -->
                    <div id="preview_area" class="hidden">
                        <div class="bg-slate-700 bg-opacity-50 rounded-xl p-4">
                            <div class="flex items-center gap-4">
                                <img id="preview_image" class="w-16 h-16 object-cover rounded-lg" alt="Preview">
                                <div class="flex-1">
                                    <p class="text-white font-semibold" id="preview_filename"></p>
                                    <p class="text-slate-400 text-sm" id="preview_size"></p>
                                </div>
                                <button type="button" id="remove_file" class="p-2 text-red-400 hover:bg-red-500 hover:bg-opacity-20 rounded-lg transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-4 pt-6 border-t border-slate-700">
                        <button
                            type="submit"
                            class="flex-1 px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-xl font-semibold transition"
                        >
                            Upload Bukti Pembayaran
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
            <?php elseif ($booking['payment_status'] === 'verified'): ?>
            <div class="glass-card rounded-2xl p-6 slide-up" style="animation-delay: 0.2s;">
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-green-500 bg-opacity-20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Pembayaran Terverifikasi</h3>
                    <p class="text-slate-400 mb-6">Bukti pembayaran Anda telah diverifikasi oleh admin.</p>
                    <a href="user-dashboard.php" class="px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-xl font-semibold transition">
                        Kembali ke Dashboard
                    </a>
                </div>
            </div>
            <?php elseif ($booking['payment_status'] === 'rejected'): ?>
            <div class="glass-card rounded-2xl p-6 slide-up" style="animation-delay: 0.2s;">
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-red-500 bg-opacity-20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Pembayaran Ditolak</h3>
                    <p class="text-slate-400 mb-4">Bukti pembayaran Anda ditolak oleh admin.</p>
                    <?php if ($booking['notes']): ?>
                    <div class="bg-red-500 bg-opacity-10 border border-red-500 border-opacity-30 rounded-xl p-4 mb-6">
                        <p class="text-red-300 text-sm"><strong>Catatan Admin:</strong> <?php echo htmlspecialchars($booking['notes']); ?></p>
                    </div>
                    <?php endif; ?>
                    <a href="payment.php?id=<?php echo $bookingId; ?>" class="px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-xl font-semibold transition">
                        Upload Ulang Bukti
                    </a>
                </div>
            </div>
            <?php else: ?>
            <div class="glass-card rounded-2xl p-6 slide-up" style="animation-delay: 0.2s;">
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-slate-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Bukti Sudah Diupload</h3>
                    <p class="text-slate-400 mb-6">Menunggu verifikasi dari admin.</p>
                    <a href="user-dashboard.php" class="px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-xl font-semibold transition">
                        Kembali ke Dashboard
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const uploadArea = document.getElementById('upload_area');
        const fileInput = document.getElementById('proof_image');
        const previewArea = document.getElementById('preview_area');
        const previewImage = document.getElementById('preview_image');
        const previewFilename = document.getElementById('preview_filename');
        const previewSize = document.getElementById('preview_size');
        const removeFileBtn = document.getElementById('remove_file');

        // Click to upload
        uploadArea.addEventListener('click', () => {
            fileInput.click();
        });

        // File selection
        fileInput.addEventListener('change', handleFileSelect);

        // Drag and drop
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');

            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                handleFileSelect();
            }
        });

        function handleFileSelect() {
            const file = fileInput.files[0];
            if (file) {
                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Format file tidak didukung. Gunakan JPG, PNG, atau GIF.');
                    return;
                }

                // Validate file size (5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('Ukuran file maksimal 5MB.');
                    return;
                }

                // Show preview
                const reader = new FileReader();
                reader.onload = (e) => {
                    previewImage.src = e.target.result;
                    previewFilename.textContent = file.name;
                    previewSize.textContent = formatFileSize(file.size);
                    previewArea.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Remove file
        removeFileBtn.addEventListener('click', () => {
            fileInput.value = '';
            previewArea.classList.add('hidden');
        });
    </script>
</body>
</html>
