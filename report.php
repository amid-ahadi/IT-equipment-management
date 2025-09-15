<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

require_once 'db.php';

$stmt = $pdo->query("SELECT DISTINCT department FROM cartridges ORDER BY department");
$departments = $stmt->fetchAll(PDO::FETCH_COLUMN);

$stmt = $pdo->query("SELECT DISTINCT station FROM cartridges ORDER BY station");
$stations = $stmt->fetchAll(PDO::FETCH_COLUMN);

$stmt = $pdo->query("SELECT DISTINCT type FROM cartridges ORDER BY type");
$types = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>گزارش‌گیری کارت‌ریج</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .report-container { max-width: 1200px; margin: auto; padding: 20px; background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .filters { background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 30px; border: 1px solid #e9ecef; }
        .filter-group { display: flex; flex-wrap: wrap; gap: 15px; margin-bottom: 15px; }
        .filter-group label { font-weight: bold; color: #555; min-width: 100px; text-align: left; }
        .filter-group select, .filter-group input[type="date"] { flex: 1; min-width: 150px; padding: 8px; border: 1px solid #ddd; border-radius: 6px; }
        .btn-primary { background-color: #28a745 !important; border-color: #28a745 !important; margin-left: 10px; }
        .btn-primary:hover { background-color: #218838 !important; border-color: #1e7e34 !important; }
        .export-buttons { text-align: center; margin: 20px 0; }
        .export-btn { background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; margin: 0 5px; font-size: 14px; }
        .export-btn:hover { background: #0069d9; }
        .chart-btn { background: #6c757d; color: white; border: none; padding: 10px 15px; border-radius: 6px; cursor: pointer; margin-left: 15px; font-size: 14px; }
        .chart-btn:hover { background: #5a6268; }
        table#reportTable { width: 100%; margin-top: 20px; border-collapse: collapse; font-size: 14px; }
        table#reportTable th, table#reportTable td { padding: 12px; text-align: center; border-bottom: 1px solid #eee; }
        table#reportTable th { background-color: #007bff; color: white; font-weight: bold; }
        table#reportTable tr:nth-child(even) { background-color: #f8f9fa; }
        .loading { text-align: center; color: #666; font-style: italic; padding: 20px; }
        .source-tag { color: #6c757d; font-size: 12px; font-style: italic; }
        .back-to-home-btn {
    display: inline-block;
    margin: 15px auto 20px;
    padding: 10px 20px;
    background-color: #6c757d;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: bold;
    text-align: center;
    transition: background-color 0.3s ease;
}

.back-to-home-btn:hover {
    background-color: #5a6268;
}
    </style>
</head>
<body>
    <div class="report-container">
        <h1 style="text-align: center; color: #333;">📊 گزارش‌گیری کارت‌ریج</h1>
        <a href="index.php" class="back-to-home-btn">🏠 بازگشت به صفحه اصلی</a>

        <div class="filters">
            <h3>🔍 فیلترهای گزارش</h3>
            <div class="filter-group">
                <label>بخش:</label>
                <select id="filterDepartment">
                    <option value="">همه بخش‌ها</option>
                    <?php foreach ($departments as $dept): ?>
                        <option><?= htmlspecialchars($dept) ?></option>
                    <?php endforeach; ?>
                </select>

                <label>ایستگاه:</label>
                <select id="filterStation">
                    <option value="">همه ایستگاه‌ها</option>
                    <?php foreach ($stations as $station): ?>
                        <option><?= htmlspecialchars($station) ?></option>
                    <?php endforeach; ?>
                </select>

                <label>نوع:</label>
                <select id="filterType">
                    <option value="">همه انواع</option>
                    <?php foreach ($types as $type): ?>
                        <option><?= htmlspecialchars($type) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label>وضعیت:</label>
                <select id="filterStatus">
                    <option value="">همه وضعیت‌ها</option>
                    <option>Full</option>
                    <option>Empty</option>
                </select>

                <label>منبع:</label>
                <select id="filterSource">
                    <option value="">همه</option>
                    <option value="(انبار IT)">انبار IT</option>
                    <option value="عملیاتی (عادی)">عملیاتی (عادی)</option>
                </select>

                <label>از تاریخ:</label>
                <input type="date" id="filterDateFrom" value="<?php echo date('Y-m-01'); ?>">

                <label>تا تاریخ:</label>
                <input type="date" id="filterDateTo" value="<?php echo date('Y-m-d'); ?>">
            </div>

            <div class="filter-group">
                <button class="btn btn-primary" onclick="applyFilters()">به‌روزرسانی گزارش</button>
                <button class="btn btn-primary" onclick="resetFilters()">بازنشانی</button>
                <button class="chart-btn" onclick="goToCharts()">🎨 نمودارها را ببینید</button>
            </div>
        </div>

        <div class="export-buttons">
            <button class="export-btn" onclick="exportToExcel()">📥 صادرات Excel</button>
        </div>

        <h3>📋 داده‌های خام</h3>
        <table id="reportTable">
            <thead>
                <tr>
                    <th>شناسه</th>
                    <th>بخش</th>
                    <th>ایستگاه</th>
                    <th>نوع</th>
                    <th>وضعیت</th>
                    <th>تاریخ</th>
                    <th>منبع</th>
                </tr>
            </thead>
            <tbody>
                <tr><td colspan="7" class="loading">در حال بارگذاری گزارش...</td></tr>
            </tbody>
        </table>
    </div>

    <script src="script.js"></script>
    <?php include 'footer.php'; ?>
</body>
</html>
