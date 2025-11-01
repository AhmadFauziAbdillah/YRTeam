<?php
// functions.php - Fungsi-fungsi Business Logic dengan Bot Integration & Admin Logging
require_once 'config.php';

// Generate ID Garansi Unik
function generateWarrantyId() {
    $timestamp = base_convert(time(), 10, 36);
    $random = substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 5);
    return 'ECU-' . strtoupper($timestamp) . '-' . $random;
}

// Cek ID sudah digunakan atau belum
function checkWarrantyIdExists($id) {
    global $conn;
    
    $sql = "SELECT id FROM warranties WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->num_rows > 0;
}

// Normalisasi Nomor HP
function normalizePhone($phone) {
    $phone = preg_replace('/\D/', '', $phone);
    if (substr($phone, 0, 1) === '0') {
        $phone = '62' . substr($phone, 1);
    }
    return $phone;
}

// Cek Duplikat Nomor HP
function checkDuplicatePhone($nohp, $excludeId = null) {
    global $conn;
    $normalizedPhone = normalizePhone($nohp);
    
    $sql = "SELECT id FROM warranties WHERE nohp = ?";
    if ($excludeId) {
        $sql .= " AND id != ?";
    }
    
    $stmt = $conn->prepare($sql);
    if ($excludeId) {
        $stmt->bind_param("ss", $normalizedPhone, $excludeId);
    } else {
        $stmt->bind_param("s", $normalizedPhone);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

// Get warranty by phone number
function getWarrantyByPhone($nohp) {
    global $conn;
    
    $sql = "SELECT * FROM warranties WHERE nohp = ? ORDER BY registration_date DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $nohp);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $warranty = $result->fetch_assoc();
        
        // Hitung sisa hari
        $expiryDate = new DateTime($warranty['expiry_date']);
        $today = new DateTime();
        $interval = $today->diff($expiryDate);
        $daysRemaining = $interval->invert ? 0 : $interval->days;
        
        $warranty['days_remaining'] = $daysRemaining;
        $warranty['is_active'] = $daysRemaining > 0;
        
        return $warranty;
    }
    
    return null;
}

// Tambah Garansi Baru dengan Auto-Send WhatsApp
function addWarranty($id, $nama, $nohp, $model, $warrantyDays = 7) {
    global $conn;
    
    // Cek ID sudah digunakan
    if (checkWarrantyIdExists($id)) {
        return ['success' => false, 'message' => 'ID Garansi sudah digunakan!'];
    }
    
    $normalizedPhone = normalizePhone($nohp);
    $registrationDate = date('Y-m-d H:i:s');
    $expiryDate = date('Y-m-d H:i:s', strtotime("+$warrantyDays days"));
    
    $sql = "INSERT INTO warranties (id, nama, nohp, model, registration_date, expiry_date, warranty_days) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssi", $id, $nama, $normalizedPhone, $model, $registrationDate, $expiryDate, $warrantyDays);
    
    if ($stmt->execute()) {
        // Generate WhatsApp message
        $message = generateWhatsAppMessage($id, $nama, $normalizedPhone, $model, $registrationDate, $warrantyDays);
        
        // Send via Bot API with fallback
        $sendResult = sendWhatsAppMessageWithFallback($normalizedPhone, $message);
        
        return [
            'success' => true,
            'id' => $id,
            'data' => [
                'nama' => $nama,
                'nohp' => $normalizedPhone,
                'model' => $model,
                'registration_date' => $registrationDate,
                'warranty_days' => $warrantyDays
            ],
            'whatsapp' => $sendResult
        ];
    }
    
    return ['success' => false, 'message' => 'Gagal menyimpan data'];
}

// Cari Garansi by ID
function getWarrantyById($id) {
    global $conn;
    
    $sql = "SELECT * FROM warranties WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $warranty = $result->fetch_assoc();
        
        // Hitung sisa hari
        $expiryDate = new DateTime($warranty['expiry_date']);
        $today = new DateTime();
        $interval = $today->diff($expiryDate);
        $daysRemaining = $interval->invert ? 0 : $interval->days;
        
        $warranty['days_remaining'] = $daysRemaining;
        $warranty['is_active'] = $daysRemaining > 0;
        
        return $warranty;
    }
    
    return null;
}

// Get Semua Garansi dengan filter search
function getAllWarranties($search = '') {
    global $conn;
    
    $sql = "SELECT * FROM warranties";
    
    if (!empty($search)) {
        $sql .= " WHERE id LIKE ? OR nama LIKE ? OR nohp LIKE ?";
    }
    
    $sql .= " ORDER BY registration_date DESC";
    
    if (!empty($search)) {
        $stmt = $conn->prepare($sql);
        $searchParam = "%$search%";
        $stmt->bind_param("sss", $searchParam, $searchParam, $searchParam);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $conn->query($sql);
    }
    
    $warranties = [];
    while ($row = $result->fetch_assoc()) {
        $expiryDate = new DateTime($row['expiry_date']);
        $today = new DateTime();
        $row['is_active'] = $expiryDate > $today;
        $warranties[] = $row;
    }
    
    return $warranties;
}

