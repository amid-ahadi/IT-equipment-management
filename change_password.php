<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

require_once 'db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // بررسی فیلدهای خالی
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "همه فیلدها الزامی هستند.";
    } elseif ($new_password !== $confirm_password) {
        $error = "رمز جدید و تکرار آن مطابقت ندارند.";
    } elseif (strlen($new_password) < 6) {
        $error = "رمز جدید باید حداقل 6 کاراکتر باشد.";
    } else {
        // بررسی رمز فعلی
        $stmt = $pdo->prepare("SELECT password FROM users WHERE username = :username LIMIT 1");
        $stmt->execute([':username' => $_SESSION['username']]);
        $user = $stmt->fetch();

        if ($user && password_verify($current_password, $user['password'])) {
            // رمز فعلی درست است — رمز جدید را ذخیره کن
            $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
            $updateStmt = $pdo->prepare("UPDATE users SET password = :password WHERE username = :username");
            $result = $updateStmt->execute([
                ':password' => $hashed_new_password,
                ':username' => $_SESSION['username']
            ]);

            if ($result) {
                $success = "✅ رمز عبور شما با موفقیت تغییر کرد!";
                // اختیاری: کاربر را از سیستم خارج کنید (اختیاری)
                // session_destroy();
                // header('Location: login.php');
                // exit;
            } else {
                $error = "❌ خطای پایگاه داده. لطفاً دوباره تلاش کنید.";
            }
        } else {
            $error = "❌ رمز عبور فعلی اشتباه است.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تغییر رمز عبور</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { font-family: 'Tahoma', sans-serif; background: #f4f4f4; padding: 50px; }
        .container { max-width: 500px; margin: auto; background: white; padding: 40px; border-radius: 12px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #333; margin-bottom: 30px; }
        input[type="password"], input[type="submit"] {
            width: 100%;
            padding: 12px;
            margin: 12px 0;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
        }
        input[type="submit"] {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }
        input[type="submit"]:hover {
            background-color: #0069d9;
        }
        .error { color: red; text-align: center; margin: 15px 0; }
        .success { color: green; text-align: center; margin: 15px 0; }
        a.back-btn {
            display: block;
            margin-top: 20px;
            text-align: center;
            color: #6c757d;
            text-decoration: none;
        }
        a.back-btn:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>🔐 تغییر رمز عبور</h2>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST">
            <label>رمز عبور فعلی:</label>
            <input type="password" name="current_password" placeholder="رمز فعلی خود را وارد کنید" required>

            <label>رمز عبور جدید:</label>
            <input type="password" name="new_password" placeholder="رمز جدید را وارد کنید" required>

            <label>تکرار رمز جدید:</label>
            <input type="password" name="confirm_password" placeholder="رمز جدید را مجدداً وارد کنید" required>

            <input type="submit" value="✅ تغییر رمز">
        </form>

        <a href="index.php" class="back-btn">← بازگشت به صفحه اصلی</a>
    </div>
</body>
</html>
