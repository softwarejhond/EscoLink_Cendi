<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

require_once __DIR__ . '/../../controller/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metodo no permitido']);
    exit;
}

$studentId = $_POST['student_id'] ?? '';
$classDate = $_POST['class_date'] ?? '';
$gradeLevel = $_POST['grade_level'] ?? '';

if (empty($studentId) || empty($classDate)) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos']);
    exit;
}

$sql = "SELECT 1 FROM el_medical_reports mr
        JOIN el_students es ON mr.student_id = es.id
        WHERE es.document_number = ?
        AND ? BETWEEN mr.start_date AND mr.end_date
        LIMIT 1";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Error en la consulta: ' . $conn->error]);
    exit;
}

$stmt->bind_param('ss', $studentId, $classDate);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode(['success' => true, 'has_incapacidad' => true]);
} else {
    echo json_encode(['success' => true, 'has_incapacidad' => false]);
}

$stmt->close();