// Update Garansi
function updateWarranty($id, $nama, $nohp, $model) {
    global $conn;
    
    $normalizedPhone = normalizePhone($nohp);
    
    $sql = "UPDATE warranties SET nama = ?, nohp = ?, model = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $nama, $normalizedPhone, $model, $id);
    
    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Data berhasil diupdate'];
    }
    
    return ['success' => false, 'message' => 'Gagal mengupdate data'];
}

// Extend/Perpanjang Garansi dengan ID baru dan Auto-Send WhatsApp
function extendWarranty($oldId, $newId, $warrantyDays) {
    global $conn;
    
    // Cek ID baru sudah digunakan
    if (checkWarrantyIdExists($newId)) {
        return ['success' => false, 'message' => 'ID Garansi baru sudah digunakan!'];
    }
    
    // Get data lama
    $oldWarranty = getWarrantyById($oldId);
    if (!$oldWarranty) {
        return ['success' => false, 'message' => 'Data garansi lama tidak ditemukan'];
    }
    
    // Insert garansi baru dengan ID baru
    $registrationDate = date('Y-m-d H:i:s');
    $expiryDate = date('Y-m-d H:i:s', strtotime("+$warrantyDays days"));
    
    $sql = "INSERT INTO warranties (id, nama, nohp, model, registration_date, expiry_date, warranty_days) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssi", $newId, $oldWarranty['nama'], $oldWarranty['nohp'], 
                      $oldWarranty['model'], $registrationDate, $expiryDate, $warrantyDays);
    
    if ($stmt->execute()) {
        // Update status garansi lama menjadi expired
        $updateOld = "UPDATE warranties SET status = 'expired' WHERE id = ?";
        $stmtUpdate = $conn->prepare($updateOld);
        $stmtUpdate->bind_param("s", $oldId);
        $stmtUpdate->execute();
        
        // Generate WhatsApp message untuk perpanjangan
        $message = "*YR Team*\n\n";
        $message .= "Garansi berhasil diperpanjang!\n\n";
        $message .= "ID Garansi Lama: $oldId\n";
        $message .= "ID Garansi Baru: *$newId*\n\n";
        $message .= "Nama: " . $oldWarranty['nama'] . "\n";
        $message .= "No HP: " . $oldWarranty['nohp'] . "\n";
        $message .= "Model Motor: " . $oldWarranty['model'] . "\n";
        $message .= "Tgl Perpanjangan: " . date('d/m/Y H:i') . "\n";
        $message .= "Masa Berlaku: $warrantyDays Hari\n";
        $message .= "Berlaku s/d: " . date('d/m/Y', strtotime($expiryDate)) . "\n\n";
        $message .= "Website : yrteam.web.id\n";
        $message .= "*SIMPAN ID GARANSI BARU ANDA*\n";
        $message .= "Gunakan ID baru ini untuk cek masa aktif garansi";
        
        // Send via Bot API with fallback
        $sendResult = sendWhatsAppMessageWithFallback($oldWarranty['nohp'], $message);
        
        return [
            'success' => true, 
            'message' => 'Garansi berhasil diperpanjang',
            'new_id' => $newId,
            'whatsapp' => $sendResult
        ];
    }
    
    return ['success' => false, 'message' => 'Gagal memperpanjang garansi'];
}

// Update durasi garansi
function updateWarrantyDuration($id, $warrantyDays) {
    global $conn;
    
    $warranty = getWarrantyById($id);
    if (!$warranty) {
        return ['success' => false, 'message' => 'Data tidak ditemukan'];
    }
    
    // Hitung expiry date baru dari registration date
    $registrationDate = $warranty['registration_date'];
    $expiryDate = date('Y-m-d H:i:s', strtotime($registrationDate . " +$warrantyDays days"));
    
    $sql = "UPDATE warranties SET expiry_date = ?, warranty_days = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sis", $expiryDate, $warrantyDays, $id);
    
    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Durasi garansi berhasil diupdate'];
    }
    
    return ['success' => false, 'message' => 'Gagal mengupdate durasi'];
}

// Hapus Garansi
function deleteWarranty($id) {
    global $conn;
    
    $sql = "DELETE FROM warranties WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $id);
    
    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Data berhasil dihapus'];
    }
    
    return ['success' => false, 'message' => 'Gagal menghapus data'];
}

// Get Statistik
function getStatistics() {
    global $conn;
    
    $total = $conn->query("SELECT COUNT(*) as count FROM warranties")->fetch_assoc()['count'];
    $active = $conn->query("SELECT COUNT(*) as count FROM warranties WHERE expiry_date > NOW()")->fetch_assoc()['count'];
    $expired = $total - $active;
    
    return [
        'total' => $total,
        'active' => $active,
        'expired' => $expired
    ];
}

