<?php
// Incluir conexión
require_once __DIR__ . '/../../controller/conexion.php';

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<style>
    .table-responsive {
        overflow-x: auto;
        width: 100%;
    }

    .datatable {
        width: 100% !important;
    }

    .datatable th,
    .datatable td {
        white-space: nowrap;
        padding: 6px 10px;
        vertical-align: middle;
        text-align: center;
        font-size: 0.875rem;
    }

    .datatable td:nth-child(3),
    .datatable td:nth-child(4) {
        text-align: left;
        max-width: 200px;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .datatable th.class-col {
        min-width: 70px;
        font-size: 0.8rem;
    }

    .observation-modal .modal-dialog {
        max-width: 600px;
    }

    .btn-outline-primary {
        font-size: 0.875rem;
        padding: 0.25rem 0.5rem;
    }

    .tab-content {
        overflow-x: auto;
    }

    .badge {
        font-size: 0.75rem;
        padding: 0.35em 0.65em;
    }

    /* Colores personalizados */
    .bg-cyan-dark {
        background-color: #007a7a !important;
        color: white !important;
        border-color: #007a7a !important;
    }

    .text-indigo-dark {
        color: #30336b !important;
    }

    .bg-indigo-dark {
        background-color: #30336b !important;
    }

    .nav-tabs .nav-link {
        color: #000000;
        font-weight: normal;
        transition: color 0.3s, font-weight 0.3s;
    }

    .nav-tabs .nav-link.active {
        color: #30336b !important;
        font-weight: bold;
        border-bottom-color: #30336b;
        border-bottom-width: 2px;
    }

    .nav-tabs .nav-link:not(.active) {
        border-color: transparent;
    }

    .nav-tabs .nav-link:hover:not(.active) {
        border-color: transparent;
        color: #30336b;
    }

    .bg-orange-dark {
        background-color: #ff8c00 !important;
        color: white !important;
        border-color: #ff8c00 !important;
    }

    .bg-teal-dark {
        background-color: #008080 !important;
        color: white !important;
        border-color: #008080 !important;
    }

    .observation-btn:hover {
        transform: scale(1.05);
        transition: transform 0.2s ease;
    }

    .observation-btn {
        position: relative;
    }
    .select2-container .select2-selection--single {
        height: 35px !important;
    }
    .select2-container .select2-selection--single .select2-selection__rendered {
        line-height: 35px !important;
    }
    .select2-container .select2-selection--single .select2-selection__arrow {
        height: 33px !important;
    }
</style>

<div class="container-fluid mt-4">
    <div class="card shadow mb-3">
        <div class="card-body rounded-0">
            <div class="container-fluid">
                <div class="row align-items-end">
                    <!-- Selección de Grado -->
                    <div class="col-lg-6 col-md-6 col-sm-12 col-12">
                        <label class="form-label">Grado</label>
                        <select id="trackingGradeLevel" class="form-select">
                            <option value="">Seleccione un grado</option>
                            <?php
                            $rol = $_SESSION['rol'] ?? null;
                            $username = $_SESSION['username'] ?? null;
                            $grades = [];

                            if ($rol == 5) {
                                $query = "SELECT DISTINCT e.grade_level FROM el_students e
                                          WHERE e.grade_level IS NOT NULL AND e.grade_level <> ''
                                          AND (
                                            e.grade_level IN (SELECT grade_level FROM group_teachers WHERE teacher_username = ?)
                                            OR
                                            e.grade_level IN (SELECT grade_level FROM group_teachers_extra WHERE teacher_username = ?)
                                          )";
                                $stmt = $conn->prepare($query);
                                if ($stmt) {
                                    $stmt->bind_param("ii", $username, $username);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    while ($row = $result->fetch_assoc()) {
                                        $grades[] = $row['grade_level'];
                                    }
                                    $stmt->close();
                                }
                            } else {
                                $query = "SELECT DISTINCT grade_level FROM el_students WHERE grade_level IS NOT NULL AND grade_level <> ''";
                                $result = $conn->query($query);
                                if ($result && $result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        $grades[] = $row['grade_level'];
                                    }
                                }
                            }

                            usort($grades, function ($a, $b) {
                                if (preg_match('/^(\d+)[\s\-]?([A-Za-z])$/', $a, $ma)) {
                                    $numA = (int)$ma[1];
                                    $letA = strtoupper($ma[2]);
                                } else {
                                    $numA = 999;
                                    $letA = $a;
                                }
                                if (preg_match('/^(\d+)[\s\-]?([A-Za-z])$/', $b, $mb)) {
                                    $numB = (int)$mb[1];
                                    $letB = strtoupper($mb[2]);
                                } else {
                                    $numB = 999;
                                    $letB = $b;
                                }
                                if ($numA === $numB) {
                                    return strcmp($letA, $letB);
                                }
                                return $numA - $numB;
                            });

                            foreach ($grades as $grade) {
                            ?>
                                <option value="<?= htmlspecialchars($grade) ?>">
                                    <?= htmlspecialchars($grade) ?>
                                </option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Botones de exportación -->
                    <div class="col-lg-6 col-md-6 col-sm-12 col-12 d-flex align-items-center gap-2">
                        <button id="exportTrackingBtn" class="btn bg-teal-dark text-white" disabled>
                            <i class="bi bi-download me-2"></i>
                            Exportar seguimiento
                        </button>
                        <button id="exportAttendanceBtn" class="btn bg-lime-dark text-white" disabled>
                            <i class="bi bi-table me-2"></i>
                            Exportar asistencia
                        </button>
                        <button id="exportGroupBtn" class="btn bg-indigo-dark text-white" disabled>
                            <i class="bi bi-people me-2"></i>
                            Exportar grupo
                        </button>
                    </div>

                    <!-- Botones de exportación -->
                    <!-- <div class="col-lg-6 col-md-6 col-sm-12 col-12 d-flex align-items-end gap-2">
                        <button id="exportTrackingBtn" class="btn btn-success" disabled>
                            <i class="fas fa-file-excel me-2"></i>
                            Exportar seguimiento
                        </button>
                    </div> -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Nav Tabs principal: Panel Estadístico / Tabla de Seguimiento -->
<div class="container-fluid px-0 mt-3">
    <ul class="nav nav-tabs" id="mainTrackingTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="stats-tab" data-bs-toggle="tab"
                    data-bs-target="#stats-tab-pane" type="button" role="tab"
                    aria-controls="stats-tab-pane" aria-selected="true">
                <i class="bi bi-bar-chart-fill me-1"></i> Panel Estadístico
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tracking-tab" data-bs-toggle="tab"
                    data-bs-target="#tracking-tab-pane" type="button" role="tab"
                    aria-controls="tracking-tab-pane" aria-selected="false">
                <i class="bi bi-table me-1"></i> Tabla de Seguimiento
            </button>
        </li>
    </ul>

    <div class="tab-content border border-top-0 rounded-bottom bg-white shadow-sm p-3"
         id="mainTrackingTabsContent">

        <!-- ======================================================= -->
        <!-- Tab 1: Panel Estadístico                                 -->
        <!-- ======================================================= -->
        <div class="tab-pane fade show active" id="stats-tab-pane"
             role="tabpanel" aria-labelledby="stats-tab">

            <!-- Placeholder cuando no hay grado seleccionado -->
            <div id="statsPlaceholder" class="text-center py-5 text-muted">
                <i class="bi bi-bar-chart fs-1"></i>
                <p class="mt-2">Selecciona un grado para ver las estadísticas del grupo</p>
            </div>

            <!-- Panel de estadísticas (se muestra tras cargar) -->
            <div id="statsContainer" style="display: none;">

                <!-- KPI Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center border-start border-5 border-primary py-3">
                                <div class="small text-muted mb-1">Total Estudiantes</div>
                                <div class="fs-1 fw-bold text-primary" id="kpi-total-students">—</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center border-start border-5 border-success py-3">
                                <div class="small text-muted mb-1">% Asistencia promedio</div>
                                <div class="fs-1 fw-bold text-success" id="kpi-avg-attendance">—</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center border-start border-5 border-warning py-3">
                                <div class="small text-muted mb-1">Total Clases</div>
                                <div class="fs-1 fw-bold text-warning" id="kpi-total-classes">—</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center border-start border-5 border-danger py-3">
                                <div class="small text-muted mb-1">Intervenciones activas</div>
                                <div class="fs-1 fw-bold text-danger" id="kpi-interventions">—</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fila 1: Distribución (Doughnut) + Tendencia (Line) -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-transparent fw-semibold border-bottom">
                                <i class="bi bi-pie-chart-fill me-1 text-indigo-dark"></i>
                                Distribución de Asistencia
                            </div>
                            <div class="card-body d-flex align-items-center justify-content-center"
                                 style="min-height: 280px;">
                                <canvas id="chartAttendanceDistribution"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-transparent fw-semibold border-bottom">
                                <i class="bi bi-graph-up me-1 text-indigo-dark"></i>
                                Tendencia de Asistencia por Clase
                            </div>
                            <div class="card-body" style="min-height: 280px;">
                                <canvas id="chartAttendanceTrend"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fila 2: Top ausentes (Bar horiz.) + Estado intervenciones (Bar) -->
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-transparent fw-semibold border-bottom">
                                <i class="bi bi-person-x-fill me-1 text-danger"></i>
                                Top 10 con más Ausencias
                            </div>
                            <div class="card-body" style="min-height: 280px;">
                                <canvas id="chartTopAbsences"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-transparent fw-semibold border-bottom">
                                <i class="bi bi-clipboard2-check-fill me-1 text-success"></i>
                                Estado de Intervenciones
                            </div>
                            <div class="card-body d-flex align-items-center justify-content-center"
                                 style="min-height: 280px;">
                                <canvas id="chartInterventions"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- /statsContainer -->
        </div><!-- /stats-tab-pane -->

        <!-- ======================================================= -->
        <!-- Tab 2: Tabla de Seguimiento                              -->
        <!-- ======================================================= -->
        <div class="tab-pane fade" id="tracking-tab-pane"
             role="tabpanel" aria-labelledby="tracking-tab">

            <!-- Placeholder cuando no hay grado seleccionado -->
            <div id="trackingPlaceholder" class="text-center py-5 text-muted">
                <i class="bi bi-table fs-1"></i>
                <p class="mt-2">Selecciona un grado para ver la tabla de seguimiento</p>
            </div>

            <div id="studentsContainer" style="display: none;">
                <div class="table-responsive">
                    <!-- La tabla se genera dinámicamente -->
                </div>
            </div>
        </div><!-- /tracking-tab-pane -->

    </div><!-- /tab-content -->
</div><!-- /container-fluid Nav Tabs -->

<!-- Bootstrap 5.3.3 CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">

<!-- Incluir SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Incluir Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    // Instancias globales de Chart.js
    let chartDistribution = null;
    let chartTrend        = null;
    let chartTopAbsences  = null;
    let chartInterventions = null;

    $(document).ready(function() {
        // Select2 en selector de grado
        $('#trackingGradeLevel').select2({
            placeholder: 'Seleccione un grado',
            allowClear: true,
            width: '100%',
            language: {
                noResults: function () { return 'No se encontraron grados.'; },
                searching: function () { return 'Buscando...'; }
            }
        });

        // Variable global para almacenar los datos cargados
        let currentTrackingData = null;
        let currentGradeLevel = null;

        // Evento de cambio en el selector de grado
        $('#trackingGradeLevel').on('change', function() {
            const selectedGrade = $(this).val();

            // Deshabilitar botones de exportación
            $('#exportTrackingBtn').prop('disabled', true);
            currentTrackingData = null;
            currentGradeLevel = null;

            // Resetear panel estadístico
            $('#statsContainer').hide();
            $('#statsPlaceholder').show();
            destroyCharts();

            if (!selectedGrade) {
                $('#studentsContainer').hide();
                $('#trackingPlaceholder').show();
                return;
            }

            currentGradeLevel = selectedGrade;

            // Cargar estadísticas del grupo en paralelo con la tabla
            loadGroupStats(selectedGrade);

            // Mostrar carga
            Swal.fire({
                title: 'Cargando estudiantes',
                text: 'Por favor espere...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Cargar datos para todas las materias
            $.ajax({
                url: 'components/attendance/getTrackingData.php',
                method: 'POST',
                data: {
                    grade_level: selectedGrade
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        currentTrackingData = response;

                        // Destruir DataTables existentes
                        $('.datatable').each(function() {
                            if ($.fn.DataTable.isDataTable(this)) {
                                $(this).DataTable().destroy();
                            }
                        });

                        // Poblar la tabla única
                        populateTrackingTables(response.data, response.classes);

                        // Mostrar contenedor de seguimiento
                        $('#trackingPlaceholder').hide();
                        $('#studentsContainer').show();

                        // Habilitar botón de exportación si hay datos
                        let totalStudents = response.total_students || 0;
                        $('#exportTrackingBtn').prop('disabled', totalStudents === 0);
                        $('#exportAttendanceBtn').prop('disabled', totalStudents === 0);
                        $('#exportGroupBtn').prop('disabled', totalStudents === 0);

                        // Inicializar DataTable y cerrar loading
                        setTimeout(() => {
                            try {
                                initializeSingleDataTable(); // Intenta inicializar la tabla
                            } catch (e) {
                                console.error("Error inicializando DataTable:", e);
                            } finally {
                                Swal.close(); // Cierra el Swal SIEMPRE, incluso si hay un error

                                // Muestra el mensaje de "sin resultados" después de cerrar el loading
                                if (totalStudents === 0) {
                                    Swal.fire({
                                        icon: 'info',
                                        title: 'Sin resultados',
                                        text: 'No se encontraron estudiantes para este grado'
                                    });
                                }
                            }
                        }, 100); // Un pequeño delay para que el DOM se actualice

                    } else {
                        Swal.close(); // Cerrar loading inmediatamente en error
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error al cargar estudiantes: ' + response.message
                        });
                    }
                },
                error: function(xhr, status, error) {
                    Swal.close(); // Cerrar loading inmediatamente en error
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de conexión',
                        text: 'No se pudo conectar con el servidor'
                    });
                    console.error("Error AJAX:", status, error);
                }
            });
        });

        // Exportar seguimiento
        $('#exportTrackingBtn').on('click', function() {
            if (!currentTrackingData || !currentGradeLevel) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No hay datos para exportar'
                });
                return;
            }

            Swal.fire({
                title: 'Generando archivo Excel',
                text: 'Por favor espere...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: 'components/attendance/exportTrackingData.php',
                method: 'POST',
                data: {
                    grade_level: currentGradeLevel,
                    data: JSON.stringify(currentTrackingData.data),
                    classes: JSON.stringify(currentTrackingData.classes)
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(data) {
                    const blob = new Blob([data], {
                        type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                    });
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `Seguimiento_${currentGradeLevel}_${new Date().toISOString().split('T')[0]}.xlsx`;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);

                    Swal.close();
                    Swal.fire({
                        icon: 'success',
                        title: 'Exportación exitosa',
                        text: 'El archivo se ha descargado correctamente',
                        timer: 2000,
                        showConfirmButton: false
                    });
                },
                error: function() {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de exportación',
                        text: 'No se pudo generar el archivo Excel'
                    });
                }
            });
        });

        // Exportar asistencia simple
        $('#exportAttendanceBtn').on('click', function() {
            if (!currentTrackingData || !currentGradeLevel) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No hay datos para exportar'
                });
                return;
            }

            Swal.fire({
                title: 'Generando archivo Excel',
                text: 'Por favor espere...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: 'components/attendance/exportAttendanceSimpleTracking.php',
                method: 'POST',
                data: {
                    grade_level: currentGradeLevel,
                    data: JSON.stringify(currentTrackingData.data),
                    classes: JSON.stringify(currentTrackingData.classes)
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(data) {
                    const blob = new Blob([data], {
                        type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                    });
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `Asistencia_${currentGradeLevel}_${new Date().toISOString().split('T')[0]}.xlsx`;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);

                    Swal.close();
                    Swal.fire({
                        icon: 'success',
                        title: 'Exportación exitosa',
                        text: 'El archivo de asistencia se ha descargado correctamente',
                        timer: 2000,
                        showConfirmButton: false
                    });
                },
                error: function() {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de exportación',
                        text: 'No se pudo generar el archivo Excel de asistencia'
                    });
                }
            });
        });


        // Exportar grupo
        $('#exportGroupBtn').on('click', function() {
            if (!currentGradeLevel) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Debe seleccionar un grado'
                });
                return;
            }

            Swal.fire({
                title: 'Generando archivo Excel',
                text: 'Por favor espere...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: 'components/attendance/exportGroupData.php',
                method: 'POST',
                data: {
                    grade_level: currentGradeLevel
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(data) {
                    const blob = new Blob([data], {
                        type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                    });
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `Listado_${currentGradeLevel.replace(/[° ]/g, '_')}_${new Date().toISOString().split('T')[0]}.xlsx`;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);

                    Swal.close();
                    Swal.fire({
                        icon: 'success',
                        title: 'Exportación exitosa',
                        text: 'El listado del grupo se ha descargado correctamente',
                        timer: 2000,
                        showConfirmButton: false
                    });
                },
                error: function() {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de exportación',
                        text: 'No se pudo generar el archivo Excel del listado'
                    });
                }
            });
        });

        // Ajustar anchos de DataTable al mostrar el tab de seguimiento
        $('#mainTrackingTabs button[data-bs-target="#tracking-tab-pane"]').on('shown.bs.tab', function() {
            if ($.fn.DataTable.isDataTable('#tracking-table')) {
                $('#tracking-table').DataTable().columns.adjust().draw(false);
            }
        });

        // Redibujar gráficas al volver al tab de estadísticas
        $('#mainTrackingTabs button[data-bs-target="#stats-tab-pane"]').on('shown.bs.tab', function() {
            [chartDistribution, chartTrend, chartTopAbsences, chartInterventions].forEach(function(c) {
                if (c) c.resize();
            });
        });

        // ...existing code...
    }); // <-- Este es el cierre de $(document).ready()

    // ─────────────────────────────────────────────────────────────────────────
    // Funciones del Panel Estadístico
    // ─────────────────────────────────────────────────────────────────────────

    function destroyCharts() {
        if (chartDistribution)  { chartDistribution.destroy();  chartDistribution  = null; }
        if (chartTrend)         { chartTrend.destroy();         chartTrend         = null; }
        if (chartTopAbsences)   { chartTopAbsences.destroy();   chartTopAbsences   = null; }
        if (chartInterventions) { chartInterventions.destroy(); chartInterventions = null; }
    }

    function loadGroupStats(gradeLevel) {
        $.ajax({
            url: 'components/attendance/getGroupStats.php',
            method: 'POST',
            data: { grade_level: gradeLevel },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    renderGroupStats(response.data);
                }
            },
            error: function() {
                console.error('Error al cargar estadísticas del grupo');
            }
        });
    }

    function renderGroupStats(data) {
        // ── KPI Cards ──────────────────────────────────────────────────────────
        $('#kpi-total-students').text(data.total_students);
        $('#kpi-avg-attendance').text(data.avg_attendance + '%');
        $('#kpi-total-classes').text(data.total_classes);
        $('#kpi-interventions').text(data.active_interventions);

        // Mostrar el contenedor ANTES de crear las gráficas para que Chart.js
        // pueda medir correctamente las dimensiones reales del canvas
        $('#statsPlaceholder').hide();
        $('#statsContainer').show();

        destroyCharts();

        // ── Gráfica 1: Distribución de asistencia (Doughnut) ──────────────────
        const ctxDist = document.getElementById('chartAttendanceDistribution').getContext('2d');
        chartDistribution = new Chart(ctxDist, {
            type: 'doughnut',
            data: {
                labels: ['Presente', 'Llegada tardía', 'Ausente'],
                datasets: [{
                    data: [
                        data.distribution.presente,
                        data.distribution.tarde,
                        data.distribution.ausente
                    ],
                    backgroundColor: ['#008080', '#ff8c00', '#dc3545'],
                    borderColor:     ['#006666', '#cc7000', '#b02a37'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                const total = ctx.dataset.data.reduce(function(a, b) { return a + b; }, 0);
                                const pct   = total > 0 ? ((ctx.raw / total) * 100).toFixed(1) : 0;
                                return ' ' + ctx.label + ': ' + ctx.raw + ' (' + pct + '%)';
                            }
                        }
                    }
                }
            }
        });

        // ── Gráfica 2: Tendencia de asistencia por clase (Line) ───────────────
        const ctxTrend = document.getElementById('chartAttendanceTrend').getContext('2d');
        const trendLabels = data.trend.map(function(t, i) {
            const d = new Date(t.date + 'T00:00:00');
            return 'Clase ' + (i + 1) + ' (' + d.toLocaleDateString('es-CO', { day: '2-digit', month: '2-digit' }) + ')';
        });
        chartTrend = new Chart(ctxTrend, {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [{
                    label: '% Asistencia',
                    data: data.trend.map(function(t) { return t.pct_asistencia; }),
                    borderColor: '#30336b',
                    backgroundColor: 'rgba(48,51,107,0.12)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#30336b',
                    pointRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        min: 0,
                        max: 100,
                        ticks: { callback: function(v) { return v + '%'; } }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) { return ' Asistencia: ' + ctx.raw + '%'; }
                        }
                    }
                }
            }
        });

        // ── Gráfica 3: Top 10 estudiantes con más ausencias (Bar horizontal) ──
        const ctxAbs = document.getElementById('chartTopAbsences').getContext('2d');
        const absLabels = data.top_absences.map(function(s) {
            const n = s.nombre || s.student_id;
            return n.length > 22 ? n.substring(0, 22) + '…' : n;
        });
        chartTopAbsences = new Chart(ctxAbs, {
            type: 'bar',
            data: {
                labels: absLabels,
                datasets: [{
                    label: 'Ausencias',
                    data: data.top_absences.map(function(s) { return s.ausencias; }),
                    backgroundColor: 'rgba(220,53,69,0.75)',
                    borderColor: '#dc3545',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: true,
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, ticks: { stepSize: 1 } }
                }
            }
        });

        // ── Gráfica 4: Estado de intervenciones (Bar vertical) ────────────────
        const inv    = data.interventions;
        const ctxInv = document.getElementById('chartInterventions').getContext('2d');
        chartInterventions = new Chart(ctxInv, {
            type: 'bar',
            data: {
                labels: ['Con intervención', 'Resueltas', 'Con estrategia', 'Estrategia cumplida'],
                datasets: [{
                    label: 'Estudiantes',
                    data: [
                        inv.con_intervencion,
                        inv.resueltas,
                        inv.con_estrategia,
                        inv.estrategia_cumplida
                    ],
                    backgroundColor: ['#30336b', '#008080', '#ff8c00', '#198754'],
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                }
            }
        });

        // Forzar recálculo de dimensiones tras el repintado del DOM
        setTimeout(function() {
            [chartDistribution, chartTrend, chartTopAbsences, chartInterventions].forEach(function(c) {
                if (c) c.resize();
            });
        }, 50);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Función para inicializar DataTables
    function initializeSingleDataTable() {
        const table = $('#tracking-table');

        if ($.fn.DataTable.isDataTable(table)) {
            table.DataTable().destroy();
        }

        table.DataTable({
            searching: true,
            paging: false,
            info: false,
            ordering: true,
            lengthChange: false,
            dom: '<"top"f>rt<"bottom">',
            scrollX: true,
            scrollCollapse: true,
            autoWidth: false,
            language: {
                search: "Buscar:",
                zeroRecords: "No se encontraron registros coincidentes",
                emptyTable: "No hay datos disponibles en la tabla",
                infoEmpty: "Mostrando 0 a 0 de 0 registros"
            }
        });
    }

    // Poblar las tablas de seguimiento
    function populateTrackingTables(students, classes) {
        // Quitar tabs - usar una sola tabla
        const table = $('#studentsContainer .table-responsive');
        table.html(`
            <table class="table table-striped table-hover datatable" id="tracking-table">
                <thead>
                    <tr>
                        <th>Tipo ID</th>
                        <th>Documento</th>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Teléfono</th>
                        <th>Estado</th>
                        ${classes.map((classInfo, index) => {
                            const dateFormatted = new Date(classInfo.class_date + 'T00:00:00').toLocaleDateString('es-CO', {
                                day: '2-digit', month: '2-digit'
                            });
                            return `<th class="class-col" title="${classInfo.class_date}">C${index + 1} ${dateFormatted}</th>`;
                        }).join('')}
                        <th>Seguimiento</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        `);

        const tbody = table.find('tbody');

        if (students.length === 0) {
            const colspan = 7 + classes.length;
            tbody.append(`<tr><td colspan="${colspan}" class="text-center">No hay estudiantes registrados</td></tr>`);
        } else {
            students.forEach(student => {
                const row = $('<tr></tr>');

                // Celdas base
                row.append(`<td>${student.document_type || 'N/A'}</td>`);
                row.append(`<td>${student.document_number || 'N/A'}</td>`);
                row.append(`<td style="text-align: left;">${student.name || 'N/A'}</td>`);
                row.append(`<td style="text-align: left;">${student.email || 'N/A'}</td>`);
                row.append(`<td>${student.cell_phone || 'N/A'}</td>`);
                row.append(`<td><span class="badge ${getStudentStatusBadge(student.status)}">${student.status || 'N/A'}</span></td>`);

                // Celdas de asistencia por clase
                classes.forEach((classInfo, index) => {
                    const classNumber = index + 1;
                    const classDate = classInfo.class_date;

                    const attendanceStatus = classInfo.attendance_by_student && classInfo.attendance_by_student[student.document_number] ?
                        classInfo.attendance_by_student[student.document_number] :
                        null;

                    const buttonClass = getAttendanceButtonClass(attendanceStatus);
                    const attendanceText = getAttendanceStatusText(attendanceStatus);

                    row.append(`<td>
                        <button class="btn btn-sm ${buttonClass} observation-btn" 
                                data-bs-toggle="modal" 
                                data-bs-target="#genericObservationModal"
                                data-student-id="${student.document_number}"
                                data-student-name="${student.name}"
                                data-grade-level="${student.grade_level}"
                                data-class-date="${classDate}"
                                data-class-number="${classNumber}"
                                data-attendance-status="${attendanceStatus || ''}"
                                data-attendance-text="${attendanceText}"
                                title="${attendanceText}">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>`);
                });

                // Celda de seguimiento
                row.append(`<td>
                    <button class="btn btn-sm bg-teal-dark text-white attendance-info-btn" 
                            data-bs-toggle="modal"
                            data-bs-target="#genericAttendanceModal"
                            data-student-id="${student.document_number}"
                            data-student-name="${student.name}"
                            data-grade-level="${student.grade_level}">
                        <i class="fas fa-info-circle"></i> Ver
                    </button>
                </td>`);

                tbody.append(row);
            });
        }

        // Crear modales genéricos
        createGenericModals();
    }

    function initializePopovers() {
        $('[data-bs-toggle="popover"]').popover('dispose');
        const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
        [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));
    }

    function getAttendanceButtonClass(attendanceStatus) {
        switch (attendanceStatus) {
            case 'presente':
                return 'bg-teal-dark text-white';
            case 'tarde':
                return 'bg-orange-dark text-white';
            case 'ausente':
                return 'bg-danger text-white';
            default:
                return 'bg-secondary text-white';
        }
    }

    function getAttendanceStatusText(attendanceStatus) {
        switch (attendanceStatus) {
            case 'presente':
                return 'Presente';
            case 'tarde':
                return 'Llegada tardía';
            case 'ausente':
                return 'Ausente';
            default:
                return 'Sin registro';
        }
    }

    function getAttendanceStatusColor(attendanceStatus) {
        switch (attendanceStatus) {
            case 'presente':
                return 'bg-success';
            case 'tarde':
                return 'bg-warning text-dark';
            case 'ausente':
                return 'bg-danger';
            default:
                return 'bg-secondary';
        }
    }

    function getStudentStatusBadge(status) {
        switch (status) {
            case 'Activo':
                return 'bg-success';
            case 'Inactivo':
                return 'bg-danger';
            case 'Retirado':
                return 'bg-dark';
            default:
                return 'bg-secondary';
        }
    }

    // Crear modales genéricos
    function createGenericModals() {
        $('.observation-modal, .attendance-info-modal').remove();

        // Modal genérico para observaciones
        const observationModal = `
        <div class="modal fade observation-modal" id="genericObservationModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header" style="background: linear-gradient(135deg, #008080, #005a5a); color:#fff;">
                        <h5 class="modal-title" id="observationModalTitle">Observación</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" style="max-height:65vh; overflow-y:auto;">
                        <div class="mb-3">
                            <label class="form-label text-muted small mb-0">Estudiante</label>
                            <div class="fw-semibold" id="observationStudentLabel"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small mb-0">Fecha de Clase</label>
                            <div class="fw-semibold" id="observationClassLabel"></div>
                        </div>
                        <div class="mb-3" id="attendanceStatusContainer" style="display:none;">
                            <span class="badge" id="attendanceStatusBadge"></span>
                        </div>

                        <!-- Notificacion de incapacidad -->
                        <div class="alert alert-warning border-0 d-flex align-items-center" id="incapacidadNotification" style="display:none; background: linear-gradient(135deg, #fff8e1, #ffecb3); border-left: 4px solid #e65100 !important;">
                            <i class="fas fa-file-medical text-danger fs-5 me-3"></i>
                            <div>
                                <strong>Incapacidad medica registrada</strong>
                                <p class="mb-0 small">Este estudiante tiene una incapacidad vigente en esta fecha. La ausencia esta justificada por el reporte medico.</p>
                            </div>
                        </div>

                        <!-- Historial de observaciones -->
                        <div class="card border mb-3 w-100">
                            <div class="card-header bg-light py-2">
                                <h6 class="mb-0"><i class="fas fa-history me-2"></i>Historial de Observaciones</h6>
                            </div>
                            <div class="card-body p-0 w-100 d-flex align-items-center justify-content-center">
                                <div id="observationHistoryList"></div>
                            </div>
                        </div>

                        <!-- Nueva observación -->
                        <form id="genericObservationForm">
                            <div class="border-top pt-3">
                                <h6 class="mb-3"><i class="fas fa-plus-circle me-2 text-teal-dark"></i>Agregar Observación</h6>
                                <div class="mb-2">
                                    <label class="form-label small fw-semibold">Tipo de Observación</label>
                                    <select class="form-select form-select-sm" name="observation_type" required>
                                        <option value="">Seleccionar...</option>
                                        <option value="no responde">No responde</option>
                                        <option value="condicion de salud">Condición de salud</option>
                                        <option value="dificultades economicas">Dificultades económicas</option>
                                        <option value="dificultades tecnicas con equipo">Dificultades técnicas</option>
                                        <option value="incompatibilidad con los horarios">Incompatibilidad horaria</option>
                                        <option value="insercion academica">Inserción académica</option>
                                        <option value="insercion laboral">Inserción laboral</option>
                                        <option value="inconformidad con el proceso">Inconformidad con el proceso</option>
                                        <option value="motivos personales">Motivos personales</option>
                                        <option value="otras causas">Otras causas</option>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-semibold">Observaciones</label>
                                    <textarea class="form-control form-control-sm" name="observation_text" rows="3"
                                            placeholder="Escriba sus observaciones..."></textarea>
                                </div>
                            </div>
                            <input type="hidden" name="student_id">
                            <input type="hidden" name="grade_level">
                            <input type="hidden" name="class_date">
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn text-white" id="saveObservationBtn" style="background: linear-gradient(135deg, #008080, #005a5a);">
                            <i class="fas fa-plus me-1"></i> Agregar Observación
                        </button>
                    </div>
                </div>
            </div>
        </div>
        `;

        // Modal genérico para información de asistencia / seguimiento
        const attendanceModal = `
        <div class="modal fade attendance-info-modal" id="genericAttendanceModal" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content shadow-lg border-0">
                    <div class="modal-header text-white" style="background: linear-gradient(135deg, #30336b, #4a4e8a);">
                        <h5 class="modal-title d-flex align-items-center gap-2" id="attendanceModalTitle">
                            <span class="badge bg-white bg-opacity-25 rounded-circle p-2">
                                <i class="fas fa-user-clock fs-6"></i>
                            </span>
                            Seguimiento de Asistencia
                        </h5>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-light btn-sm" id="showHistoryBtn">
                                <i class="fas fa-history me-1"></i> Historial
                            </button>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                    </div>
                    <div class="modal-body p-4" style="max-height: 70vh; overflow-y: auto;">
                        <div id="attendanceLoading" class="text-center py-5">
                            <div class="spinner-border" style="color:#30336b; width:3rem; height:3rem;" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <p class="mt-3 text-muted">Cargando informacion de asistencia...</p>
                        </div>

                        <div id="attendanceContent" style="display: none;">

                            <!-- Cabecera con info del estudiante -->
                            <div class="d-flex align-items-center mb-4 p-3" style="background:#f0f2f8; border-radius:12px;">
                                <div class="rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width:52px;height:52px;background: linear-gradient(135deg, #30336b, #4a4e8a);">
                                    <i class="fas fa-user-graduate text-white fs-4"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="mb-0 fw-bold text-dark" id="attendanceStudentInfo"></h5>
                                    <small class="text-muted" id="attendanceStudentGrade"></small>
                                </div>
                                <div class="ms-2">
                                    <span class="badge bg-indigo-dark px-3 py-2 fs-6" id="attendanceStatusBadge" style="display:none;"></span>
                                </div>
                            </div>

                            <!-- Estadisticas de asistencia -->
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-body text-center border-start border-5 border-danger py-3">
                                            <div class="mb-2">
                                                <i class="fas fa-user-times text-danger fs-4"></i>
                                            </div>
                                            <h6 class="text-muted mb-1" style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.5px;">Total Inasistencias</h6>
                                            <div class="fs-1 fw-bold text-danger" id="absencesDisplay">0/0</div>
                                            <small class="text-muted" style="font-size:0.7rem;">Ausencias / Total clases</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-body text-center border-start border-5 border-success py-3">
                                            <div class="mb-2">
                                                <i class="fas fa-check-circle text-success fs-4"></i>
                                            </div>
                                            <h6 class="text-muted mb-1" style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.5px;">% Asistencia</h6>
                                            <div class="fs-1 fw-bold" id="attendancePercentage">0%</div>
                                            <div class="progress mt-2 mx-auto" style="height:6px; max-width:150px;">
                                                <div class="progress-bar bg-success" id="attendanceProgressBar" style="width:0%;"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-body text-center border-start border-5 border-warning py-3">
                                            <div class="mb-2">
                                                <i class="fas fa-exclamation-triangle text-warning fs-4"></i>
                                            </div>
                                            <h6 class="text-muted mb-1" style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.5px;">% Inasistencia</h6>
                                            <div class="fs-1 fw-bold text-warning" id="absencePercentage">0%</div>
                                            <div class="progress mt-2 mx-auto" style="height:6px; max-width:150px;">
                                                <div class="progress-bar bg-warning" id="absenceProgressBar" style="width:0%;"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Formulario de gestion -->
                            <form id="genericAttendanceForm">
                                <input type="hidden" name="student_id">
                                <input type="hidden" name="grade_level">
                                <input type="hidden" name="course_type">

                                <div class="border rounded-3 p-3 mb-3 bg-white shadow-sm">
                                    <div class="d-flex align-items-center mb-3 pb-2 border-bottom">
                                        <span class="badge bg-warning text-dark p-2 me-2"><i class="fas fa-tasks"></i></span>
                                        <h6 class="mb-0 fw-bold">Gestión de Subsanación</h6>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-lg-6">
                                            <label class="form-label small fw-semibold text-secondary mb-1">¿Requiere subsanación?</label>
                                            <select class="form-select" name="requires_intervention">
                                                <option value="">Seleccionar...</option>
                                                <option value="Si">Sí</option>
                                                <option value="No">No</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-6">
                                            <label class="form-label small fw-semibold text-secondary mb-1">Responsable</label>
                                            <input type="text" class="form-control" name="responsible_username" readonly>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label small fw-semibold text-secondary mb-1">Observación</label>
                                            <textarea class="form-control" name="intervention_observation" rows="2"
                                                     placeholder="Describa las acciones de subsanación..."></textarea>
                                        </div>
                                        <div class="col-lg-6">
                                            <label class="form-label small fw-semibold text-secondary mb-1">¿Resuelta?</label>
                                            <select class="form-select" name="is_resolved">
                                                <option value="">Seleccionar...</option>
                                                <option value="Si">Sí</option>
                                                <option value="No">No</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="border rounded-3 p-3 mb-3 bg-white shadow-sm">
                                    <div class="d-flex align-items-center mb-3 pb-2 border-bottom">
                                        <span class="badge text-white p-2 me-2" style="background:#008080;"><i class="fas fa-lightbulb"></i></span>
                                        <h6 class="mb-0 fw-bold">Estrategia Adicional</h6>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-lg-6">
                                            <label class="form-label small fw-semibold text-secondary mb-1">¿Requiere estrategia?</label>
                                            <select class="form-select" name="requires_additional_strategy">
                                                <option value="">Seleccionar...</option>
                                                <option value="Si">Sí</option>
                                                <option value="No">No</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-6">
                                            <label class="form-label small fw-semibold text-secondary mb-1">¿Cumple estrategia?</label>
                                            <select class="form-select" name="strategy_fulfilled">
                                                <option value="">Seleccionar...</option>
                                                <option value="Si">Sí</option>
                                                <option value="No">No</option>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label small fw-semibold text-secondary mb-1">Observación</label>
                                            <textarea class="form-control" name="strategy_observation" rows="2"
                                                     placeholder="Describa la estrategia implementada..."></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="border rounded-3 p-3 bg-white shadow-sm">
                                    <div class="d-flex align-items-center mb-3 pb-2 border-bottom">
                                        <span class="badge bg-secondary p-2 me-2"><i class="fas fa-sign-out-alt"></i></span>
                                        <h6 class="mb-0 fw-bold">Información de Retiro</h6>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-lg-8">
                                            <label class="form-label small fw-semibold text-secondary mb-1">Motivo de retiro</label>
                                            <select class="form-select" name="withdrawal_reason">
                                                <option value="">No aplica / Estudiante activo</option>
                                                <option value="Económico">Económico</option>
                                                <option value="Sociológico">Sociológico</option>
                                                <option value="Psicológico">Psicológico</option>
                                                <option value="Institucional">Institucional</option>
                                                <option value="Académico">Académico</option>
                                                <option value="Laboral">Laboral</option>
                                                <option value="Personal">Personal</option>
                                                <option value="Inconformidad">Inconformidad</option>
                                                <option value="Inactivo">Inactivo</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-4">
                                            <label class="form-label small fw-semibold text-secondary mb-1">Fecha de retiro</label>
                                            <input type="date" class="form-control" name="withdrawal_date">
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top py-3">
                        <button type="button" class="btn btn-outline-secondary btn-lg px-4" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Cerrar
                        </button>
                        <button type="button" class="btn btn-lg text-white px-4" id="saveAttendanceBtn" style="background: linear-gradient(135deg, #30336b, #4a4e8a);">
                            <i class="fas fa-save me-2"></i> Guardar Información
                        </button>
                    </div>
                </div>
            </div>
        </div>
        `;

        $('body').append(observationModal);
        $('body').append(attendanceModal);

        setupModalEventListeners();
    }

    // Configurar event listeners para los modales
    function setupModalEventListeners() {
        // Event listener para botones de observación
        $(document).off('click', '.observation-btn').on('click', '.observation-btn', function(e) {
            e.preventDefault();

            const studentId = $(this).data('student-id');
            const studentName = $(this).data('student-name');
            const gradeLevel = $(this).data('grade-level');
            const classDate = $(this).data('class-date');
            const classNumber = $(this).data('class-number');
            const attendanceStatus = $(this).data('attendance-status');
            const attendanceText = $(this).data('attendance-text');

            $('#observationModalTitle').text('Observaci\u00f3n - Clase ' + classNumber);
            $('#observationStudentLabel').text(studentName);
            $('#observationClassLabel').text(classDate);

            const attendanceColor = getAttendanceStatusColor(attendanceStatus);
            $('#attendanceStatusBadge').text(attendanceText).removeClass().addClass('badge ' + attendanceColor);
            $('#attendanceStatusContainer').show();

            const form = $('#genericObservationForm');
            form[0].reset();
            form.find('[name="student_id"]').val(studentId);
            form.find('[name="grade_level"]').val(gradeLevel);
            form.find('[name="class_date"]').val(classDate);

            $('#genericObservationModal').data('current-button', this);

            // Verificar si hay incapacidad en esta fecha
            $('#incapacidadNotification').hide();
            $.ajax({
                url: 'components/attendance/checkIncapacidad.php',
                method: 'POST',
                data: {
                    student_id: studentId,
                    class_date: classDate,
                    grade_level: gradeLevel
                },
                dataType: 'json',
                success: function(res) {
                    if (res.success && res.has_incapacidad) {
                        $('#incapacidadNotification').slideDown();
                    }
                }
            });

            // Cargar historial de observaciones
            loadObservationGeneric(studentId, gradeLevel, classDate);

            $('#genericObservationModal').modal('show');
        });

        $('#genericObservationModal').on('hidden.bs.modal', function() {
            $('#attendanceStatusContainer').hide();
            $('#incapacidadNotification').hide();
        });

        // Event listener para botones de seguimiento
        $(document).off('click', '.attendance-info-btn').on('click', '.attendance-info-btn', function(e) {
            e.preventDefault();
            const studentId = $(this).data('student-id');
            const studentName = $(this).data('student-name');
            const gradeLevel = $(this).data('grade-level');
            loadAttendanceInfoGeneric(studentId, gradeLevel, studentName);
        });

        // Guardar observación
        $('#saveObservationBtn').off('click').on('click', function() {
            saveObservationGeneric();
        });

        // Guardar información de asistencia
        $('#saveAttendanceBtn').off('click').on('click', function() {
            saveAttendanceManagementGeneric();
        });

        // Botón de historial
        $(document).off('click', '#showHistoryBtn').on('click', '#showHistoryBtn', function(e) {
            e.preventDefault();

            const modal = $('#genericAttendanceModal');
            const studentId = modal.find('input[name="student_id"]').val();
            const gradeLevel = modal.find('input[name="grade_level"]').val();
            const courseType = modal.find('input[name="course_type"]').val();
            const studentName = modal.find('#attendanceModalTitle').text().replace('Información de Asistencia', '').trim();

            $('#genericAttendanceModal').modal('hide');

            $('#genericAttendanceModal').on('hidden.bs.modal', function() {
                $(this).off('hidden.bs.modal');

                $('#historyModalLabel').html(`<i class="fas fa-history me-2"></i> Historial de Gestiones - ${studentName}`);

                $('#historyModal').data('student-info', {
                    id: studentId,
                    name: studentName,
                    grade_level: gradeLevel,
                    course_type: courseType
                });

                $('#historyModal').modal('show');
                $('#historyLoading').show();
                $('#historyContent').hide();

                loadHistoryData(studentId, gradeLevel, courseType);
            });
        });

        // Exportar historial
        $(document).off('click', '#exportHistoryBtn').on('click', '#exportHistoryBtn', function() {
            const historyData = $('#historyModal').data('history-data');
            const studentInfo = $('#historyModal').data('student-info');

            if (!historyData || !studentInfo) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No hay datos para exportar'
                });
                return;
            }

            Swal.fire({
                title: 'Generando archivo Excel',
                text: 'Por favor espere...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: 'components/attendance/exportHistoryToExcel.php',
                method: 'POST',
                data: {
                    student_id: studentInfo.id,
                    student_name: studentInfo.name,
                    grade_level: studentInfo.grade_level,
                    history_data: JSON.stringify(historyData)
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(data) {
                    const blob = new Blob([data], {
                        type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                    });
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `Historial_Gestiones_${studentInfo.id}_${new Date().toISOString().split('T')[0]}.xlsx`;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);
                    Swal.close();
                    Swal.fire({
                        icon: 'success',
                        title: 'Exportación exitosa',
                        text: 'El archivo se ha descargado correctamente',
                        timer: 2000,
                        showConfirmButton: false
                    });
                },
                error: function() {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de exportación',
                        text: 'No se pudo generar el archivo Excel'
                    });
                }
            });
        });
    }

    // Cargar observación existente
    function loadObservationGeneric(studentId, gradeLevel, classDate) {
        // Limpiar formulario de nueva observacion
        const form = $('#genericObservationForm');
        form[0].reset();
        form.find('[name="observation_type"]').val('');

        $.ajax({
            url: 'components/attendance/getObservation.php',
            method: 'POST',
            data: {
                student_id: studentId,
                grade_level: gradeLevel,
                class_date: classDate
            },
            dataType: 'json',
            success: function(response) {
                const $list = $('#observationHistoryList');
                if (response.success && response.data && response.data.length) {
                    let html = '';
                    response.data.forEach(function(obs, i) {
                        const dateStr = obs.created_at
                            ? new Date(obs.created_at.replace(' ', 'T')).toLocaleString('es-CO', { day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit' })
                            : '--';
                        html += '<div class="p-3 border-bottom align-items-center">' +
                            '<div class="d-flex justify-content-between align-items-center">' +
                                '<span class="badge" style="background:#008080;color:#fff;white-space:nowrap;">' + escHtmlObs(obs.observation_type) + '</span>' +
                                '<small class="text-muted flex-shrink-0 ms-2">' + dateStr + '</small>' +
                            '</div>' +
                            '<div class="mt-2" style="word-break:break-word;">' + escHtmlObs(obs.observation_text || '<em class="text-muted">Sin detalle</em>') + '</div>' +
                            '<div class="mt-1"><small class="text-muted"><i class="fas fa-user me-1"></i>' + escHtmlObs(obs.created_by_name || obs.created_by || '--') + '</small></div>' +
                        '</div>';
                    });
                    $list.html(html);
                } else {
                    $list.html('<p class="text-muted text-center py-3 mb-0">Sin observaciones previas para esta clase.</p>');
                }
            },
            error: function() {
                $('#observationHistoryList').html('<p class="text-danger small">Error al cargar historial.</p>');
            }
        });
    }

    function escHtmlObs(str) {
        if (!str) return '';
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // Guardar observación
    function saveObservationGeneric() {
        const form = $('#genericObservationForm');
        const formData = form.serialize();
        const saveButton = $('#saveObservationBtn');
        const currentButton = $('#genericObservationModal').data('current-button');

        const originalText = saveButton.html();
        saveButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
            url: 'components/attendance/saveObservation.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            timeout: 10000,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '\u00c9xito',
                        text: 'Observaci\u00f3n guardada correctamente',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    if (currentButton) {
                        $(currentButton).removeClass('btn-outline-primary bg-secondary').addClass('bg-cyan-dark');
                    }
                    // Refrescar historial y limpiar formulario
                    const studentId = form.find('[name="student_id"]').val();
                    const gradeLevel = form.find('[name="grade_level"]').val();
                    const classDate = form.find('[name="class_date"]').val();
                    form[0].reset();
                    form.find('[name="student_id"]').val(studentId);
                    form.find('[name="grade_level"]').val(gradeLevel);
                    form.find('[name="class_date"]').val(classDate);
                    loadObservationGeneric(studentId, gradeLevel, classDate);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al guardar la observación: ' + response.message
                    });
                }
            },
            error: function(xhr, status) {
                let errorMessage = 'No se pudo conectar con el servidor';
                if (status === 'timeout') errorMessage = 'La operación tardó demasiado tiempo';
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: errorMessage
                });
            },
            complete: function() {
                saveButton.prop('disabled', false).html(originalText);
            }
        });
    }

    // Cargar información de asistencia en modal genérico
    function loadAttendanceInfoGeneric(studentId, gradeLevel, studentName) {
        const modal = $('#genericAttendanceModal');

        modal.find('#attendanceModalTitle').html('<i class="fas fa-user-clock me-2"></i> Seguimiento de Asistencia');
        modal.find('#attendanceStudentInfo').text(studentName);
        modal.find('#attendanceStudentGrade').text('Grupo: ' + gradeLevel + ' | Documento: ' + studentId);
        const form = modal.find('#genericAttendanceForm');
        form[0].reset();
        form.find('[name="student_id"]').val(studentId);
        form.find('[name="grade_level"]').val(gradeLevel);

        modal.find('#attendanceLoading').show();
        modal.find('#attendanceContent').hide();

        const username = '<?php echo isset($_SESSION["username"]) ? $_SESSION["username"] : "usuario"; ?>';

        Promise.all([
            $.ajax({
                url: 'components/attendance/getAttendanceStats.php',
                method: 'POST',
                data: {
                    student_id: studentId,
                    grade_level: gradeLevel
                },
                dataType: 'json'
            }),
            $.ajax({
                url: 'components/attendance/getAttendanceManagement.php',
                method: 'POST',
                data: {
                    student_id: studentId,
                    grade_level: gradeLevel
                },
                dataType: 'json'
            })
        ]).then(([statsResponse, managementResponse]) => {
            if (statsResponse && statsResponse.success) {
                const stats = statsResponse.data;
                const attPct = stats.attendancePercentage || 0;
                const absPct = stats.absencePercentage || 0;

                modal.find('#absencesDisplay').text(stats.absencesDisplay || '0/0');
                modal.find('#attendancePercentage').text(attPct + '%');
                modal.find('#absencePercentage').text(absPct + '%');

                const attEl = modal.find('#attendancePercentage');
                attEl.removeClass('text-success text-warning text-danger');
                if (attPct >= 85) attEl.addClass('text-success');
                else if (attPct >= 70) attEl.addClass('text-warning');
                else attEl.addClass('text-danger');

                modal.find('#attendanceProgressBar')
                    .css('width', attPct + '%')
                    .removeClass('bg-success bg-warning bg-danger')
                    .addClass(attPct >= 85 ? 'bg-success' : (attPct >= 70 ? 'bg-warning' : 'bg-danger'));

                modal.find('#absenceProgressBar')
                    .css('width', absPct + '%')
                    .removeClass('bg-success bg-warning bg-danger')
                    .addClass(absPct <= 10 ? 'bg-success' : (absPct <= 25 ? 'bg-warning' : 'bg-danger'));
            }

            form.find('[name="responsible_username"]').val(username);
            if (managementResponse && managementResponse.success && managementResponse.data) {
                const data = managementResponse.data;
                form.find('[name="requires_intervention"]').val(data.requires_intervention || '');
                form.find('[name="intervention_observation"]').val(data.intervention_observation || '');
                form.find('[name="is_resolved"]').val(data.is_resolved || '');
                form.find('[name="requires_additional_strategy"]').val(data.requires_additional_strategy || '');
                form.find('[name="strategy_observation"]').val(data.strategy_observation || '');
                form.find('[name="strategy_fulfilled"]').val(data.strategy_fulfilled || '');
                form.find('[name="withdrawal_reason"]').val(data.withdrawal_reason || '');
                form.find('[name="withdrawal_date"]').val(data.withdrawal_date ? data.withdrawal_date.split(' ')[0] : '');
                if (data.responsible_username) form.find('[name="responsible_username"]').val(data.responsible_username);
            }

            modal.find('#attendanceLoading').hide();
            modal.find('#attendanceContent').show();

        }).catch(error => {
            console.error("Error cargando información de asistencia:", error);
            Swal.fire('Error', 'No se pudo cargar la información de asistencia.', 'error');
            modal.find('#attendanceLoading').hide();
        });
    }

    // Guardar gestión de asistencia
    function saveAttendanceManagementGeneric() {
        const form = $('#genericAttendanceForm');
        const formData = form.serialize();
        const saveButton = $('#saveAttendanceBtn');

        const originalText = saveButton.html();
        saveButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
            url: 'components/attendance/saveAttendanceManagement.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            timeout: 15000,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Guardado exitoso',
                        text: 'La información de gestión ha sido guardada correctamente',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    setTimeout(() => {
                        $('#genericAttendanceModal').modal('hide');
                    }, 2000);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al guardar: ' + (response.message || 'Error desconocido')
                    });
                }
            },
            error: function(xhr, status) {
                let errorMessage = 'No se pudo conectar con el servidor';
                if (status === 'timeout') errorMessage = 'La operación tardó demasiado tiempo';
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: errorMessage
                });
            },
            complete: function() {
                saveButton.prop('disabled', false).html(originalText);
            }
        });
    }

    // Cargar historial de gestiones
    function loadHistoryData(studentId, gradeLevel, courseType) {
        $.ajax({
            url: 'components/attendance/getAttendanceManagementHistory.php',
            method: 'POST',
            data: {
                student_id: studentId,
                grade_level: gradeLevel,
                course_type: courseType
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#historyModal').data('history-data', response.data);

                    const tbody = $('#historyTable tbody');
                    tbody.empty();

                    if (response.data && response.data.length > 0) {
                        response.data.forEach(function(item) {
                            const row = $('<tr></tr>');
                            row.append(`<td>${item.formatted_date}</td>`);
                            row.append(`<td>${item.responsible_name || item.responsible_username || 'N/A'}</td>`);
                            row.append(`<td>${item.requires_intervention || 'N/A'}</td>`);

                            const interventionObs = item.intervention_observation ?
                                `<span class="text-truncate d-inline-block" style="max-width: 200px;" title="${item.intervention_observation}">${item.intervention_observation}</span>` :
                                'N/A';
                            row.append(`<td>${interventionObs}</td>`);
                            row.append(`<td>${item.is_resolved || 'N/A'}</td>`);
                            row.append(`<td>${item.requires_additional_strategy || 'N/A'}</td>`);

                            const strategyObs = item.strategy_observation ?
                                `<span class="text-truncate d-inline-block" style="max-width: 200px;" title="${item.strategy_observation}">${item.strategy_observation}</span>` :
                                'N/A';
                            row.append(`<td>${strategyObs}</td>`);
                            row.append(`<td>${item.strategy_fulfilled || 'N/A'}</td>`);
                            row.append(`<td>${item.withdrawal_reason || 'N/A'}</td>`);

                            const withdrawalDate = item.withdrawal_date ? new Date(item.withdrawal_date).toLocaleDateString() : 'N/A';
                            row.append(`<td>${withdrawalDate}</td>`);

                            tbody.append(row);
                        });
                    } else {
                        tbody.append(`<tr><td colspan="10" class="text-center">No hay registros de gestión para este estudiante</td></tr>`);
                        $('#exportHistoryBtn').prop('disabled', true);
                    }

                    $('#historyLoading').hide();
                    $('#historyContent').show();
                } else {
                    $('#historyTable tbody').html(`<tr><td colspan="10" class="text-center text-danger">Error al cargar historial</td></tr>`);
                    $('#historyLoading').hide();
                    $('#historyContent').show();
                    $('#exportHistoryBtn').prop('disabled', true);
                }
            },
            error: function() {
                $('#historyTable tbody').html(`<tr><td colspan="10" class="text-center text-danger">Error de conexión al cargar el historial</td></tr>`);
                $('#historyLoading').hide();
                $('#historyContent').show();
                $('#exportHistoryBtn').prop('disabled', true);
            }
        });
    }

    // Modal de historial
    $('body').append(`
    <div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="historyModalLabel">
                        <i class="fas fa-history me-2"></i> Historial de Gestiones
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="historyLoading" class="text-center p-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2">Cargando historial...</p>
                    </div>
                    <div id="historyContent" style="display: none;">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="historyTable">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Responsable</th>
                                        <th>Requiere subsanación</th>
                                        <th>Observación subsanación</th>
                                        <th>Resuelta</th>
                                        <th>Requiere estrategia</th>
                                        <th>Observación estrategia</th>
                                        <th>Estrategia cumplida</th>
                                        <th>Motivo de retiro</th>
                                        <th>Fecha de retiro</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" id="exportHistoryBtn">
                        <i class="fas fa-file-excel me-2"></i> Exportar Historial
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    `);
</script>
