<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');
ob_start();

require_once __DIR__ . '/../../controller/conexion.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metodo no permitido']);
    exit;
}

$studentId       = $_POST['student_id']       ?? '';
$gradeLevel      = $_POST['grade_level']      ?? '';
$classDate       = $_POST['class_date']       ?? '';
$observationType = $_POST['observation_type'] ?? '';
$observationText = $_POST['observation_text'] ?? '';
$createdBy       = $_SESSION['username']       ?? 'usuario_desconocido';

if (empty($studentId) || empty($gradeLevel) || empty($classDate) || empty($observationType)) {
    echo json_encode([
        'success' => false,
        'message' => 'Parametros obligatorios faltantes'
    ]);
    exit;
}

try {
    if (!$conn) {
        throw new Exception("Error de conexion a la base de datos");
    }

    // Insertar observacion (permite multiples por clase, sin UNIQUE constraint)
    $sql = "INSERT INTO class_observations (student_id, grade_level, course_type, class_date, observation_type, observation_text, created_by)
            VALUES (?, ?, 'GENERAL', ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Error en la preparacion de consulta: ' . $conn->error);
    }

    $stmt->bind_param("ssssss", $studentId, $gradeLevel, $classDate, $observationType, $observationText, $createdBy);

    if (!$stmt->execute()) {
        throw new Exception('Error al ejecutar consulta: ' . $stmt->error);
    }

    $stmt->close();

    ob_clean();
    echo json_encode([
        'success' => true,
        'message' => 'Observacion guardada correctamente'
    ]);
} catch (Exception $e) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

ob_end_flush();
