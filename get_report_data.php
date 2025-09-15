<?php
header('Content-Type: application/json');

session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

require_once 'db.php';

$department = $_GET['department'] ?? '';
$station = $_GET['station'] ?? '';
$type = $_GET['type'] ?? '';
$status = $_GET['status'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$source = $_GET['source'] ?? '';

$whereConditions = [];
$params = [];

if ($department) {
    $whereConditions[] = "department = :department";
    $params[':department'] = $department;
}
if ($station) {
    $whereConditions[] = "station = :station";
    $params[':station'] = $station;
}
if ($type) {
    $whereConditions[] = "type = :type";
    $params[':type'] = $type;
}
if ($status) {
    $whereConditions[] = "status = :status";
    $params[':status'] = $status;
}
if ($date_from && $date_to) {
    $whereConditions[] = "replaced_date BETWEEN :date_from AND :date_to";
    $params[':date_from'] = $date_from;
    $params[':date_to'] = $date_to;
} elseif ($date_from) {
    $whereConditions[] = "replaced_date >= :date_from";
    $params[':date_from'] = $date_from;
} elseif ($date_to) {
    $whereConditions[] = "replaced_date <= :date_to";
    $params[':date_to'] = $date_to;
}

// ✅ فیلتر منبع
if ($source === '(انبار IT)') {
    $whereConditions[] = "department = 'IT' AND station = 'انبار مرکزی IT'";
} elseif ($source === 'عملیاتی (عادی)') {
    $whereConditions[] = "NOT (department = 'IT' AND station = 'انبار مرکزی IT')";
}

$whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

$stmt = $pdo->prepare("
    SELECT id AS printer_id, department, station, type, status, replaced_date 
    FROM cartridges $whereClause 
    ORDER BY replaced_date DESC
");
$stmt->execute($params);
$records = $stmt->fetchAll();

// ✅ اضافه کردن نشانگر منبع به هر رکورد
foreach ($records as &$record) {
    if ($record['department'] === 'IT' && $record['station'] === 'انبار مرکزی IT') {
        $record['source'] = '(انبار IT)';
    } else {
        $record['source'] = '';
    }
}

// آمار کلی
$stmt = $pdo->prepare("SELECT COUNT(*) as full_count FROM cartridges $whereClause AND status = 'Full'");
$stmt->execute($params);
$full_count = $stmt->fetch()['full_count'];

$stmt = $pdo->prepare("SELECT COUNT(*) as empty_count FROM cartridges $whereClause AND status = 'Empty'");
$stmt->execute($params);
$empty_count = $stmt->fetch()['empty_count'];

// آمار ماهانه
$stmt = $pdo->prepare("
    SELECT MONTH(replaced_date) as month, COUNT(*) as count 
    FROM cartridges $whereClause 
    GROUP BY MONTH(replaced_date) 
    ORDER BY MONTH(replaced_date)
");
$stmt->execute($params);
$monthly = $stmt->fetchAll();

// آمار نوع کارت‌ریج
$stmt = $pdo->prepare("
    SELECT type, COUNT(*) as count 
    FROM cartridges $whereClause 
    GROUP BY type 
    ORDER BY count DESC
");
$stmt->execute($params);
$types = [];
foreach ($stmt->fetchAll() as $row) {
    $types[$row['type']] = $row['count'];
}

echo json_encode([
    'records' => $records,
    'stats' => [
        'full_count' => $full_count,
        'empty_count' => $empty_count,
        'total' => $full_count + $empty_count
    ],
    'monthly' => $monthly,
    'types' => $types
]);
