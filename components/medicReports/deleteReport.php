<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include '../../controller/conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report_id'])) {
    $report_id = (int)$_POST['report_id'];

    if ($report_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de reporte invalido.']);
        exit;
    }

    $stmt = $conn->prepare("SELECT file_name FROM el_medical_reports WHERE id = ?");
    $stmt->bind_param("i", $report_id);
    $stmt->execute();
    $stmt->bind_result($file_name);
    $stmt->fetch();
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM el_medical_reports WHERE id = ?");
    $stmt->bind_param("i", $report_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            if ($file_name) {
                $file_path = __DIR__ . '/../../uploads/incapacidades/' . $file_name;
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }
            echo json_encode(['success' => true, 'message' => 'Incapacidad eliminada correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se encontro el registro.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar: ' . $stmt->error]);
    }
    $stmt->close();

} else {
    echo json_encode(['success' => false, 'message' => 'Solicitud invalida.']);
}
