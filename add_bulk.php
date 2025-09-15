<?php
session_start();
if (!isset($_SESSION['user_id']) || !$_SESSION['username']) {
    die("❌ دسترسی غیرمجاز.");
}

require_once 'db.php';

header('Content-Type: text/plain');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $_POST['action'] !== 'bulk_add') {
    die("❌ عملیات نامعتبر.");
}

$status = trim($_POST['status'] ?? '');
if ($status !== 'Full') {
    echo "❌ فقط کارت‌ریج‌های پر شده (Full) می‌توانند به صورت انبوه ثبت شوند.";
    exit;
}

$replaced_date = $_POST['replaced_date'] ?? '';
if (empty($replaced_date)) {
    echo "❌ تاریخ بازگشت کارت‌ریج الزامی است.";
    exit;
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $replaced_date)) {
    echo "❌ تاریخ وارد شده معتبر نیست.";
    exit;
}

$types = $_POST['types'] ?? [];
if (empty($types) || !is_array($types)) {
    echo "❌ هیچ نوع کارت‌ریجی انتخاب نشده است.";
    exit;
}

$addedCount = 0;

// ✅ بخش و ایستگاه اختصاصی برای ثبت انبوه — فقط برای انبار IT
$department = 'IT';
$station = 'انبار مرکزی IT';

// ✅ اضافه کردن نوع به cartridge_types (اگر وجود نداشت)
foreach ($types as $type => $count) {
    $stmtType = $pdo->prepare("INSERT IGNORE INTO cartridge_types (name) VALUES (:type)");
    $stmtType->execute([':type' => $type]);
}

// ✅ ثبت رکورد در cartridges — برای هر کارت‌ریج یک رکورد مستقل
foreach ($types as $type => $count) {
    $count = (int)$count;
    if ($count <= 0) continue;

    for ($i = 0; $i < $count; $i++) {
        $stmt = $pdo->prepare("
            INSERT INTO cartridges (department, station, type, status, replaced_date)
            VALUES (?, ?, ?, ?, ?)
        ");
        $result = $stmt->execute([$department, $station, $type, $status, $replaced_date]);

        if ($result) {
            $addedCount++;
        }
    }
}

if ($addedCount > 0) {
    echo "✅ " . $addedCount . " عدد کارت‌ریج پر شده از انبار IT ثبت شدند.";
} else {
    echo "❌ هیچ رکوردی ثبت نشد.";
}