// Generate WhatsApp Message
function generateWhatsAppMessage($id, $nama, $nohp, $model, $registrationDate, $warrantyDays) {
    $expiryDate = date('d/m/Y', strtotime($registrationDate . " +$warrantyDays days"));
    
    $message = "*YR Team*\n\n";
    $message .= "Registrasi Berhasil!\n\n";
    $message .= "Berikut adalah data garansi Anda:\n\n";
    $message .= "ID Garansi: *$id*\n";
    $message .= "Nama: $nama\n";
    $message .= "No HP: $nohp\n";
    $message .= "Model Motor: $model\n";
    $message .= "Tanggal Registrasi: " . date('d/m/Y H:i', strtotime($registrationDate)) . "\n";
    $message .= "Masa Berlaku: $warrantyDays Hari\n";
    $message .= "Berlaku s/d: $expiryDate\n\n";
    $message .= "Website : yrteam.web.id\n";
    $message .= "*SIMPAN ID GARANSI ANDA*\n";
    $message .= "Gunakan ID ini untuk cek masa aktif garansi kapan saja.\n\n";
    $message .= "Terima kasih telah mempercayai layanan kami! 🙏";
    
    return $message;
}

// Resend warranty info via WhatsApp
function resendWarrantyInfo($warrantyId) {
    $warranty = getWarrantyById($warrantyId);
    
    if (!$warranty) {
        return ['success' => false, 'message' => 'Data garansi tidak ditemukan'];
    }
    
    // Generate message
    $message = "*YR Team*\n\n";
    $message .= "Halo *" . $warranty['nama'] . "*,\n\n";
    $message .= "Berikut adalah data garansi Anda:\n\n";
    $message .= "ID Garansi: *" . $warranty['id'] . "*\n";
    $message .= "Nama: " . $warranty['nama'] . "\n";
    $message .= "No HP: " . $warranty['nohp'] . "\n";
    $message .= "Model Motor: " . $warranty['model'] . "\n";
    $message .= "Tgl Registrasi: " . date('d/m/Y', strtotime($warranty['registration_date'])) . "\n";
    $message .= "Masa Berlaku: " . $warranty['warranty_days'] . " Hari\n";
    $message .= "Berlaku s/d: " . date('d/m/Y', strtotime($warranty['expiry_date'])) . "\n\n";
    $message .= "Website : yrteam.web.id\n";
    
    if ($warranty['is_active']) {
        $message .= "✅ Status: *AKTIF*\n";
        $message .= "⏳ Sisa Waktu: " . $warranty['days_remaining'] . " hari\n\n";
    } else {
        $message .= "❌ Status: *EXPIRED*\n\n";
    }
    
    $message .= "*SIMPAN ID GARANSI ANDA*\n";
    $message .= "Gunakan ID ini untuk cek masa aktif garansi";
    
    // Send via Bot API with fallback
    $sendResult = sendWhatsAppMessageWithFallback($warranty['nohp'], $message);
    
    return [
        'success' => true,
        'warranty' => $warranty,
        'whatsapp' => $sendResult
    ];
}

// Log Admin Activity dengan tracking admin
function logAdminActivity($action, $warrantyId = null, $description = '') {
    global $conn;
    
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    // Get current admin info
    $admin = getCurrentAdmin();
    $adminId = $admin['id'] ?? null;
    $username = $admin['username'] ?? null;
    
    $sql = "INSERT INTO admin_logs (admin_id, username, action, warranty_id, description, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssss", $adminId, $username, $action, $warrantyId, $description, $ipAddress, $userAgent);
    $stmt->execute();
}

// Get Admin Logs
function getAdminLogs($limit = 50, $adminId = null) {
    global $conn;
    
    $sql = "SELECT al.*, a.full_name 
            FROM admin_logs al 
            LEFT JOIN admins a ON al.admin_id = a.id";
    
    if ($adminId) {
        $sql .= " WHERE al.admin_id = ?";
    }
    
    $sql .= " ORDER BY al.created_at DESC LIMIT ?";
    
    $stmt = $conn->prepare($sql);
    
    if ($adminId) {
        $stmt->bind_param("ii", $adminId, $limit);
    } else {
        $stmt->bind_param("i", $limit);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $logs = [];
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }
    
    return $logs;
}

// Get All Admins
function getAllAdmins() {
    global $conn;

    $sql = "SELECT id, username, email, full_name, is_active, last_login, created_at
            FROM admins
            ORDER BY created_at DESC";

    $result = $conn->query($sql);

    $admins = [];
    while ($row = $result->fetch_assoc()) {
        $admins[] = $row;
    }

    return $admins;
}

