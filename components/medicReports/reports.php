<?php
$rol = $infoUsuario['rol'];
?>
<style>
    .btn-pulse {
        animation: pulse-animation 2s infinite;
    }
    @keyframes pulse-animation {
        0% { box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.7); }
        70% { box-shadow: 0 0 0 12px rgba(255, 255, 255, 0); }
        100% { box-shadow: 0 0 0 0 rgba(255, 255, 255, 0); }
    }
    .text-cyan-dark {
        color: var(--bs-cyan-dark) !important;
    }
</style>
<div>
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-cyan-dark text-white text-center">
                    <h5 class="mb-0"><i class="fas fa-search me-2"></i>Busqueda de estudiante por numero de documento</h5>
                </div>
                <div class="card-body">
                    <div class="row w-100">
                        <div class="col-12 col-md-8">
                            <input type="number" id="searchDocument" class="form-control form-control-lg" placeholder="Ingresa el numero de documento del estudiante" maxlength="15" oninput="this.value = this.value.slice(0, 15);">
                        </div>
                        <div class="col-12 col-md-4 mt-2 mt-md-0">
                            <button type="button" id="btnBuscar" class="btn bg-cyan-dark text-white btn-lg w-100"><i class="fas fa-search me-2"></i>Buscar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <div class="card shadow-lg" id="resultCard" style="display: none; border-color: var(--bs-cyan-dark);">
                <div class="card-header d-flex justify-content-between align-items-center bg-cyan-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>Informacion del Estudiante</h5>
                    <button type="button" id="btnReportar" class="btn btn-light btn-pulse text-cyan-dark fw-bold">
                        <i class="fas fa-file-medical me-1"></i>Reportar Incapacidad
                    </button>
                </div>
                <div class="card-body bg-light">
                    <input type="hidden" id="studentId">
                    <input type="hidden" id="studentDocument">
                    <div class="container-fluid">
                        <div class="row mb-3 p-3 bg-white rounded shadow-sm">
                            <div class="col-12 col-md-3">
                                <label class="form-label fw-bold text-cyan-dark"><i class="fas fa-id-card me-1"></i> Documento:</label>
                                <input type="text" id="resultDocument" class="form-control" readonly>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold text-cyan-dark"><i class="fas fa-user-tag me-1"></i> Nombre:</label>
                                <input type="text" id="resultName" class="form-control" readonly>
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label fw-bold text-cyan-dark"><i class="fas fa-graduation-cap me-1"></i> Grado:</label>
                                <input type="text" id="resultGrade" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="row mb-3 p-3 bg-white rounded shadow-sm">
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold text-cyan-dark"><i class="fas fa-phone me-1"></i> Telefono:</label>
                                <input type="text" id="resultPhone" class="form-control" readonly>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold text-cyan-dark"><i class="fas fa-envelope me-1"></i> Email:</label>
                                <input type="email" id="resultEmail" class="form-control" readonly>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold text-cyan-dark"><i class="fas fa-map-marker-alt me-1"></i> Direccion:</label>
                                <input type="text" id="resultAddress" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="row mb-0 p-3 bg-white rounded shadow-sm">
                            <div class="col-12 col-md-3">
                                <label class="form-label fw-bold text-cyan-dark"><i class="fas fa-user-friends me-1"></i> Acudiente:</label>
                                <input type="text" id="resultAcudiente" class="form-control" readonly>
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label fw-bold text-cyan-dark"><i class="fas fa-phone me-1"></i> Tel. Acudiente:</label>
                                <input type="text" id="resultPhoneAcudiente" class="form-control" readonly>
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label fw-bold text-cyan-dark"><i class="fas fa-building me-1"></i> Sede:</label>
                                <input type="text" id="resultSede" class="form-control" readonly>
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label fw-bold text-cyan-dark"><i class="fas fa-check-circle me-1"></i> Estado:</label>
                                <input type="text" id="resultStatus" class="form-control" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <div class="card shadow-lg" id="reportsCard" style="display: none; border-color: var(--bs-cyan-dark);">
                <div class="card-header bg-cyan-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Incapacidades registradas</h5>
                </div>
                <div class="card-body bg-light p-3">
                    <div class="table-responsive w-100">
                        <table class="table table-striped table-hover align-middle w-100" id="reportsTable">
                            <thead>
                                <tr>
                                    <th>Desde</th>
                                    <th>Hasta</th>
                                    <th>Descripcion</th>
                                    <th>Observacion</th>
                                    <th>Comprobante</th>
                                    <th>Registrado por</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="reportsTableBody">
                            </tbody>
                        </table>
                    </div>
                    <div id="noReportsMessage" class="text-center text-muted py-4" style="display: none;">
                        <i class="fas fa-info-circle me-2"></i>No hay incapacidades registradas para este estudiante.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('btnBuscar').addEventListener('click', function() {
        const documentNumber = document.getElementById('searchDocument').value.trim();
        if (!documentNumber) {
            Swal.fire('Error', 'Ingresa un numero de documento.', 'error');
            return;
        }

        Swal.fire({
            title: 'Buscando...',
            text: 'Consultando informacion del estudiante',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch('components/medicReports/searchStudent.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'document_number=' + encodeURIComponent(documentNumber)
            })
            .then(response => response.json())
            .then(data => {
                Swal.close();
                if (data.success) {
                    const emptyToSinRegistrar = function(val) { return (val != null && String(val).trim() !== '') ? val : 'SIN REGISTRAR'; };

                    document.getElementById('studentId').value = data.student.id;
                    document.getElementById('studentDocument').value = data.student.document_number;
                    document.getElementById('resultDocument').value = emptyToSinRegistrar(data.student.document_number);
                    document.getElementById('resultName').value = emptyToSinRegistrar(data.student.name);
                    document.getElementById('resultGrade').value = emptyToSinRegistrar(data.student.grade_level);
                    document.getElementById('resultPhone').value = emptyToSinRegistrar(data.student.cell_phone);
                    document.getElementById('resultEmail').value = emptyToSinRegistrar(data.student.email);
                    document.getElementById('resultAddress').value = emptyToSinRegistrar(data.student.address);
                    document.getElementById('resultAcudiente').value = emptyToSinRegistrar(data.student.nombre_acudiente);
                    document.getElementById('resultPhoneAcudiente').value = emptyToSinRegistrar(data.student.telefono_acudiente);
                    document.getElementById('resultSede').value = emptyToSinRegistrar(data.student.sede);
                    document.getElementById('resultStatus').value = emptyToSinRegistrar(data.student.status);

                    document.getElementById('resultCard').style.display = 'block';
                    document.getElementById('reportsCard').style.display = 'block';

                    renderReports(data.reports);
                } else {
                    Swal.fire('Estudiante no encontrado', data.message, 'error');
                    document.getElementById('resultCard').style.display = 'none';
                    document.getElementById('reportsCard').style.display = 'none';
                }
            })
            .catch(error => {
                Swal.close();
                Swal.fire('Error', 'Error en la solicitud: ' + error.message, 'error');
            });
    });

    document.getElementById('searchDocument').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            document.getElementById('btnBuscar').click();
        }
    });

    function renderReports(reports) {
        const tbody = document.getElementById('reportsTableBody');
        const noReports = document.getElementById('noReportsMessage');
        tbody.innerHTML = '';

        if ($.fn.DataTable.isDataTable('#reportsTable')) {
            $('#reportsTable').DataTable().destroy();
        }

        if (!reports || reports.length === 0) {
            noReports.style.display = 'block';
            document.getElementById('reportsTable').style.display = 'none';
            return;
        }

        noReports.style.display = 'none';
        document.getElementById('reportsTable').style.display = '';

        reports.forEach(report => {
            const row = document.createElement('tr');

            const formatDate = (dateStr) => {
                if (!dateStr) return 'N/A';
                const parts = dateStr.split('-');
                return parts[2] + '/' + parts[1] + '/' + parts[0];
            };

            row.innerHTML = `
                <td><span class="badge bg-warning text-dark">${formatDate(report.start_date)}</span></td>
                <td><span class="badge bg-danger">${formatDate(report.end_date)}</span></td>
                <td>${escapeHtml(report.description)}</td>
                <td>${escapeHtml(report.observation || '-')}</td>
                <td>
                    ${report.file_name ? `
                        <button class="btn btn-sm btn-outline-secondary" onclick="viewComprobante('${report.file_name}')" title="Ver comprobante">
                            <i class="fas fa-file-medical-alt"></i> Ver
                        </button>` : '<span class="text-muted">Sin comprobante</span>'}
                </td>
                <td><small class="text-muted">${escapeHtml(report.reported_by_name || report.created_by || '-')}</small></td>
                <td>
                    <button class="btn btn-sm btn-outline-warning me-1" onclick="editReport(${report.id}, '${report.start_date}', '${report.end_date}')" title="Editar fechas">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteReport(${report.id})" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        });

        $('#reportsTable').DataTable({
            responsive: true,
            language: {
                url: 'controller/datatable_esp.json'
            }
        });
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function viewComprobante(fileName) {
        const ext = fileName.split('.').pop().toLowerCase();
        const url = 'uploads/incapacidades/' + fileName;
        const isPDF = ext === 'pdf';
        const isImage = ['jpg', 'jpeg', 'png'].includes(ext);

        if (isPDF) {
            Swal.fire({
                title: '<i class="fas fa-file-pdf me-2"></i>Comprobante',
                html: `
                    <div style="width: 100%; height: 70vh;">
                        <iframe src="${url}" style="width: 100%; height: 100%; border: none;"></iframe>
                    </div>
                `,
                width: '900px',
                showCloseButton: true,
                showConfirmButton: false,
                customClass: {
                    popup: 'swal-comprobante-popup'
                }
            });
        } else if (isImage) {
            Swal.fire({
                title: '<i class="fas fa-image me-2"></i>Comprobante',
                html: `<img src="${url}" alt="Comprobante" style="max-width: 100%; max-height: 70vh; border-radius: 8px;">`,
                width: 'auto',
                showCloseButton: true,
                showConfirmButton: false,
                customClass: {
                    popup: 'swal-comprobante-popup'
                }
            });
        } else {
            window.open(url, '_blank');
        }
    }

    document.getElementById('btnReportar').addEventListener('click', function() {
        const studentName = document.getElementById('resultName').value;
        const studentId = document.getElementById('studentId').value;

        Swal.fire({
            title: '<i class="fas fa-file-medical me-2"></i>Reportar Incapacidad',
            html: `
                <div class="container-fluid text-start">
                    <div class="alert alert-info mb-3">
                        <strong>Estudiante:</strong> ${studentName}
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Descripcion de la incapacidad <span class="text-danger">*</span></label>
                        <textarea id="swalDescription" class="form-control" rows="3" placeholder="Describa la incapacidad medica..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Observacion</label>
                        <textarea id="swalObservation" class="form-control" rows="2" placeholder="Observaciones adicionales..."></textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold">Fecha de inicio <span class="text-danger">*</span></label>
                            <input type="date" id="swalStartDate" class="form-control" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold">Fecha de fin <span class="text-danger">*</span></label>
                            <input type="date" id="swalEndDate" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Comprobante de incapacidad</label>
                        <input type="file" id="swalFile" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                        <small class="text-muted">Formatos permitidos: JPG, PNG, PDF. Maximo 10MB.</small>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-save me-2"></i>Guardar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            width: '700px',
            preConfirm: () => {
                const description = document.getElementById('swalDescription').value.trim();
                const startDate = document.getElementById('swalStartDate').value;
                const endDate = document.getElementById('swalEndDate').value;

                if (!description) {
                    Swal.showValidationMessage('La descripcion de la incapacidad es obligatoria.');
                    return false;
                }
                if (!startDate) {
                    Swal.showValidationMessage('La fecha de inicio es obligatoria.');
                    return false;
                }
                if (!endDate) {
                    Swal.showValidationMessage('La fecha de fin es obligatoria.');
                    return false;
                }
                if (startDate > endDate) {
                    Swal.showValidationMessage('La fecha de inicio no puede ser mayor a la fecha de fin.');
                    return false;
                }

                const observation = document.getElementById('swalObservation').value.trim();
                const fileInput = document.getElementById('swalFile');

                const formData = new FormData();
                formData.append('student_id', studentId);
                formData.append('description', description);
                formData.append('observation', observation);
                formData.append('start_date', startDate);
                formData.append('end_date', endDate);

                if (fileInput.files.length > 0) {
                    formData.append('comprobante', fileInput.files[0]);
                }

                return formData;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Guardando incapacidad...',
                    text: 'Por favor espere',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                const formData = result.value;

                fetch('components/medicReports/saveReport.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Registrado!',
                                text: data.message,
                                confirmButtonText: 'Aceptar',
                                confirmButtonColor: '#198754'
                            }).then(() => {
                                document.getElementById('btnBuscar').click();
                            });
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        Swal.close();
                        Swal.fire('Error', 'Error en la solicitud: ' + error.message, 'error');
                    });
            }
        });
    });
