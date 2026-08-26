<?php
require_once __DIR__ . '/../../controller/conexion.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    exit(json_encode(['success' => false, 'message' => 'No autorizado']));
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false, 'message' => 'Método no permitido']));
}

$id            = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : null;
$document_type = $_POST['document_type'] ?? '';
$simat         = trim($_POST['simat'] ?? '');
$document_number = trim($_POST['document_number'] ?? '');
$student_code  = trim($_POST['student_code'] ?? '');
$name          = trim($_POST['name'] ?? '');
$grade_level   = trim($_POST['grade_level'] ?? '');
$gender        = $_POST['gender'] ?? '';
$email         = trim($_POST['email'] ?? '');
$cell_phone    = trim($_POST['cell_phone'] ?? '');
$cell_phone2   = trim($_POST['cell_phone2'] ?? '');
$address       = trim($_POST['address'] ?? '');
$barrio        = trim($_POST['barrio'] ?? '');
$comuna        = trim($_POST['comuna'] ?? '');
$city          = trim($_POST['city'] ?? '');
$sede          = trim($_POST['sede'] ?? '');
$status        = $_POST['status'] ?? 'ACTIVO';
$registration_date = trim($_POST['registration_date'] ?? '') ?: null;
$updated_by    = $_SESSION['username'] ?? 'system';
$cedula_acudiente  = trim($_POST['cedula_acudiente'] ?? '');
$nombre_acudiente  = trim($_POST['nombre_acudiente'] ?? '');
$email_acudiente   = trim($_POST['email_acudiente'] ?? '');
$telefono_acudiente = trim($_POST['telefono_acudiente'] ?? '');

// Validación de campos obligatorios
if (!$document_number || !$name || !$grade_level || !$document_type || !$gender || !$status) {
    exit(json_encode(['success' => false, 'message' => 'Faltan campos obligatorios']));
}

// Tipos de documento válidos
$valid_doc_types = ['TI', 'CC', 'CE', 'RC', 'PAS'];
if (!in_array($document_type, $valid_doc_types, true)) {
    exit(json_encode(['success' => false, 'message' => 'Tipo de documento inválido']));
}

// Géneros válidos
$valid_genders = ['M', 'F', 'OTRO'];
if (!in_array($gender, $valid_genders, true)) {
    exit(json_encode(['success' => false, 'message' => 'Género inválido']));
}

// Estados válidos
$valid_statuses = ['ACTIVO', 'RETIRADO', 'PENDIENTE RETIRO', 'GRADUADO'];
if (!in_array($status, $valid_statuses, true)) {
    exit(json_encode(['success' => false, 'message' => 'Estado inválido']));
}

if ($id) {
    // Actualizar estudiante existente
    $stmt = $conn->prepare(
        "UPDATE el_students
         SET document_type=?, simat=?, document_number=?, student_code=?, name=?,
             grade_level=?, gender=?, email=?, cell_phone=?, cell_phone2=?,
             address=?, barrio=?, comuna=?, city=?, sede=?, status=?,
             registration_date=?, updated_by=?, cedula_acudiente=?, nombre_acudiente=?,
             email_acudiente=?, telefono_acudiente=?
         WHERE id=?"
    );
    $stmt->bind_param(
        'ssssssssssssssssssssssi',
        $document_type, $simat, $document_number, $student_code, $name,
        $grade_level, $gender, $email, $cell_phone, $cell_phone2,
        $address, $barrio, $comuna, $city, $sede, $status,
        $registration_date, $updated_by, $cedula_acudiente, $nombre_acudiente,
        $email_acudiente, $telefono_acudiente, $id
    );
} else {
    // Insertar nuevo estudiante
    $stmt = $conn->prepare(
        "INSERT INTO el_students
            (document_type, simat, document_number, student_code, name,
             grade_level, gender, email, cell_phone, cell_phone2,
             address, barrio, comuna, city, sede, status, registration_date, updated_by,
             cedula_acudiente, nombre_acudiente, email_acudiente, telefono_acudiente)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)"
    );
    $stmt->bind_param(
        'ssssssssssssssssssssss',
        $document_type, $simat, $document_number, $student_code, $name,
        $grade_level, $gender, $email, $cell_phone, $cell_phone2,
        $address, $barrio, $comuna, $city, $sede, $status,
        $registration_date, $updated_by, $cedula_acudiente, $nombre_acudiente,
        $email_acudiente, $telefono_acudiente
    );
}

if ($stmt->execute()) {
    $message = $id ? 'Estudiante actualizado correctamente' : 'Estudiante registrado correctamente';
    echo json_encode(['success' => true, 'message' => $message]);
} else {
    // Detectar duplicado de clave única
    if ($conn->errno === 1062) {
        echo json_encode(['success' => false, 'message' => 'El número de documento o código ya existe']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar: ' . $conn->error]);
    }
}
$stmt->close();