// Toggle Admin Status
function toggleAdminStatus($adminId) {
    global $conn;

    // Tidak bisa menonaktifkan diri sendiri
    $currentAdmin = getCurrentAdmin();
    if ($currentAdmin && $currentAdmin['id'] == $adminId) {
        return ['success' => false, 'message' => 'Tidak dapat menonaktifkan akun sendiri'];
    }

    $sql = "UPDATE admins SET is_active = NOT is_active WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $adminId);

    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Status admin berhasil diubah'];
    }

    return ['success' => false, 'message' => 'Gagal mengubah status admin'];
}

// ========================================
// USER MANAGEMENT FUNCTIONS
// ========================================

/**
 * Register new user
 */
function registerUser($username, $password, $email, $fullName, $phone) {
    global $conn;

    $username = sanitize($username);
    $email = sanitize($email);
    $fullName = sanitize($fullName);
    $normalizedPhone = normalizePhone($phone);

    // Validasi
    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        return [
            'success' => false,
            'message' => 'Password minimal ' . PASSWORD_MIN_LENGTH . ' karakter'
        ];
    }

    // Check if username exists
    $checkSql = "SELECT id FROM users WHERE username = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("s", $username);
    $checkStmt->execute();
    if ($checkStmt->get_result()->num_rows > 0) {
        return ['success' => false, 'message' => 'Username sudah digunakan'];
    }

    // Check if email exists
    $checkEmailSql = "SELECT id FROM users WHERE email = ?";
    $checkEmailStmt = $conn->prepare($checkEmailSql);
    $checkEmailStmt->bind_param("s", $email);
    $checkEmailStmt->execute();
    if ($checkEmailStmt->get_result()->num_rows > 0) {
        return ['success' => false, 'message' => 'Email sudah digunakan'];
    }

    // Check if phone exists
    $checkPhoneSql = "SELECT id FROM users WHERE phone = ?";
    $checkPhoneStmt = $conn->prepare($checkPhoneSql);
    $checkPhoneStmt->bind_param("s", $normalizedPhone);
    $checkPhoneStmt->execute();
    if ($checkPhoneStmt->get_result()->num_rows > 0) {
        return ['success' => false, 'message' => 'Nomor HP sudah digunakan'];
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Insert user
    $sql = "INSERT INTO users (username, password, email, full_name, phone) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $username, $hashedPassword, $email, $fullName, $normalizedPhone);

    if ($stmt->execute()) {
        return [
            'success' => true,
            'message' => 'Registrasi berhasil! Silakan login.',
            'user_id' => $conn->insert_id
        ];
    }

    return ['success' => false, 'message' => 'Gagal mendaftarkan akun'];
}

/**
 * Verify user login with attempt limit
 */
function verifyUserLogin($username, $password) {
    global $conn;

    $username = sanitize($username);

    // Check if user exists and is active
    $sql = "SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        return ['success' => false, 'message' => 'Username/email atau password salah'];
    }

    $user = $result->fetch_assoc();

    // Check if account is permanently locked due to too many failed attempts
    $maxAttempts = 5;
    if ($user['login_attempts'] >= $maxAttempts) {
        return ['success' => false, 'message' => 'Akun terkunci karena terlalu banyak percobaan login gagal. Hubungi admin untuk membuka kunci akun.'];
    }

    // Verify password
    if (password_verify($password, $user['password'])) {
        // Reset login attempts on successful login
        $resetAttemptsSql = "UPDATE users SET login_attempts = 0, last_attempt = NULL, last_login = NOW() WHERE id = ?";
        $resetAttemptsStmt = $conn->prepare($resetAttemptsSql);
        $resetAttemptsStmt->bind_param("i", $user['id']);
        $resetAttemptsStmt->execute();

        // Set session
        $_SESSION['is_user'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_username'] = $user['username'];
        $_SESSION['user_full_name'] = $user['full_name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_phone'] = $user['phone'];
        $_SESSION['user_last_activity'] = time();

        return [
            'success' => true,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'full_name' => $user['full_name'],
                'email' => $user['email'],
                'phone' => $user['phone']
            ]
        ];
    } else {
        // Increment login attempts
        $attempts = $user['login_attempts'] + 1;
        $updateAttemptsSql = "UPDATE users SET login_attempts = ?, last_attempt = NOW() WHERE id = ?";
        $updateAttemptsStmt = $conn->prepare($updateAttemptsSql);
        $updateAttemptsStmt->bind_param("ii", $attempts, $user['id']);
        $updateAttemptsStmt->execute();

        // Check if account should be permanently locked
        if ($attempts >= $maxAttempts) {
            // Permanently disable account
            $disableSql = "UPDATE users SET is_active = 0 WHERE id = ?";
            $disableStmt = $conn->prepare($disableSql);
            $disableStmt->bind_param("i", $user['id']);
            $disableStmt->execute();

            return ['success' => false, 'message' => 'Username/email atau password salah. Akun terkunci karena terlalu banyak percobaan login gagal. Hubungi admin untuk membuka kunci akun.'];
        }

        $remainingAttempts = $maxAttempts - $attempts;
        return ['success' => false, 'message' => "Username/email atau password salah. Sisa percobaan: $remainingAttempts"];
    }
}