</script>

<script>
    function editReport(reportId, startDate, endDate) {
        Swal.fire({
            title: '<i class="fas fa-edit me-2"></i>Editar fechas de incapacidad',
            html: `
                <div class="container-fluid text-start">
                    <div class="row mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold">Fecha de inicio <span class="text-danger">*</span></label>
                            <input type="date" id="swalEditStartDate" class="form-control" value="${startDate}" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold">Fecha de fin <span class="text-danger">*</span></label>
                            <input type="date" id="swalEditEndDate" class="form-control" value="${endDate}" required>
                        </div>
                    </div>
                    <div class="alert alert-warning small mb-0">
                        <i class="fas fa-info-circle me-1"></i>Solo se pueden modificar las fechas. El resto de la informacion permanece sin cambios.
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-save me-2"></i>Actualizar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#6c757d',
            width: '600px',
            preConfirm: () => {
                const sd = document.getElementById('swalEditStartDate').value;
                const ed = document.getElementById('swalEditEndDate').value;

                if (!sd) {
                    Swal.showValidationMessage('La fecha de inicio es obligatoria.');
                    return false;
                }
                if (!ed) {
                    Swal.showValidationMessage('La fecha de fin es obligatoria.');
                    return false;
                }
                if (sd > ed) {
                    Swal.showValidationMessage('La fecha de inicio no puede ser mayor a la fecha de fin.');
                    return false;
                }

                const formData = new FormData();
                formData.append('report_id', reportId);
                formData.append('start_date', sd);
                formData.append('end_date', ed);

                return formData;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Actualizando...',
                    text: 'Por favor espere',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch('components/medicReports/updateReport.php', {
                        method: 'POST',
                        body: result.value
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Actualizado!',
                                text: data.message,
                                confirmButtonText: 'Aceptar',
                                confirmButtonColor: '#198754'
                            }).then(() => {
                                document.getElementById('btnBuscar').click();
                            });
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        Swal.close();
                        Swal.fire('Error', 'Error en la solicitud: ' + error.message, 'error');
                    });
            }
        });
    }

    function deleteReport(reportId) {
        Swal.fire({
            title: 'Confirmar eliminacion',
            text: 'Esta accion no se puede deshacer. Se eliminara el registro y el archivo adjunto si existe.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-trash me-2"></i>Eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('report_id', reportId);

                Swal.fire({
                    title: 'Eliminando...',
                    text: 'Procesando solicitud',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch('components/medicReports/deleteReport.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        Swal.close();
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Eliminado!',
                                text: data.message,
                                confirmButtonText: 'Aceptar',
                                confirmButtonColor: '#198754'
                            }).then(() => {
                                document.getElementById('btnBuscar').click();
                            });
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        Swal.close();
                        Swal.fire('Error', 'Error en la solicitud: ' + error.message, 'error');
                    });
            }
        });
    }
</script>
