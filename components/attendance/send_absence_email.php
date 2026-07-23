<?php

require_once __DIR__ . '/../../controller/conexion.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

function logAbsenceEmail($message) {
    $logFile = __DIR__ . '/absence_email.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
}

function sendAbsenceEmail($conn, $student_document_number, $class_date, $grade_level = '', $course_type = '') {
    $stmt = $conn->prepare(
        "SELECT name, email, nombre_acudiente, email_acudiente
         FROM el_students
         WHERE document_number = ?"
    );

    if (!$stmt) {
        logAbsenceEmail("ERROR - Falló prepare: " . $conn->error);
        return false;
    }

    $stmt->bind_param("s", $student_document_number);

    if (!$stmt->execute()) {
        logAbsenceEmail("ERROR - Falló execute para $student_document_number: " . $stmt->error);
        $stmt->close();
        return false;
    }

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if (!$row) {
        logAbsenceEmail("ERROR - Estudiante $student_document_number no encontrado en el_students");
        return false;
    }

    $student_name = $row['name'];
    $student_email = trim($row['email'] ?? '');
    $acudiente_name = trim($row['nombre_acudiente'] ?? '');
    $acudiente_email = trim($row['email_acudiente'] ?? '');

    if (!empty($acudiente_email)) {
        $target_email = $acudiente_email;
        $target_name = $acudiente_name ?: 'Acudiente';
        $is_acudiente = true;
    } elseif (!empty($student_email)) {
        $target_email = $student_email;
        $target_name = $student_name;
        $is_acudiente = false;
    } else {
        logAbsenceEmail("INFO - Sin correo para $student_document_number ($student_name), no se envía notificación");
        return false;
    }

    if (!filter_var($target_email, FILTER_VALIDATE_EMAIL)) {
        logAbsenceEmail("ERROR - Email inválido: $target_email para $student_document_number");
        return false;
    }

    $configQuery = "SELECT * FROM smtpconfig WHERE id = 1";
    $configResult = mysqli_query($conn, $configQuery);

    if (!$configResult || mysqli_num_rows($configResult) === 0) {
        logAbsenceEmail("ERROR - No se encontró configuración SMTP con id=1");
        return false;
    }

    $smtp = mysqli_fetch_assoc($configResult);
    $host = $smtp['host'];
    $port = (int)$smtp['port'];
    $smtpUser = $smtp['username'];
    $password = $smtp['password'];
    $fromEmail = !empty($smtpUser) && filter_var($smtpUser, FILTER_VALIDATE_EMAIL) ? $smtpUser : $smtp['email'];

    $fecha_formateada = date('d/m/Y', strtotime($class_date));
    $grupo_info = !empty($grade_level) ? $grade_level : $course_type;

    if ($is_acudiente) {
        $subject = "Notificación de inasistencia - $student_name - $fecha_formateada";
        $greeting = "Estimado/a <strong>$target_name</strong>";
        $body_intro = "Le informamos que el/la estudiante <strong>$student_name</strong> registró inasistencia en la jornada del <strong>$fecha_formateada</strong> en el grupo <strong>$grupo_info</strong>.";
        $body_action = "Para efectos de justificar esta falta, es indispensable presentar los soportes o justificantes válidos ante la institución dentro de los plazos establecidos. Dichos documentos serán evaluados para determinar la remoción de la inasistencia.";
    } else {
        $subject = "Registro de inasistencia - $fecha_formateada";
        $greeting = "Estimado/a <strong>$target_name</strong>";
        $body_intro = "Se ha registrado tu inasistencia en la jornada del <strong>$fecha_formateada</strong> en el grupo <strong>$grupo_info</strong>.";
        $body_action = "Si consideras que esta inasistencia debe ser justificada, te invitamos a presentar los soportes o justificantes válidos ante la institución dentro de los plazos establecidos. Dichos documentos serán evaluados para determinar la remoción de la falta.";
    }

    $emailBody = '
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body style="margin:0; padding:0; background-color:#f4f6f9; font-family: Arial, Helvetica, sans-serif;">
        <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f9; padding: 20px 0;">
            <tr>
                <td align="center">
                    <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius:12px; overflow:hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.08);">
                        <tr>
                            <td style="background: linear-gradient(135deg, #1a237e 0%, #283593 100%); padding: 32px 40px; text-align: center;">
                                <h1 style="color:#ffffff; font-size:22px; margin:0; font-weight:600; letter-spacing:0.5px;">
                                    ESCOLINK CENDI
                                </h1>
                                <p style="color: rgba(255,255,255,0.85); font-size:14px; margin:6px 0 0 0;">
                                    Sistema de Gestión Académica
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 36px 40px 20px 40px;">
                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="padding: 20px; background-color:#fff3e0; border-left: 4px solid #e65100; border-radius:4px;">
                                            <p style="color:#bf360c; font-size:15px; margin:0; font-weight:600;">
                                                NOTIFICACIÓN DE INASISTENCIA
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 0 40px 24px 40px;">
                                <p style="color:#333333; font-size:15px; line-height:1.6; margin:0 0 16px 0;">
                                    ' . $greeting . ',
                                </p>
                                <p style="color:#444444; font-size:15px; line-height:1.7; margin:0 0 20px 0;">
                                    ' . $body_intro . '
                                </p>
                                <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f8f9fa; border-radius:8px; margin-bottom:20px;">
                                    <tr>
                                        <td style="padding: 18px 22px;">
                                            <table width="100%" cellpadding="0" cellspacing="0">
                                                <tr>
                                                    <td style="padding:6px 0; color:#666; font-size:13px; width:140px;">Estudiante:</td>
                                                    <td style="padding:6px 0; color:#333; font-size:14px; font-weight:600;">' . htmlspecialchars($student_name) . '</td>
                                                </tr>
                                                <tr>
                                                    <td style="padding:6px 0; color:#666; font-size:13px;">Documento:</td>
                                                    <td style="padding:6px 0; color:#333; font-size:14px;">' . htmlspecialchars($student_document_number) . '</td>
                                                </tr>
                                                <tr>
                                                    <td style="padding:6px 0; color:#666; font-size:13px;">Fecha:</td>
                                                    <td style="padding:6px 0; color:#333; font-size:14px;">' . $fecha_formateada . '</td>
                                                </tr>
                                                <tr>
                                                    <td style="padding:6px 0; color:#666; font-size:13px;">Grupo:</td>
                                                    <td style="padding:6px 0; color:#333; font-size:14px;">' . htmlspecialchars($grupo_info) . '</td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                                <p style="color:#444444; font-size:14px; line-height:1.7; margin:0 0 24px 0;">
                                    ' . $body_action . '
                                </p>
                                <p style="color:#666666; font-size:13px; line-height:1.5; margin:0;">
                                    Este es un mensaje automático generado por el sistema ESCOLINK CENDI. Por favor, no responda a este correo.
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td style="background-color:#f4f6f9; padding: 20px 40px; text-align: center; border-top: 1px solid #e8ecf1;">
                                <p style="color:#888888; font-size:12px; margin:0;">
                                    ESCOLINK CENDI &copy; ' . date('Y') . ' &mdash; Todos los derechos reservados.
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
    </html>';

    try {
        $mail = new PHPMailer(true);
        $mail->SMTPDebug = SMTP::DEBUG_OFF;
        $mail->isSMTP();
        $mail->Host = $host;
        $mail->SMTPAuth = true;
        $mail->Username = $smtpUser;
        $mail->Password = $password;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = $port;

        $mail->Timeout = 30;
        $mail->CharSet = 'UTF-8';

        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ],
        ];

        $mail->setFrom($fromEmail, 'ESCOLINK CENDI');
        $mail->addAddress($target_email, $target_name);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $emailBody;
        $mail->AltBody = strip_tags(str_replace(['<br>', '</p>'], ["\n", "\n\n"], $emailBody));

        $mail->send();
        logAbsenceEmail("ENVIADO - A: $target_email ($target_name) | Estudiante: $student_document_number ($student_name) | Fecha: $fecha_formateada | Acudiente: " . ($is_acudiente ? 'SI' : 'NO'));
        return true;

    } catch (Exception $e) {
        logAbsenceEmail("ERROR SMTP - $target_email: " . $e->getMessage());
        return false;
    }
}
