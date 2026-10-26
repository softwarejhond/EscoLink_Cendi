<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../controller/conexion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Cache-Control: max-age=0');

set_time_limit(120);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido');
}

try {
    $spreadsheet = new Spreadsheet();

    // ==========================================
    // Funciones auxiliares
    // ==========================================

    /**
     * Obtener el estado de asistencia codificado
     */
    function getAttendanceCode($attendanceStatus) {
        switch ($attendanceStatus) {
            case 'presente':
                return 1;
            case 'ausente':
                return 0;
            case 'tarde':
                return 2;
            default:
                return '-'; // Sin registro
        }
    }

    /**
     * Aplicar relleno por tramos contiguos dentro de una fila.
     * $fills: [colorHex => [indices de columna, ...], ...]
     */
    function applyRowFills($sheet, $row, $fills) {
        if (empty($fills)) {
            return;
        }
        foreach ($fills as $color => $cols) {
            sort($cols);
            $count = count($cols);
            $start = $cols[0];
            $prev = $cols[0];
            for ($i = 1; $i < $count; $i++) {
                $cur = $cols[$i];
                if ($cur !== $prev + 1) {
                    $range = Coordinate::stringFromColumnIndex($start) . $row;
                    if ($prev !== $start) {
                        $range .= ':' . Coordinate::stringFromColumnIndex($prev) . $row;
                    }
                    $sheet->getStyle($range)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB($color);
                    $start = $cur;
                }
                $prev = $cur;
            }
            $range = Coordinate::stringFromColumnIndex($start) . $row;
            if ($prev !== $start) {
                $range .= ':' . Coordinate::stringFromColumnIndex($prev) . $row;
            }
            $sheet->getStyle($range)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB($color);
        }
    }

    // ==========================================
    // Consultar todos los estudiantes
    // ==========================================
    $sqlStudents = "SELECT document_type, document_number, name, email, cell_phone, grade_level, status
                    FROM el_students
                    ORDER BY grade_level ASC, name ASC";

    $resultStudents = $conn->query($sqlStudents);
    if (!$resultStudents) {
        throw new Exception('Error al consultar estudiantes: ' . $conn->error);
    }

    $students = [];
    while ($row = $resultStudents->fetch_assoc()) {
        $students[] = $row;
    }

    // ==========================================
    // Obtener la unión de todas las fechas de clase
    // ==========================================
    $sqlDates = "SELECT DISTINCT class_date
                 FROM attendance_records
                 ORDER BY class_date ASC";

    $resultDates = $conn->query($sqlDates);
    if (!$resultDates) {
        throw new Exception('Error al consultar fechas: ' . $conn->error);
    }

    $classDates = [];
    while ($row = $resultDates->fetch_assoc()) {
        $classDates[] = $row['class_date'];
    }

    // ==========================================
    // Cargar todos los registros de asistencia en memoria
    // y acumular conteos por estudiante/grado
    // ==========================================
    $attendanceMap = [];   // [studentId][gradeLevel][classDate] => status
    $attendanceStats = []; // [studentId][gradeLevel] => ['att' => n, 'abs' => n]

    $sqlAttendance = "SELECT student_id, grade_level, class_date, attendance_status
                      FROM attendance_records";

    $resultAttendance = $conn->query($sqlAttendance);
    if (!$resultAttendance) {
        throw new Exception('Error al consultar asistencia: ' . $conn->error);
    }

    while ($row = $resultAttendance->fetch_assoc()) {
        $studentId = $row['student_id'];
        $gradeLevel = $row['grade_level'];
        $classDate = $row['class_date'];
        $status = $row['attendance_status'];

        $attendanceMap[$studentId][$gradeLevel][$classDate] = $status;

        if (!isset($attendanceStats[$studentId][$gradeLevel])) {
            $attendanceStats[$studentId][$gradeLevel] = ['att' => 0, 'abs' => 0];
        }

        if ($status === 'presente' || $status === 'tarde') {
            $attendanceStats[$studentId][$gradeLevel]['att']++;
        } elseif ($status === 'ausente') {
            $attendanceStats[$studentId][$gradeLevel]['abs']++;
        }
    }

    // ==========================================
    // Construir la hoja de cálculo
    // ==========================================
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Asistencia General');

    if (empty($students)) {
        $sheet->setCellValue('A1', 'No hay estudiantes registrados en el sistema');
        $sheet->getStyle('A1')->getFont()->setBold(true);
    } else {
        // ==========================================
        // Construir encabezados
        // ==========================================
        $headers = [
            'Grado',
            'Tipo ID',
            'Documento',
            'Nombre',
            'Correo',
            'Teléfono',
            'Estado'
        ];

        // Agregar una columna por cada fecha de clase
        foreach ($classDates as $classDate) {
            $dateObj = DateTime::createFromFormat('Y-m-d', $classDate);
            $formattedDate = $dateObj ? $dateObj->format('d/m/Y') : $classDate;
            $headers[] = $formattedDate;
        }

        // Columnas de porcentajes al final
        $headers[] = '% Asistencia';
        $headers[] = '% Inasistencia';

        $basicCols = 7; // Grado + 6 columnas básicas
        $totalHeaders = count($headers);
        $firstDateCol = $basicCols + 1;
        $lastDateCol = $totalHeaders - 2;
        $firstPctCol = $totalHeaders - 1;

        // ==========================================
        // Escribir encabezados en fila 1
        // ==========================================
        $sheet->fromArray([$headers], null, 'A1', true);

        // Color de fondo de encabezados por bloques
        $headerRanges = [
            ['A1:' . Coordinate::stringFromColumnIndex($basicCols) . '1', 'E2E8F0'],
            [Coordinate::stringFromColumnIndex($firstPctCol) . '1:' . Coordinate::stringFromColumnIndex($totalHeaders) . '1', 'D1FAE5'],
        ];
        if (!empty($classDates)) {
            $headerRanges[] = [
                Coordinate::stringFromColumnIndex($firstDateCol) . '1:' . Coordinate::stringFromColumnIndex($lastDateCol) . '1',
                'DBEAFE',
            ];
        }
        foreach ($headerRanges as $headerRange) {
            $sheet->getStyle($headerRange[0])->getFont()->setBold(true);
            $sheet->getStyle($headerRange[0])->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB($headerRange[1]);
        }

        // ==========================================
        // Escribir datos de estudiantes
        // ==========================================
        $row = 2;
        foreach ($students as $student) {
            $studentId = $student['document_number'] ?? '';
            $studentGrade = $student['grade_level'] ?? '';

            // Datos básicos del estudiante
            $rowData = [
                $studentGrade,
                $student['document_type'] ?? 'N/A',
                $studentId,
                $student['name'] ?? 'N/A',
                $student['email'] ?? 'N/A',
                $student['cell_phone'] ?? 'N/A',
                $student['status'] ?? 'N/A',
            ];

            // Datos de asistencia por fecha (acumulando rellenos por color)
            $rowFills = [];
            $col = $firstDateCol;
            foreach ($classDates as $classDate) {
                $attendanceStatus = $attendanceMap[$studentId][$studentGrade][$classDate] ?? null;
                $rowData[] = getAttendanceCode($attendanceStatus);

                if ($attendanceStatus !== null) {
                    $bgColor = '';
                    switch ($attendanceStatus) {
                        case 'presente':
                            $bgColor = 'D1FAE5'; // Verde claro
                            break;
                        case 'ausente':
                            $bgColor = 'FEE2E2'; // Rojo claro
                            break;
                        case 'tarde':
                            $bgColor = 'FEF3C7'; // Amarillo claro
                            break;
                    }
                    if ($bgColor) {
                        $rowFills[$bgColor][] = $col;
                    }
                }

                $col++;
            }

            // Calcular porcentajes
            $stats = $attendanceStats[$studentId][$studentGrade] ?? ['att' => 0, 'abs' => 0];
            $totalRecords = $stats['att'] + $stats['abs'];

            if ($totalRecords > 0) {
                $attendancePercentage = round(($stats['att'] / $totalRecords) * 100, 1);
                $absencePercentage = round(($stats['abs'] / $totalRecords) * 100, 1);
            } else {
                $attendancePercentage = 0;
                $absencePercentage = 0;
            }

            $rowData[] = $attendancePercentage . '%';
            $rowData[] = $absencePercentage . '%';

            // Escribir fila completa de una sola vez
            $sheet->fromArray($rowData, null, 'A' . $row, true);

            // Aplicar rellenos agrupados por tramos contiguos
            applyRowFills($sheet, $row, $rowFills);

            $row++;
        }

        // ==========================================
        // Estilos generales
        // ==========================================
        $highestRow = $sheet->getHighestRow();
        $highestCol = $sheet->getHighestColumn();
        $highestColIndex = Coordinate::columnIndexFromString($highestCol);

        // Ajustar ancho de columnas automáticamente para las primeras 7
        for ($i = 1; $i <= $basicCols; $i++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
        }

        // Ancho fijo para columnas de fechas
        for ($i = $firstDateCol; $i <= $lastDateCol; $i++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setWidth(12);
        }

        // Ancho para las columnas de porcentajes
        $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($highestColIndex - 1))->setWidth(14);
        $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($highestColIndex))->setWidth(14);

        // Aplicar bordes a toda la tabla
        $sheet->getStyle('A1:' . $highestCol . $highestRow)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        // Alineación central en encabezados + wrap text
        $sheet->getStyle('A1:' . $highestCol . '1')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);

        // Alineación vertical centrada para datos
        $sheet->getStyle('A2:' . $highestCol . $highestRow)->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER);

        // Alineación horizontal centrada para columnas de asistencia y porcentajes
        $sheet->getStyle(
            Coordinate::stringFromColumnIndex($firstDateCol) . '2:' . $highestCol . $highestRow
        )->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Fijar fila de encabezados
        $sheet->freezePane('A2');
    }

    // ==========================================
    // Hoja de leyenda
    // ==========================================
    $legendSheet = $spreadsheet->createSheet();
    $legendSheet->setTitle('Leyenda');

    // Título
    $legendSheet->setCellValue('A1', 'LEYENDA DE CÓDIGOS DE ASISTENCIA');
    $legendSheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

    // Encabezados de leyenda
    $legendSheet->setCellValue('A3', 'Código');
    $legendSheet->setCellValue('B3', 'Significado');
    $legendSheet->setCellValue('C3', 'Color');
    $legendSheet->getStyle('A3:C3')->getFont()->setBold(true);
    $legendSheet->getStyle('A3:C3')->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setRGB('E2E8F0');

    // Presente
    $legendSheet->setCellValue('A4', '1');
    $legendSheet->setCellValue('B4', 'Presente');
    $legendSheet->setCellValue('C4', '');
    $legendSheet->getStyle('C4')->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setRGB('D1FAE5');

    // Ausente
    $legendSheet->setCellValue('A5', '0');
    $legendSheet->setCellValue('B5', 'Ausente');
    $legendSheet->setCellValue('C5', '');
    $legendSheet->getStyle('C5')->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setRGB('FEE2E2');

    // Tarde
    $legendSheet->setCellValue('A6', '2');
    $legendSheet->setCellValue('B6', 'Llegada tardía');
    $legendSheet->setCellValue('C6', '');
    $legendSheet->getStyle('C6')->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setRGB('FEF3C7');

    // Sin registro
    $legendSheet->setCellValue('A7', '-');
    $legendSheet->setCellValue('B7', 'Sin registro');
    $legendSheet->setCellValue('C7', '');

    // Bordes a la leyenda
    $legendSheet->getStyle('A3:C7')->getBorders()->getAllBorders()
        ->setBorderStyle(Border::BORDER_THIN);

    // Ajustar ancho de columnas de la leyenda
    $legendSheet->getColumnDimension('A')->setWidth(10);
    $legendSheet->getColumnDimension('B')->setWidth(20);
    $legendSheet->getColumnDimension('C')->setWidth(10);

    // Centrar contenido de la leyenda
    $legendSheet->getStyle('A3:C7')->getAlignment()
        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
        ->setVertical(Alignment::VERTICAL_CENTER);

    // ==========================================
    // Generar y descargar archivo
    // ==========================================
    $filename = 'Informe_Asistencia_General_' . date('Y-m-d') . '.xlsx';

    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');

} catch (Throwable $e) {
    http_response_code(500);
    echo 'Error al generar el archivo: ' . $e->getMessage();
}
