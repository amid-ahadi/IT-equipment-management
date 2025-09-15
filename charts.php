<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

require_once 'db.php';

// دریافت پارامترهای فیلتر از URL
$department = $_GET['department'] ?? '';
$station = $_GET['station'] ?? '';
$type = $_GET['type'] ?? '';
$status = $_GET['status'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$source = $_GET['source'] ?? '';

// ساخت کوئری برای داده‌های نمودار
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

if ($source === '(انبار IT)') {
    $whereConditions[] = "department = 'IT' AND station = 'انبار مرکزی IT'";
} elseif ($source === 'عملیاتی (عادی)') {
    $whereConditions[] = "NOT (department = 'IT' AND station = 'انبار مرکزی IT')";
}

$whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

// دریافت داده‌های نمودار
$stmt = $pdo->prepare("SELECT COUNT(*) as full_count FROM cartridges $whereClause AND status = 'Full'");
$stmt->execute($params);
$full_count = $stmt->fetch()['full_count'];

$stmt = $pdo->prepare("SELECT COUNT(*) as empty_count FROM cartridges $whereClause AND status = 'Empty'");
$stmt->execute($params);
$empty_count = $stmt->fetch()['empty_count'];

$stmt = $pdo->prepare("
    SELECT MONTH(replaced_date) as month, COUNT(*) as count 
    FROM cartridges $whereClause 
    GROUP BY MONTH(replaced_date) 
    ORDER BY MONTH(replaced_date)
");
$stmt->execute($params);
$monthly = $stmt->fetchAll();

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

// دریافت داده‌های جدول برای نمایش زیر نمودارها (اختیاری)
$stmt = $pdo->prepare("
    SELECT id AS printer_id, department, station, type, status, replaced_date 
    FROM cartridges $whereClause 
    ORDER BY replaced_date DESC 
    LIMIT 10
");
$stmt->execute($params);
$records = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>نمودارهای کارت‌ریج</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { font-family: 'Tahoma', sans-serif; background: #f4f4f4; padding: 20px; direction: rtl; }
        .container { max-width: 1200px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        h1 { text-align: center; color: #333; margin-bottom: 30px; }
        .chart-section { margin: 40px 0; }
        .chart-container { height: 400px; background: #fff; border-radius: 10px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 30px; }
        .back-btn { 
            display: inline-block; 
            padding: 10px 20px; 
            background: #6c757d; 
            color: white; 
            text-decoration: none; 
            border-radius: 6px; 
            margin-bottom: 20px; 
            font-size: 16px;
        }
        .back-btn:hover { background: #5a6268; }
        table#summaryTable { width: 100%; margin-top: 30px; border-collapse: collapse; }
        table#summaryTable th, table#summaryTable td { padding: 12px; text-align: center; border-bottom: 1px solid #eee; }
        table#summaryTable th { background-color: #007bff; color: white; }
        table#summaryTable tr:nth-child(even) { background-color: #f8f9fa; }
        .stats { display: flex; justify-content: space-around; margin: 30px 0; }
        .stat-box { text-align: center; padding: 15px; background: #e8f5e9; border-radius: 8px; border-left: 4px solid #4CAF50; }
        .stat-box span { display: block; font-size: 24px; font-weight: bold; color: #28a745; }
        .stat-box label { font-size: 14px; color: #555; }
    </style>
</head>
<body>
    <div class="container">
        <a href="report.php" class="back-btn">← بازگشت به گزارش</a>
        <h1>📈 نمودارهای کارت‌ریج</h1>

        <!-- آمار کلی -->
        <div class="stats">
            <div class="stat-box">
                <span><?= $full_count ?></span>
                <label>کارت‌ریج پر شده</label>
            </div>
            <div class="stat-box">
                <span><?= $empty_count ?></span>
                <label>کارت‌ریج خالی</label>
            </div>
            <div class="stat-box">
                <span><?= $full_count + $empty_count ?></span>
                <label>مجموع</label>
            </div>
        </div>

        <!-- نمودار وضعیت -->
        <div class="chart-section">
            <h3>📊 توزیع وضعیت کارت‌ریج‌ها</h3>
            <div class="chart-container">
                <canvas id="statusChart"></canvas>
            </div>
        </div>

        <!-- نمودار ماهانه -->
        <div class="chart-section">
            <h3>📅 تعداد تعویض‌ها بر اساس ماه</h3>
            <div class="chart-container">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>

        <!-- نمودار نوع -->
        <div class="chart-section">
            <h3>🏷️ توزیع نوع کارت‌ریج</h3>
            <div class="chart-container">
                <canvas id="typeChart"></canvas>
            </div>
        </div>

        <!-- جدول نمونه (اختیاری) -->
        <h3>📋 ۱۰ ثبت اخیر</h3>
        <table id="summaryTable">
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
            <tbody>
                <?php if (count($records) > 0): ?>
                    <?php foreach ($records as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['printer_id']) ?></td>
                            <td><?= htmlspecialchars($row['department']) ?></td>
                            <td><?= htmlspecialchars($row['station']) ?></td>
                            <td><?= htmlspecialchars($row['type']) ?></td>
                            <td><?= htmlspecialchars($row['status']) ?></td>
                            <td><?= htmlspecialchars($row['replaced_date']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align: center;">هیچ داده‌ای یافت نشد.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script src="chart.js"></script>
    <script>
        // تبدیل اطلاعات PHP به JS
        const chartData = {
            status: {
                labels: ['Full', 'Empty'],
                data: [<?= $full_count ?>, <?= $empty_count ?>]
            },
            monthly: {
                labels: [
                    'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور',
                    'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'
                ],
                data: [
                    <?php
                    $monthlyData = array_fill(0, 12, 0);
                    foreach ($monthly as $item) {
                        $monthIndex = intval($item['month']) - 1;
                        $monthlyData[$monthIndex] = $item['count'];
                    }
                    echo implode(',', $monthlyData);
                    ?>
                ]
            },
            types: {
                labels: [],
                data: []
            }
        };

        <?php foreach ($types as $type => $count): ?>
            chartData.types.labels.push('<?= addslashes($type) ?>');
            chartData.types.data.push(<?= $count ?>);
        <?php endforeach; ?>

        // نمودار وضعیت
        new Chart(document.getElementById('statusChart'), {
            type: 'pie',
            data: {
                labels: chartData.status.labels,
                datasets: [{
                    data: chartData.status.data,
                    backgroundColor: ['#28a745', '#dc3545'],
                    borderColor: ['#218838', '#c82333'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: { callbacks: { label: (context) => `${context.label}: ${context.raw}` } }
                }
            }
        });

        // نمودار ماهانه
        new Chart(document.getElementById('monthlyChart'), {
            type: 'bar',
            data: {
                labels: chartData.monthly.labels,
                datasets: [{
                    label: 'تعداد تعویض‌ها',
                    data: chartData.monthly.data,
                    backgroundColor: '#17a2b8',
                    borderColor: '#138496',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true, title: { display: true, text: 'تعداد' } },
                    x: { title: { display: true, text: 'ماه' } }
                },
                plugins: { legend: { display: false } }
            }
        });

        // نمودار نوع
        new Chart(document.getElementById('typeChart'), {
            type: 'doughnut',
            data: {
                labels: chartData.types.labels,
                datasets: [{
                    data: chartData.types.data,
                    backgroundColor: [
                        '#ffc107', '#28a745', '#007bff', '#dc3545', '#6f42c1', '#fd7e14'
                    ],
                    borderColor: [
                        '#e0a800', '#218838', '#0069d9', '#c82333', '#5a3294', '#e67e22'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: { callbacks: { label: (context) => `${context.label}: ${context.raw}` } }
                }
            }
        });
    </script>
    <?php include 'footer.php'; ?>
</body>
</html>
