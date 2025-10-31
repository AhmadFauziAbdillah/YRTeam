<?php
require_once 'config.php';
require_once 'functions.php';

requireAdmin();

$pageTitle = 'Kelola Users';
$admin = getCurrentAdmin();

// Pagination
$page = intval($_GET['page'] ?? 1);
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Filters
$status = isset($_GET['status']) ? intval($_GET['status']) : null;
$search = trim($_GET['search'] ?? '');

// Get users
$users = getAllUsers($search, $status);
$totalUsers = count($users);
$totalPages = ceil($totalUsers / $perPage);

// Slice for pagination
$paginatedUsers = array_slice($users, $offset, $perPage);

// Handle Edit User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user'])) {
    $userId = intval($_POST['user_id']);
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $fullName = sanitize($_POST['full_name']);
    $phone = sanitize($_POST['phone']);

    $result = updateUser($userId, $username, $email, $fullName, $phone);

    if ($result['success']) {
        logAdminActivity('UPDATE_USER', null, "Updated user ID: $userId");
        setFlashMessage($result['message'], 'success');
    } else {
        setFlashMessage($result['message'], 'error');
    }

    $query = $_GET;
    unset($query['edit']);
    $queryString = http_build_query($query);
    redirect('admin-users.php' . ($queryString ? '?' . $queryString : ''));
}

// Handle Delete User
if (isset($_GET['delete'])) {
    $userId = intval($_GET['delete']);

    $result = deleteUser($userId);

    if ($result['success']) {
        logAdminActivity('DELETE_USER', null, "Deleted user ID: $userId");
        setFlashMessage($result['message'], 'success');
    } else {
        setFlashMessage($result['message'], 'error');
    }

    redirect('admin-users.php');
}

// Handle Toggle Status
if (isset($_GET['toggle'])) {
    $userId = intval($_GET['toggle']);

    $result = toggleUserStatus($userId);

    if ($result['success']) {
        logAdminActivity('TOGGLE_USER_STATUS', null, "Toggled status for user ID: $userId");
        setFlashMessage($result['message'], 'success');
    } else {
        setFlashMessage($result['message'], 'error');
    }

    $query = $_GET;
    unset($query['toggle']);
    $queryString = http_build_query($query);
    redirect('admin-users.php' . ($queryString ? '?' . $queryString : ''));
}

// Handle Reset Attempts
if (isset($_GET['reset_attempts'])) {
    $userId = intval($_GET['reset_attempts']);

    $result = resetUserAttempts($userId);

    if ($result['success']) {
        logAdminActivity('RESET_USER_ATTEMPTS', null, "Reset login attempts for user ID: $userId");
        setFlashMessage($result['message'], 'success');
    } else {
        setFlashMessage($result['message'], 'error');
    }

    redirect('admin-users.php' . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''));
}

$userStats = getUserStats();