/**
 * Get user by ID
 */
function getUserById($userId) {
    global $conn;

    $sql = "SELECT id, username, email, full_name, phone, is_active, last_login, created_at
            FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }

    return null;
}

/**
 * Get all users for admin
 */
function getAllUsers($search = '', $status = null) {
    global $conn;

    $sql = "SELECT id, username, email, full_name, phone, is_active, last_login, created_at, login_attempts
            FROM users WHERE 1=1";

    $params = [];
    $types = '';

    if (!empty($search)) {
        $sql .= " AND (username LIKE ? OR email LIKE ? OR full_name LIKE ? OR phone LIKE ?)";
        $searchParam = "%$search%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $types .= 'ssss';
    }

    if ($status !== null) {
        $sql .= " AND is_active = ?";
        $params[] = $status;
        $types .= 'i';
    }

    $sql .= " ORDER BY created_at DESC";

    $stmt = $conn->prepare($sql);

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }

    return $users;
}

/**
 * Update user data
 */
function updateUser($userId, $username, $email, $fullName, $phone) {
    global $conn;

    $username = sanitize($username);
    $email = sanitize($email);
    $fullName = sanitize($fullName);
    $normalizedPhone = normalizePhone($phone);

    // Check if username exists (exclude current user)
    $checkSql = "SELECT id FROM users WHERE username = ? AND id != ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("si", $username, $userId);
    $checkStmt->execute();
    if ($checkStmt->get_result()->num_rows > 0) {
        return ['success' => false, 'message' => 'Username sudah digunakan'];
    }

    // Check if email exists (exclude current user)
    $checkEmailSql = "SELECT id FROM users WHERE email = ? AND id != ?";
    $checkEmailStmt = $conn->prepare($checkEmailSql);
    $checkEmailStmt->bind_param("si", $email, $userId);
    $checkEmailStmt->execute();
    if ($checkEmailStmt->get_result()->num_rows > 0) {
        return ['success' => false, 'message' => 'Email sudah digunakan'];
    }

    // Check if phone exists (exclude current user)
    $checkPhoneSql = "SELECT id FROM users WHERE phone = ? AND id != ?";
    $checkPhoneStmt = $conn->prepare($checkPhoneSql);
    $checkPhoneStmt->bind_param("si", $normalizedPhone, $userId);
    $checkPhoneStmt->execute();
    if ($checkPhoneStmt->get_result()->num_rows > 0) {
        return ['success' => false, 'message' => 'Nomor HP sudah digunakan'];
    }

    // Update user
    $sql = "UPDATE users SET username = ?, email = ?, full_name = ?, phone = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $username, $email, $fullName, $normalizedPhone, $userId);

    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Data user berhasil diupdate'];
    }

    return ['success' => false, 'message' => 'Gagal mengupdate data user'];
}

/**
 * Delete user
 */
function deleteUser($userId) {
    global $conn;

    // Check if user has active bookings
    $checkSql = "SELECT COUNT(*) as count FROM bookings WHERE user_id = ? AND status IN ('pending', 'confirmed', 'in_progress')";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("i", $userId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result()->fetch_assoc();

    if ($checkResult['count'] > 0) {
        return ['success' => false, 'message' => 'Tidak dapat menghapus user yang memiliki booking aktif'];
    }

    // Delete user (cascade will handle related records)
    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);

    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'User berhasil dihapus'];
    }

    return ['success' => false, 'message' => 'Gagal menghapus user'];
}

/**
 * Toggle user active status
 */
function toggleUserStatus($userId) {
    global $conn;

    $sql = "UPDATE users SET is_active = NOT is_active WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);

    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Status user berhasil diubah'];
    }

    return ['success' => false, 'message' => 'Gagal mengubah status user'];
}

/**
 * Reset user login attempts (for admin unlock)
 */
function resetUserAttempts($userId) {
    global $conn;

    $sql = "UPDATE users SET login_attempts = 0, last_attempt = NULL WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);

    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Login attempts berhasil direset'];
    }

    return ['success' => false, 'message' => 'Gagal mereset login attempts'];
}

/**
 * Get user statistics
 */
function getUserStats() {
    global $conn;

    $sql = "SELECT
                COUNT(*) as total_users,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_users,
                SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive_users
            FROM users";

    $result = $conn->query($sql);
    return $result->fetch_assoc();
}

// ========================================
// BOOKING MANAGEMENT FUNCTIONS
// ========================================

/**
 * Generate booking ID
 */
function generateBookingId() {
    $timestamp = base_convert(time(), 10, 36);
    $random = substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 5);
    return 'BK-' . strtoupper($timestamp) . '-' . $random;
}

/**
 * Create new booking
 */
