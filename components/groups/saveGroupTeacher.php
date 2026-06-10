<?php
require_once __DIR__ . '/../../controller/conexion.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// Verificar que el usuario tiene permisos (rol != 5)
$currentRol = $_SESSION['rol'] ?? null;
$currentUser = $_SESSION['username'] ?? null;

if ($currentRol == 5 || !$currentUser) {
    echo json_encode(['success' => false, 'message' => 'No tienes permisos para realizar esta asignación.']);
    exit;
}

// Crear la tabla group_teachers si no existe
$createTable = "CREATE TABLE IF NOT EXISTS `group_teachers` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `teacher_username` int(11) DEFAULT NULL COMMENT 'username (documento) del docente',
    `grade_level` varchar(20) NOT NULL,
    `assigned_by` int(11) DEFAULT NULL COMMENT 'username de quien asigno',
    `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_group` (`grade_level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$conn->query($createTable);

$grade_level = isset($_POST['grade_level']) ? trim($_POST['grade_level']) : '';
$teacher_username = isset($_POST['teacher_username']) ? trim($_POST['teacher_username']) : '';

if (empty($grade_level)) {
    echo json_encode(['success' => false, 'message' => 'Grupo no especificado.']);
    exit;
}

// Sanitizar
$teacher_username = $teacher_username !== '' ? (int)$teacher_username : null;

$conn->begin_transaction();

try {
    // Eliminar registro existente para este grupo
    $deleteStmt = $conn->prepare("DELETE FROM group_teachers WHERE grade_level = ?");
    $deleteStmt->bind_param("s", $grade_level);
    $deleteStmt->execute();
    $deleteStmt->close();

    // Insertar nueva asignacion (solo si se selecciono un maestro)
    if ($teacher_username !== null && $teacher_username > 0) {
        $insertStmt = $conn->prepare("INSERT INTO group_teachers (teacher_username, grade_level, assigned_by) VALUES (?, ?, ?)");
        $insertStmt->bind_param("isi", $teacher_username, $grade_level, $currentUser);
        $insertStmt->execute();
        $insertStmt->close();
    }

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => $teacher_username ? 'Maestro asignado correctamente.' : 'Maestro desasignado correctamente.'
    ]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error al guardar la asignación: ' . $e->getMessage()]);
}
