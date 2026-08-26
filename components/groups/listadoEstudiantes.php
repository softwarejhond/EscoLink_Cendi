<?php
require_once __DIR__ . '/../../controller/conexion.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Obtener los grados disponibles y ordenarlos
$queryGrades = "SELECT DISTINCT grade_level FROM el_students WHERE grade_level IS NOT NULL AND grade_level <> '' ORDER BY grade_level ASC";
$resultGrades = $conn->query($queryGrades);
$grades = [];
if ($resultGrades && $resultGrades->num_rows > 0) {
    while ($row = $resultGrades->fetch_assoc()) {
        $grades[] = $row['grade_level'];
    }
}

usort($grades, function ($a, $b) {
    if (preg_match('/^(\d+)[\s\-]?([A-Za-z]?)$/', $a, $ma)) {
        $numA = (int)$ma[1];
        $letA = strtoupper($ma[2]);
    } else {
        $numA = 999;
        $letA = $a;
    }
    if (preg_match('/^(\d+)[\s\-]?([A-Za-z]?)$/', $b, $mb)) {
        $numB = (int)$mb[1];
        $letB = strtoupper($mb[2]);
    } else {
        $numB = 999;
        $letB = $b;
    }
    return $numA !== $numB ? $numA - $numB : strcmp($letA, $letB);
});
?>

<!-- SweetAlert2 -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.0/dist/sweetalert2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.0/dist/sweetalert2.min.js"></script>

<!-- Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<style>
    #studentsTableWrapper {
        display: none;
    }

    #studentsTable th,
    #studentsTable td {
        vertical-align: middle;
        white-space: nowrap;
    }

    .swal-student-form label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 2px;
    }

    .swal2-popup.swal-wide {
        width: 900px !important;
        max-width: 95vw !important;
    }

    .swal2-popup.swal-wide .swal2-html-container {
        overflow-x: hidden;
    }

    .swal-student-form {
        overflow-x: hidden;
    }

    .badge-activo {
        background-color: #198754;
    }

    .badge-retirado {
        background-color: #dc3545;
    }

    .badge-graduado {
        background-color: #0d6efd;
    }

    .badge-pendiente-retiro {
        background-color: #ffc107;
    }

    #totalEstudiantes {
        opacity: 1 !important;
        pointer-events: none;
        white-space: nowrap;
    }

    .toolbar-btn {
        font-size: 0.875rem;
        font-weight: 600;
        letter-spacing: 0.3px;
        white-space: nowrap;
    }

    #groupGradeLevel + .select2-container .select2-selection--single {
        height: 35px !important;
        line-height: 35px !important;
    }

    #groupGradeLevel + .select2-container .select2-selection__arrow {
        height: 35px !important;
    }

    #groupGradeLevel + .select2-container .select2-selection__rendered {
        line-height: 35px !important;
        padding-top: 0 !important;
    }

    #teacherModal .modal-body,
    #teachersExtraModal .modal-body {
        max-height: 65vh;
        overflow-y: auto;
    }

    .current-assignment-card {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 12px 16px;
        margin-bottom: 16px;
    }

    .current-assignment-card .label {
        font-size: 0.8rem;
        color: #6c757d;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }

    .current-assignment-card .value {
        font-size: 1rem;
        font-weight: 600;
        color: #212529;
    }

    .teacher-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #e9ecef;
        border: 1px solid #ced4da;
        border-radius: 50px;
        padding: 4px 12px 4px 14px;
        font-size: 0.9rem;
        font-weight: 500;
    }

    .teacher-badge .btn-remove-teacher {
        width: 20px;
        height: 20px;
        padding: 0;
        border: none;
        background: #dc3545;
        color: #fff;
        border-radius: 50%;
        font-size: 0.7rem;
        line-height: 20px;
        text-align: center;
        cursor: pointer;
        transition: background .15s;
    }

    .teacher-badge .btn-remove-teacher:hover {
        background: #a71d2a;
    }
</style>