function createBooking($userId, $serviceType, $vehicleModel, $vehicleYear, $description, $preferredDate, $totalPrice = 0) {
    global $conn;

    $bookingId = generateBookingId();
    $bookingDate = date('Y-m-d H:i:s');

    $sql = "INSERT INTO bookings (booking_id, user_id, service_type, vehicle_model, vehicle_year, description, booking_date, preferred_date, total_price)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sissssssd", $bookingId, $userId, $serviceType, $vehicleModel, $vehicleYear, $description, $bookingDate, $preferredDate, $totalPrice);

    if ($stmt->execute()) {
        $bookingId = $conn->insert_id;

        // Send WhatsApp notification only after payment proof is uploaded
        // WA notification will be sent in uploadPaymentProof function instead

        return [
            'success' => true,
            'booking_id' => $bookingId,
            'message' => 'Booking berhasil dibuat!'
        ];
    }

    return ['success' => false, 'message' => 'Gagal membuat booking'];
}

/**
 * Get bookings by user ID
 */
function getBookingsByUser($userId, $status = null) {
    global $conn;

    $sql = "SELECT b.*, p.status as payment_status, p.amount as payment_amount,
                   w.id as warranty_id, w.expiry_date as warranty_expiry, w.warranty_days,
                   CASE WHEN w.expiry_date > NOW() THEN 1 ELSE 0 END as warranty_active,
                   DATEDIFF(w.expiry_date, NOW()) as warranty_days_remaining
            FROM bookings b
            LEFT JOIN payments p ON b.id = p.booking_id
            LEFT JOIN warranties w ON w.nohp = (SELECT phone FROM users WHERE id = b.user_id)
                AND w.model = b.vehicle_model
                AND w.registration_date >= DATE_SUB(b.created_at, INTERVAL 1 DAY)
            WHERE b.user_id = ?";

    if ($status) {
        $sql .= " AND b.status = ?";
    }

    $sql .= " ORDER BY b.created_at DESC";

    $stmt = $conn->prepare($sql);

    if ($status) {
        $stmt->bind_param("is", $userId, $status);
    } else {
        $stmt->bind_param("i", $userId);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $bookings = [];
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }

    return $bookings;
}

/**
 * Get booking by ID
 */
function getBookingById($bookingId) {
    global $conn;

    $sql = "SELECT b.*, u.full_name, u.phone, u.email,
                   p.id as payment_id, p.payment_method, p.amount, p.payment_number,
                   p.proof_image, p.status as payment_status, p.verified_at, p.notes,
                   p.updated_at as proof_uploaded_at
            FROM bookings b
            LEFT JOIN users u ON b.user_id = u.id
            LEFT JOIN payments p ON b.id = p.booking_id
            WHERE b.id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $bookingId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }

    return null;
}

/**
 * Get all bookings for admin
 */
function getAllBookings($status = null, $search = '') {
    global $conn;

    $sql = "SELECT b.*, u.full_name, u.phone, u.email,
                   p.status as payment_status, p.amount as payment_amount, p.proof_image
            FROM bookings b
            LEFT JOIN users u ON b.user_id = u.id
            LEFT JOIN payments p ON b.id = p.booking_id
            WHERE 1=1";

    $params = [];
    $types = '';

    if ($status) {
        $sql .= " AND b.status = ?";
        $params[] = $status;
        $types .= 's';
    }

    if (!empty($search)) {
        $sql .= " AND (b.booking_id LIKE ? OR u.full_name LIKE ? OR u.phone LIKE ? OR b.vehicle_model LIKE ?)";
        $searchParam = "%$search%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $types .= 'ssss';
    }

    $sql .= " ORDER BY b.created_at DESC";

    $stmt = $conn->prepare($sql);

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $bookings = [];
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }

    return $bookings;
}

/**
 * Update booking status
 */
function updateBookingStatus($bookingId, $status) {
    global $conn;

    $sql = "UPDATE bookings SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $bookingId);

    if ($stmt->execute()) {
        // Send WhatsApp notification only if payment proof has been uploaded
        $booking = getBookingById($bookingId);
        if ($booking && $booking['proof_image']) {
            $message = "*YR Team - Update Status Booking*\n\n";
            $message .= "Halo *" . $booking['full_name'] . "*,\n\n";
            $message .= "Status booking Anda telah diupdate!\n\n";
            $message .= "ID Booking: *" . $booking['booking_id'] . "*\n";
            $message .= "Layanan: " . ucfirst(str_replace('_', ' ', $booking['service_type'])) . "\n";
            $message .= "Model Motor: " . $booking['vehicle_model'] . "\n";
            $message .= "Status Baru: *" . ucfirst(str_replace('_', ' ', $status)) . "*\n";
            $message .= "Tanggal Update: " . date('d/m/Y H:i') . "\n\n";

            if ($status === 'confirmed') {
                $message .= "✅ Booking Anda telah dikonfirmasi!\n";
                $message .= "Silakan datang ke workshop sesuai jadwal yang telah disepakati.\n\n";
            } elseif ($status === 'in_progress') {
                $message .= "🔧 Layanan sedang diproses!\n";
                $message .= "Tim kami sedang mengerjakan remap ECU motor Anda.\n\n";
            } elseif ($status === 'completed') {
                $message .= "🎉 Layanan selesai!\n";
                $message .= "Remap ECU telah berhasil dilakukan. Silakan ambil motor Anda.\n\n";
            } elseif ($status === 'cancelled') {
                $message .= "❌ Booking dibatalkan\n";
                $message .= "Booking Anda telah dibatalkan.\n\n";
            }

            $message .= "Website : yrtem.web.id\n";
            $message .= "Untuk informasi lebih lanjut, hubungi admin.";

            sendWhatsAppMessageWithFallback($booking['phone'], $message);
        }

        return ['success' => true, 'message' => 'Status booking berhasil diupdate'];
    }

    return ['success' => false, 'message' => 'Gagal mengupdate status booking'];
}

