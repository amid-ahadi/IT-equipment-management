<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $department = trim($_POST['department'] ?? '');
    $station = trim($_POST['station'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $status = $_POST['status'] ?? '';
    $replaced_date = $_POST['replaced_date'] ?? '';

    if (empty($department) || empty($station) || empty($type) || empty($status) || empty($replaced_date)) {
        echo "❌ همه فیلدها الزامی هستند.";
        exit;
    }

    // ✅ اضافه کردن نوع به cartridge_types (اگر وجود نداشت)
    $stmtType = $pdo->prepare("INSERT IGNORE INTO cartridge_types (name) VALUES (:type)");
    $stmtType->execute([':type' => $type]);

    // ✅ ثبت کارت‌ریج
    $stmt = $pdo->prepare("
        INSERT INTO cartridges (department, station, type, status, replaced_date)
        VALUES (:dept, :station, :type, :status, :date)
    ");
    $result = $stmt->execute([
        ':dept' => $department,
        ':station' => $station,
        ':type' => $type,
        ':status' => $status,
        ':date' => $replaced_date
    ]);

    if ($result) {
        echo "✅ کارت‌ریج با موفقیت ثبت شد.";
    } else {
        echo "❌ خطای پایگاه داده. لطفاً دوباره تلاش کنید.";
    }
    exit;
}