$editUser = null;
if (isset($_GET['edit'])) {
    $editUser = getUserById(intval($_GET['edit']));
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
                            <h1 class="text-2xl lg:text-3xl font-bold text-white">Kelola Users</h1>
                            <p class="text-slate-400 text-sm">Pantau dan kelola semua akun user</p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <a href="admin.php" class="px-4 py-2 bg-slate-600 hover:bg-slate-700 text-white rounded-lg font-semibold transition">
                            Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="glass-card rounded-2xl p-6 slide-up">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-400 text-sm font-semibold mb-1">Total Users</p>
                            <p class="text-3xl font-bold text-white"><?php echo $userStats['total_users']; ?></p>
                        </div>
                        <div class="w-12 h-12 bg-purple-500 bg-opacity-20 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="glass-card rounded-2xl p-6 slide-up" style="animation-delay: 0.1s;">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-400 text-sm font-semibold mb-1">Active Users</p>
                            <p class="text-3xl font-bold text-green-400"><?php echo $userStats['active_users']; ?></p>
                        </div>
                        <div class="w-12 h-12 bg-green-500 bg-opacity-20 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="glass-card rounded-2xl p-6 slide-up" style="animation-delay: 0.2s;">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-400 text-sm font-semibold mb-1">Inactive Users</p>
                            <p class="text-3xl font-bold text-red-400"><?php echo $userStats['inactive_users']; ?></p>
                        </div>
                        <div class="w-12 h-12 bg-red-500 bg-opacity-20 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="glass-card rounded-2xl p-6 mb-6 slide-up" style="animation-delay: 0.3s;">
                <form method="GET" class="flex flex-col lg:flex-row gap-4">
                    <div class="flex-1">
                        <label class="block text-slate-300 text-sm font-semibold mb-2">Cari User</label>
                        <input
                            type="text"
                            name="search"
                            placeholder="Cari berdasarkan username, email, nama, atau nomor HP..."
                            value="<?php echo htmlspecialchars($search); ?>"
                            class="w-full px-4 py-3 bg-slate-700 bg-opacity-50 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                        />
                    </div>
                    <div class="lg:w-48">
                        <label class="block text-slate-300 text-sm font-semibold mb-2">Status</label>
                        <select name="status" class="w-full px-4 py-3 bg-slate-700 bg-opacity-50 border border-slate-600 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                            <option value="">Semua Status</option>
                            <option value="1" <?php echo $status === 1 ? 'selected' : ''; ?>>Aktif</option>
                            <option value="0" <?php echo $status === 0 ? 'selected' : ''; ?>>Tidak Aktif</option>
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-xl font-semibold transition">
                            Filter
                        </button>
                        <?php if ($status !== null || $search): ?>
                        <a href="admin-users.php" class="px-6 py-3 bg-slate-600 hover:bg-slate-700 text-white rounded-xl font-semibold transition">
                            Reset
                        </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Users Table -->
            <div class="glass-card rounded-2xl p-6 slide-up" style="animation-delay: 0.4s;">
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-6">
                    <h2 class="text-xl font-bold text-white">Daftar Users (<?php echo $totalUsers; ?>)</h2>
                </div>

                <?php if (empty($paginatedUsers)): ?>
                    <div class="text-center py-12">
                        <div class="w-16 h-16 bg-slate-700 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                        </div>
                        <p class="text-slate-400 mb-4">Tidak ada user ditemukan</p>
                        <a href="admin-users.php" class="px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-lg font-semibold transition">
                            Reset Filter
                        </a>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-slate-700">
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase">Username</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase">Nama Lengkap</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase">Email</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase">No HP</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase">Login Attempts</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase">Last Login</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($paginatedUsers as $user): ?>
                                <tr class="border-b border-slate-700 border-opacity-50 hover:bg-slate-700 hover:bg-opacity-30 transition">
                                    <td class="px-4 py-3 text-sm font-mono text-slate-300"><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td class="px-4 py-3 text-sm text-white"><?php echo htmlspecialchars($user['full_name']); ?></td>
                                    <td class="px-4 py-3 text-sm text-slate-300"><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td class="px-4 py-3 text-sm text-slate-300"><?php echo htmlspecialchars($user['phone']); ?></td>
                                    <td class="px-4 py-3">
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $user['is_active'] ? 'bg-green-500 bg-opacity-20 text-green-400' : 'bg-red-500 bg-opacity-20 text-red-400'; ?>">
                                            <?php echo $user['is_active'] ? 'Aktif' : 'Tidak Aktif'; ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-slate-300">
                                        <?php echo ($user['login_attempts'] ?? 0) . '/5'; ?>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-slate-300">
                                        <?php echo $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : '-'; ?>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex gap-2">
                                            <a href="?edit=<?php echo $user['id']; ?><?php echo $status !== null ? '&status=' . $status : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="p-2 text-blue-400 hover:bg-blue-500 hover:bg-opacity-20 rounded-lg transition btn-action" title="Edit">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </a>
                                            <a href="?toggle=<?php echo $user['id']; ?><?php echo $status !== null ? '&status=' . $status : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="p-2 <?php echo $user['is_active'] ? 'text-yellow-400 hover:bg-yellow-500' : 'text-green-400 hover:bg-green-500'; ?> hover:bg-opacity-20 rounded-lg transition btn-action" title="<?php echo $user['is_active'] ? 'Nonaktifkan' : 'Aktifkan'; ?>">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo $user['is_active'] ? 'M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21' : 'M15 12a3 3 0 11-6 0 3 3 0 016 0z'; ?>"></path>
                                                    <?php if (!$user['is_active']): ?>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    <?php endif; ?>
                                                </svg>
                                            </a>
                                            <a href="?delete=<?php echo $user['id']; ?>" onclick="return confirm('Yakin ingin menghapus user ini?')" class="p-2 text-red-400 hover:bg-red-500 hover:bg-opacity-20 rounded-lg transition btn-action" title="Hapus">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </a>
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
                            <a href="?page=<?php echo $page - 1; ?><?php echo $status !== null ? '&status=' . $status : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="px-3 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </a>
                            <?php endif; ?>

                            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <a href="?page=<?php echo $i; ?><?php echo $status !== null ? '&status=' . $status : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="px-3 py-2 <?php echo $i === $page ? 'bg-blue-500' : 'bg-slate-700 hover:bg-slate-600'; ?> text-white rounded-lg transition">
                                <?php echo $i; ?>
                            </a>
                            <?php endfor; ?>

                            <?php if ($page < $totalPages): ?>
                            <a href="?page=<?php echo $page + 1; ?><?php echo $status !== null ? '&status=' . $status : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="px-3 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg transition">
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

    <!-- Edit User Modal -->
    <?php if ($editUser): ?>
    <div class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center p-4 z-50">
        <div class="glass-card rounded-2xl p-6 w-full max-w-md">
            <h3 class="text-xl font-bold text-white mb-4">Edit User</h3>
            <form method="POST">
                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($editUser['id']); ?>">

                <div class="mb-4">
                    <label class="block text-slate-400 text-sm font-semibold mb-2">Username *</label>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($editUser['username']); ?>" required class="w-full px-4 py-2 bg-slate-700 bg-opacity-50 border border-slate-600 rounded-lg text-white placeholder-slate-400 focus:outline-none focus:border-purple-500 transition">
                </div>

                <div class="mb-4">
                    <label class="block text-slate-400 text-sm font-semibold mb-2">Email *</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($editUser['email']); ?>" required class="w-full px-4 py-2 bg-slate-700 bg-opacity-50 border border-slate-600 rounded-lg text-white placeholder-slate-400 focus:outline-none focus:border-purple-500 transition">
                </div>

                <div class="mb-4">
                    <label class="block text-slate-400 text-sm font-semibold mb-2">Nama Lengkap *</label>
                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($editUser['full_name']); ?>" required class="w-full px-4 py-2 bg-slate-700 bg-opacity-50 border border-slate-600 rounded-lg text-white placeholder-slate-400 focus:outline-none focus:border-purple-500 transition">
                </div>

                <div class="mb-6">
                    <label class="block text-slate-400 text-sm font-semibold mb-2">No HP *</label>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($editUser['phone']); ?>" required class="w-full px-4 py-2 bg-slate-700 bg-opacity-50 border border-slate-600 rounded-lg text-white placeholder-slate-400 focus:outline-none focus:border-purple-500 transition">
                </div>

                <div class="flex gap-2">
                    <button type="submit" name="edit_user" class="flex-1 px-4 py-2 bg-purple-500 hover:bg-purple-600 text-white rounded-lg font-semibold transition">
                        Update
                    </button>
                    <a href="admin-users.php<?php echo $status !== null ? '?status=' . $status : ''; ?><?php echo $search ? ($status !== null ? '&' : '?') . 'search=' . urlencode($search) : ''; ?>" class="flex-1 px-4 py-2 bg-slate-600 hover:bg-slate-700 text-white rounded-lg font-semibold transition text-center">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

</body>
</html>
