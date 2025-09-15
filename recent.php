<?php
header('Content-Type: application/json');

require_once 'db.php';

$stmt = $pdo->prepare("
    SELECT id AS printer_id, department, station, type, status, replaced_date 
    FROM cartridges 
    ORDER BY replaced_date DESC 
    LIMIT 10
");
$stmt->execute();
$records = $stmt->fetchAll();
echo json_encode($records);