// ========================================
// PAYMENT MANAGEMENT FUNCTIONS
// ========================================

/**
 * Create payment record
 */
function createPayment($bookingId, $paymentMethod, $amount, $paymentNumber = null) {
    global $conn;

    $sql = "INSERT INTO payments (booking_id, payment_method, amount, payment_number)
            VALUES (?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isds", $bookingId, $paymentMethod, $amount, $paymentNumber);

    if ($stmt->execute()) {
        return [
            'success' => true,
            'payment_id' => $conn->insert_id,
            'message' => 'Payment record berhasil dibuat'
        ];
    }

    return ['success' => false, 'message' => 'Gagal membuat payment record'];
}

/**
 * Upload payment proof
 */
function uploadPaymentProof($bookingId, $file) {
    global $conn;

    // Validate file
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'message' => 'Format file tidak didukung. Gunakan JPG, PNG, atau GIF.'];
    }

    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'Ukuran file maksimal 5MB.'];
    }

    // Create uploads directory if not exists
    $uploadDir = __DIR__ . '/uploads/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'proof_' . $bookingId . '_' . time() . '.' . $extension;
    $filepath = $uploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Update payment record
        $sql = "UPDATE payments SET proof_image = ?, updated_at = NOW() WHERE booking_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $filename, $bookingId);

        if ($stmt->execute()) {
            // Send WhatsApp notification to user after payment proof is uploaded
            $booking = getBookingById($bookingId);
            if ($booking) {
                $message = "*YR Team - Bukti Pembayaran Diterima*\n\n";
                $message .= "Halo *" . $booking['full_name'] . "*,\n\n";
                $message .= "Bukti pembayaran Anda telah berhasil diupload!\n\n";
                $message .= "ID Booking: *" . $booking['booking_id'] . "*\n";
                $message .= "Layanan: " . ucfirst(str_replace('_', ' ', $booking['service_type'])) . "\n";
                $message .= "Model Motor: " . $booking['vehicle_model'] . "\n";
                $message .= "Total: Rp " . number_format($booking['total_price'], 0, ',', '.') . "\n";
                $message .= "Tanggal Upload: " . date('d/m/Y H:i') . "\n\n";
                $message .= "Status: *MENUNGGU VERIFIKASI*\n\n";
                $message .= "Admin akan memverifikasi pembayaran Anda dalam 1-2 hari kerja.\n";
                $message .= "Anda akan menerima notifikasi selanjutnya setelah verifikasi selesai.\n\n";
                $message .= "Website : yrteam.web.id\n";
                $message .= "Terima kasih atas kepercayaan Anda! 🙏";

                sendWhatsAppMessageWithFallback($booking['phone'], $message);

                // Send WhatsApp notification to admin
                $adminMessage = "*YR Team - Booking Baru dengan Bukti Pembayaran*\n\n";
                $adminMessage .= "Ada booking baru yang sudah upload bukti pembayaran!\n\n";
                $adminMessage .= "ID Booking: *" . $booking['booking_id'] . "*\n";
                $adminMessage .= "Nama: " . $booking['full_name'] . "\n";
                $adminMessage .= "No HP: " . $booking['phone'] . "\n";
                $adminMessage .= "Email: " . $booking['email'] . "\n";
                $adminMessage .= "Layanan: " . ucfirst(str_replace('_', ' ', $booking['service_type'])) . "\n";
                $adminMessage .= "Model Motor: " . $booking['vehicle_model'] . "\n";
                $adminMessage .= "Tahun: " . $booking['vehicle_year'] . "\n";
                $adminMessage .= "Tanggal Booking: " . date('d/m/Y H:i', strtotime($booking['booking_date'])) . "\n";
                $adminMessage .= "Tanggal Preferred: " . date('d/m/Y', strtotime($booking['preferred_date'])) . "\n";
                $adminMessage .= "Total: Rp " . number_format($booking['total_price'], 0, ',', '.') . "\n";
                $adminMessage .= "Tanggal Upload Bukti: " . date('d/m/Y H:i') . "\n\n";
                $adminMessage .= "Status: *MENUNGGU VERIFIKASI PEMBAYARAN*\n\n";
                $adminMessage .= "Silakan verifikasi pembayaran di admin panel.\n\n";
                $adminMessage .= "Website : yrteam.web.id/admin-bookings.php";

                sendWhatsAppMessageWithFallback('62859106545737', $adminMessage);
            }

            return [
                'success' => true,
                'filename' => $filename,
                'message' => 'Bukti pembayaran berhasil diupload'
            ];
        }
    }

    return ['success' => false, 'message' => 'Gagal mengupload bukti pembayaran'];
}

