<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../controller/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metodo no permitido']);
    exit;
}

$studentId  = $_POST['student_id']  ?? '';
$gradeLevel = $_POST['grade_level'] ?? '';
$classDate  = $_POST['class_date']  ?? '';

if (empty($studentId) || empty($gradeLevel) || empty($classDate)) {
    echo json_encode(['success' => false, 'message' => 'Parametros faltantes']);
    exit;
}

try {
    $sql = "SELECT co.observation_type, co.observation_text, co.created_by,
            u.nombre as created_by_name, co.created_at
            FROM class_observations co
            LEFT JOIN users u ON co.created_by = u.username
            WHERE co.student_id = ? AND co.grade_level = ? AND co.class_date = ?
            ORDER BY co.created_at ASC";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Error en la preparacion: ' . $conn->error);
    }

    $stmt->bind_param("sss", $studentId, $gradeLevel, $classDate);
    if (!$stmt->execute()) {
        throw new Exception('Error al ejecutar: ' . $stmt->error);
    }

    $result = $stmt->get_result();
    $observations = [];
    while ($row = $result->fetch_assoc()) {
        $observations[] = $row;
    }
    $stmt->close();

    echo json_encode([
        'success' => true,
        'data' => $observations
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
