<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

require_once __DIR__ . '/../../controller/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metodo no permitido']);
    exit;
}

$studentId = (int)($_POST['student_id'] ?? 0);
$classDate = trim($_POST['class_date'] ?? '');

if ($studentId <= 0 || empty($classDate)) {
    echo json_encode(['success' => false, 'has_incapacidad' => false, 'debug' => ['student_id' => $studentId, 'class_date' => $classDate, 'error' => 'invalid_params']]);
    exit;
}

$sql = "SELECT id, description, start_date, end_date FROM el_medical_reports 
        WHERE student_id = ? 
        AND ? BETWEEN start_date AND end_date 
        LIMIT 1";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'has_incapacidad' => false, 'debug' => ['error' => $conn->error]]);
    exit;
}

$stmt->bind_param('is', $studentId, $classDate);
$stmt->execute();
$result = $stmt->get_result();

$debug = [
    'student_id_recibido' => $studentId,
    'class_date_recibida' => $classDate,
    'sql_where' => "student_id = $studentId AND '$classDate' BETWEEN start_date AND end_date"
];

if ($result && $row = $result->fetch_assoc()) {
    echo json_encode([
        'success' => true,
        'has_incapacidad' => true,
        'incapacidad' => $row,
        'debug' => $debug
    ]);
} else {
    echo json_encode([
        'success' => true,
        'has_incapacidad' => false,
        'debug' => $debug
    ]);
}

$stmt->close();
