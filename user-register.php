<?php
require_once 'config.php';
require_once 'functions.php';

$pageTitle = 'Registrasi Customer';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $fullName = sanitize($_POST['full_name'] ?? '');
    $phone = $_POST['phone'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($username) || empty($email) || empty($fullName) || empty($phone) || empty($password)) {
        $errors[] = 'Semua field harus diisi';
    } elseif ($password !== $confirmPassword) {
        $errors[] = 'Password dan konfirmasi password tidak cocok';
    } elseif (strlen($password) < PASSWORD_MIN_LENGTH) {
        $errors[] = 'Password minimal ' . PASSWORD_MIN_LENGTH . ' karakter';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid';
    } else {
        $registerResult = registerUser($username, $password, $email, $fullName, $phone);

        if ($registerResult['success']) {
            setFlashMessage($registerResult['message'], 'success');
            redirect('user-login.php');
        } else {
            $errors[] = $registerResult['message'];
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
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
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

        .float-tree {
            animation: float 8s ease-in-out infinite;
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

        .input-glow:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
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
    <?php
    $flash = getFlashMessage();
    if ($flash):
    ?>
    <div class="fixed top-6 right-6 z-50 max-w-md">
        <div class="<?php echo $flash['type'] === 'success' ? 'bg-green-500' : 'bg-red-500'; ?> text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-3 fade-in">
            <?php if ($flash['type'] === 'success'): ?>
                <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            <?php else: ?>
                <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

    <div class="min-h-screen flex items-center justify-center p-4 bg-pattern relative">
        <!-- Decorative SVG Path -->
        <svg class="absolute inset-0 w-full h-full opacity-10" xmlns="http://www.w3.org/2000/svg">
            <path class="animated-path" d="M 0,400 Q 250,300 500,400 T 1000,400" stroke="rgba(59, 130, 246, 0.5)" stroke-width="2" fill="none"/>
            <path class="animated-path" d="M 0,500 Q 250,450 500,500 T 1000,500" stroke="rgba(99, 102, 241, 0.5)" stroke-width="2" fill="none" style="animation-delay: 0.5s;"/>
        </svg>

        <!-- Floating Tree Silhouette -->
        <div class="absolute top-10 right-10 opacity-20 float-tree hidden lg:block">
            <svg width="200" height="300" viewBox="0 0 200 300" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M100 280 Q90 250 80 200 Q70 150 75 100 Q80 50 100 20" stroke="#64748b" stroke-width="8" stroke-linecap="round"/>
                <circle cx="100" cy="30" r="40" fill="#475569" opacity="0.5"/>
                <circle cx="70" cy="70" r="35" fill="#475569" opacity="0.6"/>
                <circle cx="130" cy="70" r="35" fill="#475569" opacity="0.6"/>
                <circle cx="100" cy="100" r="45" fill="#475569" opacity="0.7"/>
            </svg>
        </div>

        <div class="w-full max-w-md relative z-10">
            <div class="glass-morphism rounded-3xl shadow-2xl overflow-hidden fade-in">
                <div class="p-8">
                    <!-- Header -->
                    <div class="flex items-center justify-between mb-8">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center logo-pulse">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                            </div>
                            <span class="text-white text-xl font-bold">YR Team</span>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="slide-up" style="animation-delay: 0.2s;">
                        <h1 class="text-3xl font-bold text-white mb-3">
                            Registrasi<span class="text-blue-500">.</span>
                        </h1>
                        <p class="text-slate-400 mb-6">
                            Buat akun untuk booking layanan remap ECU
                        </p>

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

                        <!-- Registration Form -->
                        <form method="POST" class="space-y-4 slide-up" style="animation-delay: 0.3s;">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-slate-300 text-sm font-semibold mb-2">Username</label>
                                    <input
                                        type="text"
                                        name="username"
                                        placeholder="username"
                                        class="w-full px-4 py-3 bg-slate-700 bg-opacity-50 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:outline-none input-glow transition"
                                        value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                                        required
                                    />
                                </div>
                                <div>
                                    <label class="block text-slate-300 text-sm font-semibold mb-2">Email</label>
                                    <input
                                        type="email"
                                        name="email"
                                        placeholder="email@example.com"
                                        class="w-full px-4 py-3 bg-slate-700 bg-opacity-50 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:outline-none input-glow transition"
                                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                        required
                                    />
                                </div>
                            </div>

                            <div>
                                <label class="block text-slate-300 text-sm font-semibold mb-2">Nama Lengkap</label>
                                <input
                                    type="text"
                                    name="full_name"
                                    placeholder="Nama lengkap"
                                    class="w-full px-4 py-3 bg-slate-700 bg-opacity-50 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:outline-none input-glow transition"
                                    value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>"
                                    required
                                />
                            </div>

                            <div>
                                <label class="block text-slate-300 text-sm font-semibold mb-2">No HP</label>
                                <input
                                    type="tel"
                                    name="phone"
                                    placeholder="08xxxxxxxxxx"
                                    class="w-full px-4 py-3 bg-slate-700 bg-opacity-50 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:outline-none input-glow transition"
                                    pattern="[0-9]+"
                                    value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                                    required
                                />
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-slate-300 text-sm font-semibold mb-2">Password</label>
                                    <input
                                        type="password"
                                        name="password"
                                        placeholder="Password"
                                        class="w-full px-4 py-3 bg-slate-700 bg-opacity-50 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:outline-none input-glow transition"
                                        required
                                    />
                                </div>
                                <div>
                                    <label class="block text-slate-300 text-sm font-semibold mb-2">Konfirmasi Password</label>
                                    <input
                                        type="password"
                                        name="confirm_password"
                                        placeholder="Konfirmasi password"
                                        class="w-full px-4 py-3 bg-slate-700 bg-opacity-50 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:outline-none input-glow transition"
                                        required
                                    />
                                </div>
                            </div>

                            <button
                                type="submit"
                                class="w-full btn-primary text-white py-3.5 rounded-xl font-semibold shadow-lg"
                            >
                                Daftar Sekarang
                            </button>
                        </form>

                        <!-- Footer Links -->
                        <div class="mt-8 pt-6 border-t border-slate-700 flex flex-col gap-4 text-center">
                            <p class="text-slate-400 text-sm">
                                Sudah punya akun?
                                <a href="user-login.php" class="text-blue-400 hover:text-blue-300 transition">Login sekarang</a>
                            </p>
                            <p class="text-slate-400 text-sm">
                                <a href="index.php" class="text-slate-400 hover:text-white transition">← Kembali ke halaman utama</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