<div class="container-fluid mt-4">

    <!-- Tarjeta de filtro -->
    <div class="card shadow-sm mb-3">
        <div class="card-body py-2">
            <div class="d-flex align-items-center gap-2 w-100">
                <div style="flex:2 1 0; min-width:160px;">
                    <select id="groupGradeLevel" class="form-select">
                        <option value="">Seleccione un grupo</option>
                        <?php foreach ($grades as $grade): ?>
                            <option value="<?= htmlspecialchars($grade) ?>">
                                <?= htmlspecialchars($grade) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button id="totalEstudiantes" class="btn bg-orange-dark text-white disabled flex-fill" disabled style="white-space:nowrap;">
                    <i class="bi bi-people-fill me-1"></i>Total: 0
                </button>
                <button id="btnAsignarMaestro" class="btn toolbar-btn bg-teal-dark text-white flex-fill" disabled>
                    <i class="bi bi-person-check-fill me-1"></i> Asignación de Permanencia
                </button>
                <button id="btnNuevoEstudiante" class="btn toolbar-btn bg-indigo-dark text-white flex-fill" disabled>
                    <i class="bi bi-person-plus-fill me-1"></i> Nuevo Estudiante
                </button>
                <button id="btnAsignarDocentes" class="btn toolbar-btn bg-magenta-dark text-white flex-fill" disabled>
                    <i class="bi bi-people-fill me-1"></i> Extras de Permanencia
                </button>
            </div>
        </div>
    </div>

    <!-- Placeholder -->
    <div id="studentsPlaceholder" class="text-center py-5 text-muted">
        <i class="bi bi-people fs-1 d-block mb-2 opacity-50"></i>
        Seleccione un grupo para ver el listado de estudiantes.
    </div>

    <!-- Tabla -->
    <div id="studentsTableWrapper">
        <div>
            <table id="studentsTable" class="table table-hover table-bordered w-100">
                <thead class="text-center">
                    <tr>
                        <th>#</th>
                        <th>Tipo Doc.</th>
                        <th>SIMAT</th>
                        <th>Nro. Documento</th>
                        <th>Código</th>
                        <th>Nombre Completo</th>
                        <th>Género</th>
                        <th>Grado</th>
                        <th>Email</th>
                        <th>Celular</th>
                        <th>Celular 2</th>
                        <th>Dirección</th>
                        <th>Barrio</th>
                        <th>Comuna</th>
                        <th>Ciudad</th>
                        <th>Sede</th>
                        <th>Estado</th>
                        <th>F. Registro</th>
                        <th>Editar</th>
                        <th>Eliminar</th>
                    </tr>
                </thead>
                <tbody id="studentsTableBody">
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal Director de Grupo (un solo maestro) -->
<div class="modal fade" id="teacherModal" tabindex="-1" aria-labelledby="teacherModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="teacherModalLabel">Asignación de Permanencia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="currentDirectorInfo" class="current-assignment-card" style="display:none;">
                    <div class="label">Permanencia actual</div>
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="value" id="currentDirectorName"></span>
                        <button class="btn btn-sm btn-outline-danger" id="btnRemoveDirector">
                            <i class="bi bi-x-lg"></i> Quitar
                        </button>
                    </div>
                </div>
                <div id="noDirectorInfo" class="current-assignment-card" style="display:none;">
                    <div class="label">Permanencia actual</div>
                    <div class="value text-muted">Sin permanencia asignada</div>
                </div>
                <div id="teacherModalLoading" class="text-center py-4">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2 text-muted">Cargando usuarios...</p>
                </div>
                <div id="teacherModalContent" style="display:none;">
                    <p class="text-muted mb-2">Cambiar permanencia del grupo <strong id="teacherModalGroupName"></strong>:</p>
                    <select id="teacherSelect" class="form-select" style="width:100%;">
                        <option value="">-- Sin asignar --</option>
                    </select>
                    <p class="text-muted mt-2 small">Puede buscar por nombre o n&uacute;mero de documento.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn bg-teal-dark text-white" id="btnSaveTeacherAssignment">
                    <i class="bi bi-save me-1"></i> Guardar Asignación
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Asignar Docentes (varios maestros) -->
<div class="modal fade" id="teachersExtraModal" tabindex="-1" aria-labelledby="teachersExtraModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="teachersExtraModalLabel">Docentes de Permanencia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="currentTeachersInfo" class="current-assignment-card" style="display:none;">
                    <div class="label">Docentes asignados actualmente</div>
                    <div id="currentTeachersList" class="mt-2 d-flex flex-wrap gap-2"></div>
                </div>
                <div id="noTeachersExtraInfo" class="current-assignment-card" style="display:none;">
                    <div class="label">Docentes asignados</div>
                    <div class="value text-muted">No hay docentes adicionales asignados</div>
                </div>
                <div id="teachersExtraModalLoading" class="text-center py-4">
                    <div class="spinner-border text-warning" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2 text-muted">Cargando docentes...</p>
                </div>
                <div id="teachersExtraModalContent" style="display:none;">
                    <p class="text-muted mb-2">A&ntilde;adir docentes de permanencia al grupo <strong id="teachersExtraModalGroupName"></strong>:</p>
                    <select id="teachersExtraSelect" class="form-select" multiple="multiple" style="width:100%;">
                    </select>
                    <p class="text-muted mt-2 small">Busque por nombre o n&uacute;mero de documento y seleccione para a&ntilde;adir.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn bg-magenta-dark text-white" id="btnSaveTeachersExtraAssignment">
                    <i class="bi bi-save me-1"></i> Guardar Asignación
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        'use strict';

        // Grados disponibles para el formulario (generados desde PHP)
        const availableGrades = <?= json_encode($grades) ?>;

        let studentsDataTable = null;
        let currentGradeLevel = '';

        // ── Generador de opciones de grado para el formulario ──
        function buildGradeOptions(selectedGrade) {
            let opts = availableGrades.map(function(g) {
                const sel = g === selectedGrade ? ' selected' : '';
                return `<option value="${escHtml(g)}"${sel}>${escHtml(g)}</option>`;
            }).join('');
            // Si el grado seleccionado no está en la lista (grado nuevo), agregar opción
            if (selectedGrade && !availableGrades.includes(selectedGrade)) {
                opts = `<option value="${escHtml(selectedGrade)}" selected>${escHtml(selectedGrade)}</option>` + opts;
            }
            return opts;
        }

        function escHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        // ── Carga de estudiantes por grupo ──
        function loadStudents(gradeLevel) {
            Swal.fire({
                title: 'Cargando...',
                allowOutsideClick: false,
                didOpen: function() {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: 'components/groups/getStudentsByGroup.php',
                type: 'POST',
                data: {
                    grade_level: gradeLevel
                },
                dataType: 'json',
                success: function(response) {
                    Swal.close();
                    renderStudentsTable(response.students || []);
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo cargar el listado de estudiantes.'
                    });
                }
            });
        }

        // ── Renderizado de la tabla ──
        function renderStudentsTable(students) {
            if (studentsDataTable) {
                studentsDataTable.destroy();
                studentsDataTable = null;
            }

            const tbody = document.getElementById('studentsTableBody');
            if (!students.length) {
                tbody.innerHTML = '<tr><td colspan="20" class="text-center text-muted py-4">No hay estudiantes registrados en este grupo.</td></tr>';
                document.getElementById('studentsPlaceholder').style.display = 'none';
                document.getElementById('studentsTableWrapper').style.display = 'block';
                document.getElementById('totalEstudiantes').innerHTML = '<i class="bi bi-people-fill me-1"></i>Total: 0';
                return;
            }

            const statusBadge = {
                ACTIVO: 'badge-activo',
                RETIRADO: 'badge-retirado',
                GRADUADO: 'badge-graduado',
                'PENDIENTE RETIRO': 'badge-pendiente-retiro'
            };

            const rows = students.map(function(s, idx) {
                const badge = statusBadge[s.status] || 'bg-secondary';
                const regDate = s.registration_date ? s.registration_date.split('-').reverse().join('/') : '-';
                const dataJson = escHtml(JSON.stringify(s));

                return `<tr>
                <td class="text-center">${idx + 1}</td>
                <td class="text-center">${escHtml(s.document_type)}</td>
                <td>${escHtml(s.simat)}</td>
                <td>${escHtml(s.document_number)}</td>
                <td>${escHtml(s.student_code)}</td>
                <td>${escHtml(s.name)}</td>
                <td class="text-center">${escHtml(s.gender)}</td>
                <td class="text-center">${escHtml(s.grade_level)}</td>
                <td>${escHtml(s.email)}</td>
                <td>${escHtml(s.cell_phone)}</td>
                <td>${escHtml(s.cell_phone2)}</td>
                <td>${escHtml(s.address)}</td>
                <td>${escHtml(s.barrio)}</td>
                <td>${escHtml(s.comuna)}</td>
                <td>${escHtml(s.city)}</td>
                <td>${escHtml(s.sede)}</td>
                <td class="text-center"><span class="badge ${badge} px-2 py-1">${escHtml(s.status)}</span></td>
                <td class="text-center">${regDate}</td>
                <td class="text-center">
                    <button class="btn btn-sm bg-indigo-dark text-white btn-edit-student" data-student='${dataJson}' title="Editar">
                        <i class="bi bi-pencil-fill"></i>
                    </button>
                </td>
                <td class="text-center">
                    <button class="btn btn-sm btn-outline-danger btn-delete-student" data-id="${s.id}" data-name="${escHtml(s.name)}" title="Eliminar">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </td>
            </tr>`;
            }).join('');

            tbody.innerHTML = rows;

            document.getElementById('studentsPlaceholder').style.display = 'none';
            document.getElementById('studentsTableWrapper').style.display = 'block';
            document.getElementById('totalEstudiantes').innerHTML = '<i class="bi bi-people-fill me-1"></i>Total: ' + students.length;

            studentsDataTable = $('#studentsTable').DataTable({
                responsive: false,
                scrollX: true,
                autoWidth: false,
                paging: false,
                lengthChange: false,
                language: {
                    url: 'controller/datatable_esp.json'
                },
                order: [
                    [5, 'asc']
                ],
                columnDefs: [{
                    orderable: false,
                    targets: [18, 19]
                }],
                initComplete: function() {
                    this.api().columns.adjust();
                }
            });
            setTimeout(function() {
                if (studentsDataTable) studentsDataTable.columns.adjust();
            }, 150);
        }

        // ── Formulario de estudiante (add / edit) ──
        function buildStudentForm(student) {
            student = student || {};
            const gradeToSelect = student.grade_level || currentGradeLevel;

            return `
        <div class="swal-student-form text-start">
            <div class="row g-2">
                <div class="col-md-4">
                    <label>Tipo Documento <span class="text-danger">*</span></label>
                    <select class="form-select form-select-sm" id="sf-doc-type">
                        ${['TI','CC','CE','RC','PAS'].map(t =>
                            `<option value="${t}"${student.document_type === t ? ' selected' : ''}>${t}</option>`
                        ).join('')}
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Nro. Documento <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-sm" id="sf-doc-number"
                           value="${escHtml(student.document_number)}" placeholder="Solo números">
                </div>
                <div class="col-md-4">
                    <label>Código Estudiante</label>
                    <input type="text" class="form-control form-control-sm" id="sf-student-code"
                           value="${escHtml(student.student_code)}" placeholder="Carné / Matrícula">
                </div>
                <div class="col-md-3">
                    <label>SIMAT</label>
                    <input type="text" class="form-control form-control-sm" id="sf-simat"
                           value="${escHtml(student.simat)}">
                </div>
                <div class="col-md-9">
                    <label>Nombre Completo <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-sm" id="sf-name"
                           value="${escHtml(student.name)}" placeholder="Apellidos y Nombres">
                </div>
                <div class="col-md-3">
                    <label>Grado <span class="text-danger">*</span></label>
                    <select class="form-select form-select-sm" id="sf-grade">
                        ${buildGradeOptions(gradeToSelect)}
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Género <span class="text-danger">*</span></label>
                    <select class="form-select form-select-sm" id="sf-gender">
                        <option value="M"${student.gender === 'M' ? ' selected' : ''}>Masculino (M)</option>
                        <option value="F"${student.gender === 'F' ? ' selected' : ''}>Femenino (F)</option>
                        <option value="OTRO"${student.gender === 'OTRO' ? ' selected' : ''}>Otro</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Estado <span class="text-danger">*</span></label>
                    <select class="form-select form-select-sm" id="sf-status">
                        <option value="ACTIVO"${(student.status||'ACTIVO') === 'ACTIVO' ? ' selected' : ''}>ACTIVO</option>
                        <option value="RETIRADO"${student.status === 'RETIRADO' ? ' selected' : ''}>RETIRADO</option>
                        <option value="PENDIENTE RETIRO"${student.status === 'PENDIENTE RETIRO' ? ' selected' : ''}>PENDIENTE RETIRO</option>
                        <option value="GRADUADO"${student.status === 'GRADUADO' ? ' selected' : ''}>GRADUADO</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>F. Registro</label>
                    <input type="date" class="form-control form-control-sm" id="sf-reg-date"
                           value="${escHtml(student.registration_date)}">
                </div>
                <div class="col-md-6">
                    <label>Email</label>
                    <input type="email" class="form-control form-control-sm" id="sf-email"
                           value="${escHtml(student.email)}">
                </div>
                <div class="col-md-3">
                    <label>Celular</label>
                    <input type="text" class="form-control form-control-sm" id="sf-cell"
                           value="${escHtml(student.cell_phone)}" placeholder="10 dígitos">
                </div>
                <div class="col-md-3">
                    <label>Celular 2</label>
                    <input type="text" class="form-control form-control-sm" id="sf-cell2"
                           value="${escHtml(student.cell_phone2)}" placeholder="10 dígitos">
                </div>
                <div class="col-md-6">
                    <label>Dirección</label>
                    <input type="text" class="form-control form-control-sm" id="sf-address"
                           value="${escHtml(student.address)}">
                </div>
                <div class="col-md-3">
                    <label>Barrio</label>
                    <input type="text" class="form-control form-control-sm" id="sf-barrio"
                           value="${escHtml(student.barrio)}">
                </div>
                <div class="col-md-3">
                    <label>Comuna</label>
                    <input type="text" class="form-control form-control-sm" id="sf-comuna"
                           value="${escHtml(student.comuna)}">
                </div>
                <div class="col-md-4">
                    <label>Ciudad</label>
                    <input type="text" class="form-control form-control-sm" id="sf-city"
                           value="${escHtml(student.city)}">
                </div>
                <div class="col-md-4">
                    <label>Sede</label>
                    <input type="text" class="form-control form-control-sm" id="sf-sede"
                           value="${escHtml(student.sede)}">
                </div>
            </div>
        </div>`;
        }

        // ── Guardar estudiante (AJAX) ──
        function saveStudent(id, formData) {
            return $.ajax({
                url: 'components/groups/saveStudent.php',
                type: 'POST',
                data: formData,
                dataType: 'json'
            });
        }

        // ── Abrir formulario (nuevo o editar) ──
        function openStudentForm(student) {
            const isEdit = !!(student && student.id);
            const title = isEdit ? 'Editar Estudiante' : 'Registrar Nuevo Estudiante';
            const confirmBtnText = isEdit ? '<i class="bi bi-save me-1"></i> Actualizar' : '<i class="bi bi-person-plus me-1"></i> Registrar';

            Swal.fire({
                title: title,
                html: buildStudentForm(student),
                customClass: {
                    popup: 'swal-wide'
                },
                showCancelButton: true,
                cancelButtonText: 'Cancelar',
                confirmButtonText: confirmBtnText,
                confirmButtonColor: '#30336b',
                focusConfirm: false,
                preConfirm: function() {
                    const docType = document.getElementById('sf-doc-type').value;
                    const docNumber = document.getElementById('sf-doc-number').value.trim();
                    const studCode = document.getElementById('sf-student-code').value.trim();
                    const simat = document.getElementById('sf-simat').value.trim();
                    const name = document.getElementById('sf-name').value.trim();
                    const grade = document.getElementById('sf-grade').value;
                    const gender = document.getElementById('sf-gender').value;
                    const status = document.getElementById('sf-status').value;
                    const regDate = document.getElementById('sf-reg-date').value;
                    const email = document.getElementById('sf-email').value.trim();
                    const cell = document.getElementById('sf-cell').value.trim();
                    const cell2 = document.getElementById('sf-cell2').value.trim();
                    const address = document.getElementById('sf-address').value.trim();
                    const barrio = document.getElementById('sf-barrio').value.trim();
                    const comuna = document.getElementById('sf-comuna').value.trim();
                    const city = document.getElementById('sf-city').value.trim();
                    const sede = document.getElementById('sf-sede').value.trim();

                    if (!docType || !docNumber || !name || !grade || !gender || !status) {
                        Swal.showValidationMessage('Complete los campos obligatorios (*)');
                        return false;
                    }
                    if (!/^\d+$/.test(docNumber)) {
                        Swal.showValidationMessage('El número de documento debe contener solo dígitos');
                        return false;
                    }

                    return {
                        id: isEdit ? student.id : '',
                        document_type: docType,
                        document_number: docNumber,
                        student_code: studCode,
                        simat: simat,
                        name: name,
                        grade_level: grade,
                        gender: gender,
                        status: status,
                        registration_date: regDate,
                        email: email,
                        cell_phone: cell,
                        cell_phone2: cell2,
                        address: address,
                        barrio: barrio,
                        comuna: comuna,
                        city: city,
                        sede: sede
                    };
                },
                showLoaderOnConfirm: true,
                allowOutsideClick: function() {
                    return !Swal.isLoading();
                }
            }).then(function(result) {
                if (!result.isConfirmed) return;

                saveStudent(student ? student.id : null, result.value)
                    .done(function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: response.message,
                                timer: 1800,
                                showConfirmButton: false
                            }).then(function() {
                                if (currentGradeLevel) loadStudents(currentGradeLevel);
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message
                            });
                        }
                    })
                    .fail(function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudo conectar con el servidor.'
                        });
                    });
            });
        }

        // ── Eliminar estudiante ──
        function deleteStudent(id, name) {
            Swal.fire({
                title: '¿Archivar estudiante?',
                html: `
                <p class="mb-3">Está a punto de archivar a <strong>${escHtml(name)}</strong>.<br>
                El registro <strong>no se borrará definitivamente</strong>, se moverá al archivo de estudiantes.</p>
                <label class="form-label fw-semibold d-block text-start" style="font-size:0.9rem;">Motivo del retiro <span class="text-muted fw-normal">(opcional)</span></label>
                <textarea id="swal-delete-reason" class="form-control" rows="3"
                    placeholder="Ej: Traslado de institución, retiro voluntario..."></textarea>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonText: 'Cancelar',
                confirmButtonText: '<i class="bi bi-archive me-1"></i> Sí, archivar',
                preConfirm: function() {
                    return document.getElementById('swal-delete-reason').value.trim();
                }
            }).then(function(result) {
                if (!result.isConfirmed) return;

                Swal.fire({
                    title: 'Archivando...',
                    allowOutsideClick: false,
                    didOpen: function() {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: 'components/groups/deleteStudent.php',
                    type: 'POST',
                    data: {
                        id: id,
                        delete_reason: result.value
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Archivado',
                                text: response.message,
                                timer: 1800,
                                showConfirmButton: false
                            }).then(function() {
                                if (currentGradeLevel) loadStudents(currentGradeLevel);
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudo archivar el estudiante.'
                        });
                    }
                });
            });
        }

        // ── Actualizar texto del boton segun director asignado ──
        function updateTeacherButton(gradeLevel) {
            if (!gradeLevel) {
                $('#btnAsignarMaestro').html('<i class="bi bi-person-check-fill me-1"></i> Asignación de Permanencia');
                return;
            }

            $.ajax({
                url: 'components/groups/getTeachers.php',
                type: 'POST',
                data: {
                    grade_level: gradeLevel
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.assigned_teacher) {
                        $('#btnAsignarMaestro').html('<i class="bi bi-person-check-fill me-1"></i> Permanencia: ' + escHtml(response.assigned_teacher.nombre));
                    } else {
                        $('#btnAsignarMaestro').html('<i class="bi bi-person-check-fill me-1"></i> Asignación de Permanencia');
                    }
                },
                error: function() {
                    $('#btnAsignarMaestro').html('<i class="bi bi-person-check-fill me-1"></i> Asignación de Permanencia');
                }
            });
        }

        // ── Cargar maestros en el Select2 del modal ──
        function loadTeachersForModal(gradeLevel) {
            $('#currentDirectorInfo').hide();
            $('#noDirectorInfo').hide();
            $('#teacherModalLoading').show();
            $('#teacherModalContent').hide();
            $('#teacherModalGroupName').text(gradeLevel);

            if ($('#teacherSelect').data('select2')) {
                $('#teacherSelect').select2('destroy');
            }

            $.ajax({
                url: 'components/groups/getTeachers.php',
                type: 'POST',
                data: {
                    grade_level: gradeLevel
                },
                dataType: 'json',
                success: function(response) {
                    $('#teacherModalLoading').hide();
                    if (!response.success) {
                        $('#teacherModalContent').html('<div class="alert alert-danger">' + response.message + '</div>').show();
                        return;
                    }

                    // Mostrar info del director actual
                    if (response.assigned_teacher) {
                        $('#currentDirectorName').text(escHtml(response.assigned_teacher.nombre) + ' (' + escHtml(response.assigned_teacher.username) + ')');
                        $('#currentDirectorInfo').show();
                        $('#noDirectorInfo').hide();
                    } else {
                        $('#currentDirectorInfo').hide();
                        $('#noDirectorInfo').show();
                    }

                    const $select = $('#teacherSelect');
                    $select.empty().append('<option value="">-- Sin asignar --</option>');

                    if (response.teachers && response.teachers.length) {
                        response.teachers.forEach(function(t) {
                            const selected = response.assigned_teacher && String(t.username) === String(response.assigned_teacher.username) ? ' selected' : '';
                            $select.append('<option value="' + t.username + '"' + selected + '>' + escHtml(t.nombre) + ' - ' + escHtml(t.username) + '</option>');
                        });
                    }

                    $select.select2({
                        placeholder: 'Buscar usuario por nombre o documento...',
                        allowClear: true,
                        width: '100%',
                        dropdownParent: $('#teacherModal'),
                        language: {
                            noResults: function() {
                                return 'No se encontraron usuarios.';
                            },
                            searching: function() {
                                return 'Buscando...';
                            }
                        }
                    });

                    $('#teacherModalContent').show();
                },
                error: function() {
                    $('#teacherModalLoading').hide();
                    $('#teacherModalContent').html('<div class="alert alert-danger">Error al cargar los maestros.</div>').show();
                }
            });
        }

        // ── Guardar asignacion del maestro ──
        function saveTeacherAssignment() {
            const gradeLevel = currentGradeLevel;
            if (!gradeLevel) return;

            const teacherUsername = $('#teacherSelect').val() || '';

            Swal.fire({
                title: 'Guardando...',
                allowOutsideClick: false,
                didOpen: function() {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: 'components/groups/saveGroupTeacher.php',
                type: 'POST',
                data: {
                    grade_level: gradeLevel,
                    teacher_username: teacherUsername
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: response.message,
                            timer: 1800,
                            showConfirmButton: false
                        });
                        $('#teacherModal').modal('hide');
                        updateTeacherButton(gradeLevel);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo guardar la asignación.'
                    });
                }
            });
        }

        // ── Renderizar badges de docentes asignados ──
        function renderTeacherBadges(assignedUsernames, teachers) {
            const $list = $('#currentTeachersList');
            $list.empty();

            if (!assignedUsernames || !assignedUsernames.length) {
                return;
            }

            assignedUsernames.forEach(function(u) {
                const teacher = teachers.find(function(t) {
                    return String(t.username) === String(u);
                });
                const name = teacher ? teacher.nombre : u;
                const badge = document.createElement('span');
                badge.className = 'teacher-badge';
                badge.innerHTML = escHtml(name) +
                    ' <button class="btn-remove-teacher" data-username="' + u + '" title="Quitar">&times;</button>';
                $list.append(badge);
            });

            // Evento para quitar docente del listado
            $list.on('click', '.btn-remove-teacher', function() {
                const username = String($(this).data('username'));
                const currentVal = $('#teachersExtraSelect').val() || [];
                const newVal = currentVal.filter(function(v) {
                    return String(v) !== username;
                });
                $('#teachersExtraSelect').val(newVal).trigger('change');
                $(this).closest('.teacher-badge').remove();
                if ($('#teachersExtraSelect').val().length === 0) {
                    $('#currentTeachersInfo').hide();
                    $('#noTeachersExtraInfo').show();
                }
            });
        }

        // ── Cargar docentes en el Select2 multiple del modal ──
        function loadTeachersExtraForModal(gradeLevel) {
            $('#currentTeachersInfo').hide();
            $('#noTeachersExtraInfo').hide();
            $('#teachersExtraModalLoading').show();
            $('#teachersExtraModalContent').hide();
            $('#teachersExtraModalGroupName').text(gradeLevel);

            if ($('#teachersExtraSelect').data('select2')) {
                $('#teachersExtraSelect').select2('destroy');
            }

            $.ajax({
                url: 'components/groups/getTeachersExtra.php',
                type: 'POST',
                data: {
                    grade_level: gradeLevel
                },
                dataType: 'json',
                success: function(response) {
                    $('#teachersExtraModalLoading').hide();
                    if (!response.success) {
                        $('#teachersExtraModalContent').html('<div class="alert alert-danger">' + response.message + '</div>').show();
                        return;
                    }

                    // Mostrar badges de docentes ya asignados
                    if (response.assigned_usernames && response.assigned_usernames.length) {
                        renderTeacherBadges(response.assigned_usernames, response.teachers);
                        $('#currentTeachersInfo').show();
                        $('#noTeachersExtraInfo').hide();
                    } else {
                        $('#currentTeachersInfo').hide();
                        $('#noTeachersExtraInfo').show();
                    }

                    const $select = $('#teachersExtraSelect');
                    $select.empty();

                    if (response.teachers && response.teachers.length) {
                        response.teachers.forEach(function(t) {
                            const selected = response.assigned_usernames && response.assigned_usernames.includes(String(t.username)) ? ' selected' : '';
                            $select.append('<option value="' + t.username + '"' + selected + '>' + escHtml(t.nombre) + ' - ' + escHtml(t.username) + '</option>');
                        });
                    }

                    $select.select2({
                        placeholder: 'Buscar docentes por nombre o documento...',
                        allowClear: true,
                        width: '100%',
                        dropdownParent: $('#teachersExtraModal'),
                        language: {
                            noResults: function() {
                                return 'No se encontraron docentes.';
                            },
                            searching: function() {
                                return 'Buscando...';
                            }
                        }
                    });

                    // Actualizar badges cuando cambia la seleccion
                    $select.on('change', function() {
                        const selected = $(this).val() || [];
                        const teachers = response.teachers || [];
                        renderTeacherBadges(selected, teachers);
                        if (selected.length) {
                            $('#currentTeachersInfo').show();
                            $('#noTeachersExtraInfo').hide();
                        } else {
                            $('#noTeachersExtraInfo').show();
                        }
                    });

                    $('#teachersExtraModalContent').show();
                },
                error: function() {
                    $('#teachersExtraModalLoading').hide();
                    $('#teachersExtraModalContent').html('<div class="alert alert-danger">Error al cargar los docentes.</div>').show();
                }
            });
        }

        // ── Guardar asignacion de docentes ──
        function saveTeachersExtraAssignment() {
            const gradeLevel = currentGradeLevel;
            if (!gradeLevel) return;

            const teacherUsernames = $('#teachersExtraSelect').val() || [];

            Swal.fire({
                title: 'Guardando...',
                allowOutsideClick: false,
                didOpen: function() {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: 'components/groups/saveGroupTeachersExtra.php',
                type: 'POST',
                data: {
                    grade_level: gradeLevel,
                    teacher_usernames: teacherUsernames
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: response.message,
                            timer: 1800,
                            showConfirmButton: false
                        });
                        $('#teachersExtraModal').modal('hide');
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo guardar la asignación.'
                    });
                }
            });
        }

        // ── Eventos ──
        $(document).ready(function() {

            // Inicializar Select2
            $('#groupGradeLevel').select2({
                placeholder: 'Seleccione un grupo',
                allowClear: true,
                width: '100%',
                language: {
                    noResults: function() {
                        return 'No se encontraron grupos.';
                    },
                    searching: function() {
                        return 'Buscando...';
                    }
                }
            });

            // Cambio de grupo
            $('#groupGradeLevel').on('change', function() {
                currentGradeLevel = $(this).val();
                $('#btnNuevoEstudiante').prop('disabled', !currentGradeLevel);
                $('#btnAsignarMaestro').prop('disabled', !currentGradeLevel);
                $('#btnAsignarDocentes').prop('disabled', !currentGradeLevel);

                if (!currentGradeLevel) {
                    if (studentsDataTable) {
                        studentsDataTable.destroy();
                        studentsDataTable = null;
                    }
                    $('#studentsTableBody').empty();
                    $('#studentsTableWrapper').hide();
                    $('#studentsPlaceholder').show();
                    $('#totalEstudiantes').html('<i class="bi bi-people-fill me-1"></i>Total: 0');
                    $('#btnAsignarMaestro').html('<i class="bi bi-person-check-fill me-1"></i> Asignación de Permanencia');
                    return;
                }
                loadStudents(currentGradeLevel);
                updateTeacherButton(currentGradeLevel);
            });

            // Nuevo estudiante
            $('#btnNuevoEstudiante').on('click', function() {
                openStudentForm(null);
            });

            // Asignación de Permanencia (un solo maestro)
            $('#btnAsignarMaestro').on('click', function() {
                if (!currentGradeLevel) return;
                loadTeachersForModal(currentGradeLevel);
                $('#teacherModal').modal('show');
            });

            // Guardar director
            $('#btnSaveTeacherAssignment').on('click', function() {
                saveTeacherAssignment();
            });

            // Quitar director (auto-save con vacio)
            $('#btnRemoveDirector').on('click', function() {
                Swal.fire({
                    title: 'Quitar permanencia',
                    text: 'Se eliminar\u00e1 la permanencia asignada a este grupo.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'S\u00ed, quitar',
                    cancelButtonText: 'Cancelar'
                }).then(function(result) {
                    if (!result.isConfirmed) return;
                    $('#teacherSelect').val('').trigger('change');
                    saveTeacherAssignment();
                });
            });

            // Limpiar Select2 del modal director al cerrarlo
            $('#teacherModal').on('hidden.bs.modal', function() {
                if ($('#teacherSelect').data('select2')) {
                    $('#teacherSelect').select2('destroy');
                }
            });

            // Asignar Docentes (varios maestros)
            $('#btnAsignarDocentes').on('click', function() {
                if (!currentGradeLevel) return;
                loadTeachersExtraForModal(currentGradeLevel);
                $('#teachersExtraModal').modal('show');
            });

            // Guardar docentes
            $('#btnSaveTeachersExtraAssignment').on('click', function() {
                saveTeachersExtraAssignment();
            });

            // Limpiar Select2 del modal docentes al cerrarlo
            $('#teachersExtraModal').on('hidden.bs.modal', function() {
                if ($('#teachersExtraSelect').data('select2')) {
                    $('#teachersExtraSelect').select2('destroy');
                }
            });

            // Editar estudiante (delegación de evento)
            $(document).on('click', '.btn-edit-student', function() {
                let student;
                try {
                    student = JSON.parse($(this).attr('data-student'));
                } catch (e) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo leer la información del estudiante.'
                    });
                    return;
                }
                openStudentForm(student);
            });

            // Eliminar estudiante (delegación de evento)
            $(document).on('click', '.btn-delete-student', function() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                deleteStudent(id, name);
            });
        });

    })();
</script>