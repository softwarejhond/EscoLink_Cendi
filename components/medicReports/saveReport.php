<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include '../../controller/conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = (int)$_POST['student_id'];
    $description = trim($_POST['description']);
    $observation = trim($_POST['observation'] ?? '');
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $created_by = $_SESSION['username'] ?? 'sistema';

    if (empty($student_id) || empty($description) || empty($start_date) || empty($end_date)) {
        echo json_encode(['success' => false, 'message' => 'Campos obligatorios faltantes.']);
        exit;
    }

    if ($start_date > $end_date) {
        echo json_encode(['success' => false, 'message' => 'La fecha de inicio no puede ser mayor a la fecha de fin.']);
        exit;
    }

    $file_name = null;
    if (isset($_FILES['comprobante']) && $_FILES['comprobante']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../../uploads/incapacidades/';

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $original_name = $_FILES['comprobante']['name'];
        $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'pdf'];

        if (!in_array($ext, $allowed)) {
            echo json_encode(['success' => false, 'message' => 'Formato no permitido. Solo JPG, PNG y PDF.']);
            exit;
        }

        $max_size = 10 * 1024 * 1024;
        if ($_FILES['comprobante']['size'] > $max_size) {
            echo json_encode(['success' => false, 'message' => 'El archivo excede el tamano maximo de 10MB.']);
            exit;
        }

        $file_name = 'incapacidad_' . $student_id . '_' . time() . '.' . $ext;
        $target = $upload_dir . $file_name;

        if (!move_uploaded_file($_FILES['comprobante']['tmp_name'], $target)) {
            echo json_encode(['success' => false, 'message' => 'Error al guardar el archivo.']);
            exit;
        }
    }

    $stmt = $conn->prepare("INSERT INTO el_medical_reports (student_id, description, observation, start_date, end_date, file_name, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss", $student_id, $description, $observation, $start_date, $end_date, $file_name, $created_by);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Incapacidad registrada correctamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al registrar: ' . $stmt->error]);
    }
    $stmt->close();

} else {
    echo json_encode(['success' => false, 'message' => 'Solicitud invalida.']);
}
