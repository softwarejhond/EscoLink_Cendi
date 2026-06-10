<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../controller/conexion.php';

if (!isset($_POST['student_id']) || !isset($_POST['grade_level'])) {
    echo json_encode(['success' => false, 'message' => 'Faltan parametros necesarios']);
    exit;
}

$studentId = $_POST['student_id'];
$gradeLevel = $_POST['grade_level'];
$courseType = isset($_POST['course_type']) && trim($_POST['course_type']) !== '' ? trim($_POST['course_type']) : null;

try {
    if ($courseType !== null) {
        $sqlTotalClasses = "SELECT COUNT(DISTINCT class_date) AS total_classes
                             FROM attendance_records
                             WHERE grade_level = ? AND course_type = ? AND class_date <= CURRENT_DATE()";
        $stmtClasses = $conn->prepare($sqlTotalClasses);
        $stmtClasses->bind_param('ss', $gradeLevel, $courseType);
    } else {
        $sqlTotalClasses = "SELECT COUNT(DISTINCT class_date) AS total_classes
                             FROM attendance_records
                             WHERE grade_level = ? AND class_date <= CURRENT_DATE()";
        $stmtClasses = $conn->prepare($sqlTotalClasses);
        $stmtClasses->bind_param('s', $gradeLevel);
    }
    $stmtClasses->execute();
    $resultClasses = $stmtClasses->get_result();
    $rowClasses = $resultClasses->fetch_assoc();
    $totalClasses = (int)$rowClasses['total_classes'];

    if ($courseType !== null) {
        $sqlAttendance = "SELECT 
                            SUM(CASE WHEN attendance_status = 'presente' OR attendance_status = 'tarde' THEN 1 ELSE 0 END) AS total_attendance,
                            SUM(CASE WHEN attendance_status = 'ausente' THEN 1 ELSE 0 END) AS total_absences
                          FROM attendance_records
                          WHERE student_id = ?
                            AND grade_level = ?
                            AND course_type = ?
                            AND class_date <= CURRENT_DATE()";
        $stmtAttendance = $conn->prepare($sqlAttendance);
        $stmtAttendance->bind_param('sss', $studentId, $gradeLevel, $courseType);
    } else {
        $sqlAttendance = "SELECT 
                            SUM(CASE WHEN attendance_status = 'presente' OR attendance_status = 'tarde' THEN 1 ELSE 0 END) AS total_attendance,
                            SUM(CASE WHEN attendance_status = 'ausente' THEN 1 ELSE 0 END) AS total_absences
                          FROM attendance_records
                          WHERE student_id = ?
                            AND grade_level = ?
                            AND class_date <= CURRENT_DATE()";
        $stmtAttendance = $conn->prepare($sqlAttendance);
        $stmtAttendance->bind_param('ss', $studentId, $gradeLevel);
    }
    $stmtAttendance->execute();
    $resultAttendance = $stmtAttendance->get_result();
    $rowAttendance = $resultAttendance->fetch_assoc();

    $totalAttendance = (int)$rowAttendance['total_attendance'];
    $totalAbsences  = (int)$rowAttendance['total_absences'];

    if ($totalClasses > 0) {
        $attendancePercentage = round(($totalAttendance / $totalClasses) * 100, 1);
        $absencePercentage    = round(($totalAbsences / $totalClasses) * 100, 1);
    } else {
        $attendancePercentage = 0;
        $absencePercentage    = 0;
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'totalClasses'         => $totalClasses,
            'totalAttendance'      => $totalAttendance,
            'totalAbsences'        => $totalAbsences,
            'attendancePercentage' => $attendancePercentage,
            'absencePercentage'    => $absencePercentage,
            'absencesDisplay'      => $totalAbsences . '/' . $totalClasses
        ]
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener estadisticas de asistencia: ' . $e->getMessage()
    ]);
}
