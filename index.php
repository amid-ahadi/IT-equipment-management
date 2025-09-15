<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

require_once 'db.php';

// دریافت لیست بخش‌ها
$departments = [];
$stmt = $pdo->query("SELECT name FROM departments ORDER BY name ASC");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $departments[] = $row['name'];
}

// دریافت لیست ایستگاه‌ها
$stations = [];
$stmt = $pdo->query("SELECT name FROM stations ORDER BY name ASC");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $stations[] = $row['name'];
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>ثبت وضعیت کارت‌ریج</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <a href="logout.php" style="float: left; padding: 8px 16px; background: #dc3545; color: white; text-decoration: none; border-radius: 5px;">🚪 خروج</a>
        <a href="change_password.php" style="float: left; padding: 8px 16px; background: #6c757d; color: white; text-decoration: none; border-radius: 5px; margin-left: 10px;">🔐 تغییر رمز عبور</a>
        <h2>ثبت وضعیت کارت‌ریج</h2>

        <button class="btn" onclick="openModal()">➕ مدیریت بخش‌ها و ایستگاه‌ها</button>
        <button class="btn" onclick="openBulkModal()">➕ ثبت انبوه کارت‌ریج</button>
        <a href="report.php" class="btn report-btn">📊 گزارش‌گیری و نمودار</a>

        <form id="cartridgeForm">
            <label>بخش:</label>
            <select name="department" id="departmentSelect" required>
                <option value="">-- انتخاب بخش --</option>
                <?php foreach ($departments as $dept): ?>
                    <option><?= htmlspecialchars($dept) ?></option>
                <?php endforeach; ?>
            </select>

            <label>ایستگاه:</label>
            <select name="station" id="stationSelect" required>
                <option value="">-- انتخاب ایستگاه --</option>
                <?php foreach ($stations as $station): ?>
                    <option><?= htmlspecialchars($station) ?></option>
                <?php endforeach; ?>
            </select>

            <label>نوع کارت‌ریج:</label>
            <select name="type" id="typeSelect" required>
                <option value="">-- انتخاب نوع --</option>
                <!-- از JS بارگذاری می‌شود -->
            </select>

            <label>وضعیت:</label>
            <select name="status" required>
                <option value="">-- انتخاب وضعیت --</option>
                <option>Full</option>
                <option>Empty</option>
            </select>

            <label>تاریخ تعویض (میلادی):</label>
            <input type="date" name="replaced_date" value="<?php echo date('Y-m-d'); ?>" required>

            <input type="submit" value="ثبت">
        </form>

        <p class="success" id="successMsg"></p>
        <p class="error" id="errorMsg"></p>

        <h3>۱۰ ثبت اخیر:</h3>
        <table id="recentTable">
            <thead>
                <tr>
                    <th>شناسه</th>
                    <th>بخش</th>
                    <th>ایستگاه</th>
                    <th>نوع</th>
                    <th>وضعیت</th>
                    <th>تاریخ</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <!-- Modal: مدیریت بخش/ایستگاه/نوع -->
    <div id="modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">×</span>
            <h3>افزودن بخش، ایستگاه یا نوع کارت‌ریج</h3>

            <form id="addDeptForm">
                <input type="text" name="new_department" placeholder="نام بخش جدید" required>
                <input type="submit" value="افزودن بخش">
            </form>

            <hr>

            <form id="addStationForm">
                <input type="text" name="new_station" placeholder="نام ایستگاه جدید" required>
                <input type="submit" value="افزودن ایستگاه">
            </form>

            <hr>

            <form id="addTypeForm">
                <input type="text" name="new_type" placeholder="نام نوع کارت‌ریج جدید (مثال: 120 AHP)" required>
                <input type="submit" value="افزودن نوع کارت‌ریج">
            </form>

            <p class="success" id="modalSuccess"></p>
        </div>
    </div>

    <!-- Modal: ثبت انبوه کارت‌ریج -->
    <div id="bulkModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeBulkModal()">×</span>
            <h3>➕ ثبت انبوه کارت‌ریج (پر شده)</h3>
            <p>تعداد کارت‌ریج‌های پر شده (Full) که به سیستم برگشتند را وارد کنید.</p>

            <form id="bulkForm">
                <div id="bulkItemsContainer">
                    <div class="loading">در حال بارگذاری انواع کارت‌ریج...</div>
                </div>

                <label>تاریخ بازگشت:</label>
                <input type="date" name="replaced_date" value="<?php echo date('Y-m-d'); ?>" required>

                <label>وضعیت:</label>
                <select name="status" disabled style="background-color: #f0f0f0; color: #555;">
                    <option value="Full" selected>پر شده (Full)</option>
                </select>

                <div style="margin: 20px 0; text-align: center;">
                    <button type="submit" class="btn-primary" style="width: 100%;">✅ ثبت انبوه</button>
                </div>
            </form>

            <p class="success" id="bulkSuccess"></p>
            <p class="error" id="bulkError"></p>
        </div>
    </div>

    <script src="script.js"></script>
    <?php include 'footer.php'; ?>
</body>
</html>
