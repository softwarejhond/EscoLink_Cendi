<?php
require_once __DIR__ . '/../../controller/conexion.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

$currentRol = $_SESSION['rol'] ?? null;
$currentUser = $_SESSION['username'] ?? null;

if ($currentRol == 5 || !$currentUser) {
    echo json_encode(['success' => false, 'message' => 'No tienes permisos para realizar esta asignación.']);
    exit;
}

$createTable = "CREATE TABLE IF NOT EXISTS `group_teachers_extra` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `teacher_username` int(11) NOT NULL COMMENT 'username (documento) del docente',
    `grade_level` varchar(20) NOT NULL,
    `assigned_by` int(11) DEFAULT NULL COMMENT 'username de quien asigno',
    `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_teacher_group` (`teacher_username`, `grade_level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$conn->query($createTable);

$grade_level = isset($_POST['grade_level']) ? trim($_POST['grade_level']) : '';
$teacher_usernames = isset($_POST['teacher_usernames']) ? $_POST['teacher_usernames'] : [];

if (empty($grade_level)) {
    echo json_encode(['success' => false, 'message' => 'Grupo no especificado.']);
    exit;
}

if (!is_array($teacher_usernames)) {
    $teacher_usernames = [];
}

$teacher_usernames = array_map('intval', $teacher_usernames);
$teacher_usernames = array_filter($teacher_usernames, function ($u) { return $u > 0; });

$conn->begin_transaction();

try {
    $deleteStmt = $conn->prepare("DELETE FROM group_teachers_extra WHERE grade_level = ?");
    $deleteStmt->bind_param("s", $grade_level);
    $deleteStmt->execute();
    $deleteStmt->close();

    if (!empty($teacher_usernames)) {
        $insertStmt = $conn->prepare("INSERT INTO group_teachers_extra (teacher_username, grade_level, assigned_by) VALUES (?, ?, ?)");
        foreach ($teacher_usernames as $tu) {
            $insertStmt->bind_param("isi", $tu, $grade_level, $currentUser);
            $insertStmt->execute();
        }
        $insertStmt->close();
    }

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Docentes asignados correctamente.',
        'assigned_count' => count($teacher_usernames)
    ]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error al guardar la asignación: ' . $e->getMessage()]);
}
