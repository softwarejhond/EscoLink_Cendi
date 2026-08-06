<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include '../../controller/conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $report_id = (int)$_POST['report_id'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    if (empty($report_id) || empty($start_date) || empty($end_date)) {
        echo json_encode(['success' => false, 'message' => 'Campos obligatorios faltantes.']);
        exit;
    }

    if ($start_date > $end_date) {
        echo json_encode(['success' => false, 'message' => 'La fecha de inicio no puede ser mayor a la fecha de fin.']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE el_medical_reports SET start_date = ?, end_date = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
    $stmt->bind_param("ssi", $start_date, $end_date, $report_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Fechas actualizadas correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se encontro el registro o no hubo cambios.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar: ' . $stmt->error]);
    }
    $stmt->close();

} else {
    echo json_encode(['success' => false, 'message' => 'Solicitud invalida.']);
}
