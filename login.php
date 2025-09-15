<?php
session_start();
require_once 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'نام کاربری و رمز عبور الزامی هستند.';
    } else {
        $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = :username LIMIT 1");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: index.php');
            exit;
        } else {
            $error = 'نام کاربری یا رمز عبور اشتباه است.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>ورود به سیستم</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Tahoma', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            background: white;
            padding: 50px 40px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(102, 126, 234, 0.1) 0%, transparent 70%);
            z-index: -1;
            animation: rotate 20s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .logo {
            width: 80px;
            height: 80px;
            background: #f0f0f0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            font-size: 32px;
            color: #667eea;
            font-weight: bold;
        }

        h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 30px;
            font-weight: 600;
        }

        .input-group {
            margin-bottom: 20px;
            text-align: right;
        }

        .input-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 600;
            font-size: 14px;
        }

        .input-group input {
            width: 100%;
            padding: 16px;
            border: 1px solid #ddd;
            border-radius: 12px;
            font-size: 16px;
            direction: ltr;
            transition: all 0.3s ease;
        }

        .input-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
        }

        .captcha-container {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 25px;
            align-items: center;
        }

        .captcha-image {
            width: 120px;
            height: 50px;
            background: #f0f0f0;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: bold;
            color: #333;
            letter-spacing: 5px;
            user-select: none;
            cursor: pointer;
            border: 1px solid #ddd;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .captcha-image:hover {
            background: #e8f4ff;
            transform: scale(1.02);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }

        .captcha-refresh {
            color: #667eea;
            font-size: 14px;
            text-decoration: underline;
            cursor: pointer;
            margin-top: 5px;
        }

        .captcha-refresh:hover {
            color: #5a6fd8;
        }

        .btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
            transition: transform 0.2s, box-shadow 0.3s;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .error {
            color: #e74c3c;
            background: #fdf2f2;
            border: 1px solid #f8d7da;
            padding: 12px;
            border-radius: 10px;
            margin: 20px 0;
            font-size: 15px;
            direction: rtl;
        }

        .footer {
            margin-top: 30px;
            color: #888;
            font-size: 13px;
        }

        .footer a {
            color: #667eea;
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-container {
                padding: 40px 20px;
            }
            h1 {
                font-size: 24px;
            }
            .captcha-image {
                width: 100px;
                height: 45px;
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">🔒</div>
        <h1>ورود به سیستم</h1>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">
            <div class="input-group">
                <label>نام کاربری</label>
                <input type="text" name="username" placeholder="نام کاربری خود را وارد کنید"  required>
            </div>

            <div class="input-group">
                <label>رمز عبور</label>
                <input type="password" name="password" placeholder="رمز عبور خود را وارد کنید" required>
            </div>

            <div class="captcha-container">
                <div class="captcha-image" onclick="refreshCaptcha()">
                    <?= htmlspecialchars($_SESSION['captcha']) ?>
                </div>
                <div class="captcha-refresh" onclick="refreshCaptcha()">تغییر کد</div>
                <div class="input-group">
                    <input type="text" name="captcha" placeholder="کد امنیتی را وارد کنید" maxlength="4" required autocomplete="off">
                </div>
            </div>

            <button type="submit" class="btn">ورود</button>
        </form>

        <div class="footer">
            © ۲۰۲۵ — سیستم مدیریت منابع IT بيمارستان امام خميني <a href="https://c-security.ir" target="_blank">Coded by: Amid Ahadi</a>
        </div>
    </div>

    <script>
        function refreshCaptcha() {
            const captchaElement = document.querySelector('.captcha-image');
            fetch('generate_captcha.php')
                .then(response => response.text())
                .then(data => {
                    captchaElement.innerText = data;
                    document.querySelector('input[name="captcha"]').value = ''; // پاک کردن فیلد
                })
                .catch(() => {
                    // در صورت خطای شبکه، یک عدد تصادفی جدید بده
                    const newCaptcha = Math.floor(1000 + Math.random() * 9000);
                    captchaElement.innerText = newCaptcha;
                    document.querySelector('input[name="captcha"]').value = '';
                });
        }

        // بارگذاری اولیه
        document.addEventListener('DOMContentLoaded', () => {
            // اگر کاربر کد را تغییر دهد — اینجا هم اعمال شود
            document.querySelector('.captcha-image').addEventListener('click', refreshCaptcha);
        });
    </script>
</body>
</html>
