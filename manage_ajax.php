<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

require_once 'db.php';

header('Content-Type: text/plain');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add_department':
            $name = trim($_POST['new_department'] ?? '');
            if (empty($name)) {
                echo "❌ نام بخش نمی‌تواند خالی باشد.";
                exit;
            }
            $stmt = $pdo->prepare("INSERT INTO departments (name) VALUES (:name)");
            if ($stmt->execute([':name' => $name])) {
                echo "✅ بخش اضافه شد.";
            } else {
                echo "❌ این بخش قبلاً وجود دارد.";
            }
            break;

        case 'add_station':
            $name = trim($_POST['new_station'] ?? '');
            if (empty($name)) {
                echo "❌ نام ایستگاه نمی‌تواند خالی باشد.";
                exit;
            }
            $stmt = $pdo->prepare("INSERT INTO stations (name) VALUES (:name)");
            if ($stmt->execute([':name' => $name])) {
                echo "✅ ایستگاه اضافه شد.";
            } else {
                echo "❌ این ایستگاه قبلاً وجود دارد.";
            }
            break;

        case 'add_type':
            $name = trim($_POST['new_type'] ?? '');
            if (empty($name)) {
                echo "❌ نام نوع کارت‌ریج نمی‌تواند خالی باشد.";
                exit;
            }
            $stmt = $pdo->prepare("INSERT INTO cartridge_types (name) VALUES (:name)");
            if ($stmt->execute([':name' => $name])) {
                echo "✅ نوع کارت‌ریج اضافه شد.";
            } else {
                echo "❌ این نوع قبلاً وجود دارد.";
            }
            break;

        default:
            echo "❌ عملیات ناشناخته.";
    }
    exit;
}
