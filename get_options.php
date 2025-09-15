<?php
header('Content-Type: application/json');

require_once 'db.php';

$stmt_dept = $pdo->query("SELECT name FROM departments ORDER BY name ASC");
$departments = $stmt_dept->fetchAll(PDO::FETCH_COLUMN);

$stmt_station = $pdo->query("SELECT name FROM stations ORDER BY name ASC");
$stations = $stmt_station->fetchAll(PDO::FETCH_COLUMN);

$stmt_type = $pdo->query("SELECT name FROM cartridge_types ORDER BY name ASC");
$types = $stmt_type->fetchAll(PDO::FETCH_COLUMN);

echo json_encode([
    'departments' => $departments,
    'stations' => $stations,
    'types' => $types
]);