/**
 * Verify payment
 */
function verifyPayment($paymentId, $status, $notes = '') {
    global $conn;

    $admin = getCurrentAdmin();
    $verifiedAt = ($status === 'verified') ? date('Y-m-d H:i:s') : null;

    $sql = "UPDATE payments SET status = ?, verified_by = ?, verified_at = ?, notes = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sisss", $status, $admin['id'], $verifiedAt, $notes, $paymentId);

    if ($stmt->execute()) {
        // Get booking info for WhatsApp notification
        $paymentSql = "SELECT p.*, b.booking_id, u.full_name, u.phone, u.email,
                              b.service_type, b.vehicle_model, b.vehicle_year
                      FROM payments p
                      JOIN bookings b ON p.booking_id = b.id
                      JOIN users u ON b.user_id = u.id
                      WHERE p.id = ?";
        $paymentStmt = $conn->prepare($paymentSql);
        $paymentStmt->bind_param("i", $paymentId);
        $paymentStmt->execute();
        $paymentResult = $paymentStmt->get_result();

        if ($paymentResult->num_rows > 0) {
            $payment = $paymentResult->fetch_assoc();

            // Send WhatsApp notification
            $message = "*YR Team - Status Pembayaran*\n\n";
            $message .= "Halo *" . $payment['full_name'] . "*,\n\n";

            if ($status === 'verified') {
                $message .= "✅ PEMBAYARAN ANDA TELAH DIVERIFIKASI!\n\n";
                $message .= "ID Booking: *" . $payment['booking_id'] . "*\n";
                $message .= "Jumlah: Rp " . number_format($payment['amount'], 0, ',', '.') . "\n";
                $message .= "Status: *VERIFIED*\n";
                $message .= "Diverifikasi pada: " . date('d/m/Y H:i') . "\n\n";

                // Generate warranty ID for verified payment
                $warrantyId = generateWarrantyId();
                $warrantyDays = 7; // 7 hari garansi

                // Create warranty record
                $warrantyResult = addWarranty($warrantyId, $payment['full_name'], $payment['phone'],
                                            $payment['vehicle_model'], $warrantyDays);

                if ($warrantyResult['success']) {
                    $message .= "🎉 *GARANSI AKTIF!*\n\n";
                    $message .= "ID Garansi: *" . $warrantyId . "*\n";
                    $message .= "Masa Berlaku: " . $warrantyDays . " Hari\n";
                    $message .= "Berlaku s/d: " . date('d/m/Y', strtotime("+$warrantyDays days")) . "\n\n";
                    $message .= "*SIMPAN ID GARANSI ANDA*\n";
                    $message .= "Gunakan ID ini untuk cek masa aktif garansi kapan saja.\n\n";
                }

                $message .= "Booking Anda akan segera diproses.";
            } else {
                $message .= "❌ PEMBAYARAN DITOLAK\n\n";
                $message .= "ID Booking: *" . $payment['booking_id'] . "*\n";
                $message .= "Jumlah: Rp " . number_format($payment['amount'], 0, ',', '.') . "\n";
                $message .= "Status: *REJECTED*\n";
                if ($notes) {
                    $message .= "Catatan: $notes\n";
                }
                $message .= "\nSilakan upload ulang bukti pembayaran yang valid.";
            }

            $message .= "\n\nWebsite : yrteam.web.id";

            sendWhatsAppMessageWithFallback($payment['phone'], $message);
        }

        return ['success' => true, 'message' => 'Status pembayaran berhasil diupdate'];
    }

    return ['success' => false, 'message' => 'Gagal mengupdate status pembayaran'];
}

/**
 * Get payment statistics
 */
function getPaymentStats() {
    global $conn;

    $sql = "SELECT
                COUNT(*) as total_payments,
                SUM(CASE WHEN status = 'verified' THEN 1 ELSE 0 END) as verified_payments,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_payments,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_payments,
                SUM(CASE WHEN status = 'verified' THEN amount ELSE 0 END) as total_verified_amount
            FROM payments";

    $result = $conn->query($sql);
    return $result->fetch_assoc();
}

?>
