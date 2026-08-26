<?php
require_once __DIR__ . '/../../controller/conexion.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

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

if (empty($grade_level)) {
    echo json_encode(['success' => false, 'message' => 'Grupo no especificado.']);
    exit;
}

$queryTeachers = "SELECT id, username, nombre, email FROM users WHERE rol = 14 AND orden = 1 ORDER BY nombre ASC";
$resultTeachers = $conn->query($queryTeachers);

$teachers = [];
if ($resultTeachers && $resultTeachers->num_rows > 0) {
    while ($row = $resultTeachers->fetch_assoc()) {
        $teachers[] = $row;
    }
}

$queryAssigned = "SELECT teacher_username FROM group_teachers_extra WHERE grade_level = ?";
$stmtAssigned = $conn->prepare($queryAssigned);
$stmtAssigned->bind_param("s", $grade_level);
$stmtAssigned->execute();
$resultAssigned = $stmtAssigned->get_result();

$assignedUsernames = [];
if ($resultAssigned && $resultAssigned->num_rows > 0) {
    while ($row = $resultAssigned->fetch_assoc()) {
        $assignedUsernames[] = $row['teacher_username'];
    }
}
$stmtAssigned->close();

echo json_encode([
    'success' => true,
    'teachers' => $teachers,
    'assigned_usernames' => $assignedUsernames
]);
