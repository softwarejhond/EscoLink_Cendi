<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../../controller/conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['document_number'])) {
    $document_number = trim($_POST['document_number']);

    if (empty($document_number)) {
        echo json_encode(['success' => false, 'message' => 'Ingrese un numero de documento.']);
        exit;
    }

    $stmt = $conn->prepare("SELECT id, document_type, document_number, student_code, name, grade_level, gender, email, cell_phone, cell_phone2, address, barrio, comuna, city, sede, status, cedula_acudiente, nombre_acudiente, email_acudiente, telefono_acudiente FROM el_students WHERE document_number = ?");
    $stmt->bind_param("s", $document_number);
    $stmt->execute();

    $meta = $stmt->result_metadata();
    $row = [];
    $params = [];

    while ($field = $meta->fetch_field()) {
        $params[] = &$row[$field->name];
    }

    call_user_func_array([$stmt, 'bind_result'], $params);

    $studentData = null;
    if ($stmt->fetch()) {
        $studentData = [];
        foreach ($row as $key => $val) {
            $studentData[$key] = $val;
        }
    }
    $stmt->close();

    if ($studentData) {
        $stmt_reports = $conn->prepare("SELECT r.id, r.description, r.observation, r.start_date, r.end_date, r.file_name, r.created_at, r.updated_at, r.created_by, u.nombre AS reported_by_name FROM el_medical_reports r LEFT JOIN users u ON r.created_by = u.username WHERE r.student_id = ? ORDER BY r.created_at DESC");
        $stmt_reports->bind_param("i", $studentData['id']);
        $stmt_reports->execute();

        $meta_r = $stmt_reports->result_metadata();
        $report_row = [];
        $report_params = [];

        while ($field = $meta_r->fetch_field()) {
            $report_params[] = &$report_row[$field->name];
        }

        call_user_func_array([$stmt_reports, 'bind_result'], $report_params);

        $reports = [];
        while ($stmt_reports->fetch()) {
            $report = [];
            foreach ($report_row as $key => $val) {
                $report[$key] = $val;
            }
            $reports[] = $report;
        }
        $stmt_reports->close();

        echo json_encode([
            'success' => true,
            'student' => $studentData,
            'reports' => $reports
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Estudiante no encontrado.']);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Solicitud invalida.']);
}
