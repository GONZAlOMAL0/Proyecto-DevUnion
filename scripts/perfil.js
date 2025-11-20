let userData = {
    id: 0,
    nombre: 'Hitay Gonzalo',
    apellido: 'Malo Profumo',
    correo: 'gonzalomalo@gmail.com',
    telefonos: ['099 999 999'],
    cedula: '123456789',
    casa: { id: 1, nombre: 'Casa 1' },
    direccion: 'Neyra',
    estado_pago: "PENDIENTE"
};

const HORAS_REQUERIDAS = 21;
const TARIFA_POR_HORA = 15;

const API_BASE_URL = 'http://localhost/backend/api';
const TOKEN = localStorage.getItem('jwt');

let registros = [];

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    document.body.appendChild(notification);

    setTimeout(() => notification.classList.add('show'), 100);
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => document.body.removeChild(notification), 300);
    }, 3000);
}

function $id(id) { return document.getElementById(id); }

const formHoras = $id('horasForm');
const semanaSelect = $id('semana');
const horasInput = $id('horasTrabajadas');
const horasStatus = $id('horasStatus');
const alertaHoras = $id('alertaHoras');
const alertaTexto = $id('alertaTexto');
const seccionJustificacion = $id('seccionJustificacion');
const motivoTextarea = $id('motivo');
const accionRadios = () => document.querySelectorAll('input[name="accion"]');
const montoPagoDiv = $id('montoPago');
const montoTotal = $id('montoTotal');
const montoDetalle = $id('montoDetalle');
const submitBtn = $id('submitBtn');
const historialVacio = $id('historialVacio');
const tablaHistorial = $id('tablaHistorial');
const historialBody = $id('historialBody');

const editForm = $id('editForm');
const uploadArea = $id('uploadArea');
const fileInput = $id('fileInput');
const filePreview = $id('filePreview');
const aportesForm = $id('aportesForm');
const paymentsBody = $id('paymentsBody');
const periodoSelect = $id('periodo');
const sidebarToggle = $id('sidebarToggle');
const themeToggle = document.querySelector('.theme-toggle');

function guard(el, cb) { if (el) cb(el); }

function toggleTheme() {
    const body = document.body;
    if (!themeToggle) return;
    if (body.getAttribute('data-theme') === 'dark') {
        body.removeAttribute('data-theme');
        themeToggle.textContent = '🌙';
        localStorage.setItem('theme', 'light');
    } else {
        body.setAttribute('data-theme', 'dark');
        themeToggle.textContent = '☀️';
        localStorage.setItem('theme', 'dark');
    }
}
function loadTheme() {
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        document.body.setAttribute('data-theme', 'dark');
        if (themeToggle) themeToggle.textContent = '☀️';
    }
}
function logout() {
    if (confirm('¿Estás seguro de que quieres cerrar sesión?')) {
        localStorage.removeItem('jwt');
        showNotification('👋 Sesión cerrada correctamente', 'info');
        setTimeout(() => window.location.href = '/login.html', 800);
    }
}
function openEditModal() {
    if (!$id('editModal')) return;
    $id('editNombre').value = userData.nombre || '';
    $id('editApellido').value = userData.apellido || '';
    $id('editCorreo').value = userData.correo || '';
    $id('editDireccion').value = userData.direccion || '';
    $id('editModal').style.display = 'block';
}
function closeEditModal() {
    if ($id('editModal')) $id('editModal').style.display = 'none';
}

function updateUserInfo() {
    guard($id('nombre'), el => el.textContent = userData.nombre || "No hay informacion");
    guard($id('apellido'), el => el.textContent = userData.apellido || "No hay informacion");
    guard($id('correo'), el => el.textContent = userData.correo || "No hay informacion");
    guard($id('telefono'), el => el.textContent = (userData.telefonos || []).join(", ") || "No hay informacion");
    guard($id('cedula'), el => el.textContent = userData.cedula || "No hay informacion");
    guard($id('casa'), el => el.textContent = (userData.casa && userData.casa.nombre) ? userData.casa.nombre : "No hay informacion");
    guard($id('direccion'), el => el.textContent = userData.direccion || "No hay informacion");
    guard($id('userNameTop'), el => el.textContent = userData.nombre || '');
    guard($id('estadoPago'), el => el.textContent = userData.estado_pago || "No hay informacion");
    const estadoElm = $id('estado');
    if (estadoElm) estadoElm.setAttribute('data-estado', userData.estado_pago || "PENDIENTE");
}

if (uploadArea && fileInput && filePreview) {
    uploadArea.addEventListener('click', () => fileInput.click());
    uploadArea.addEventListener('dragover', (e) => { e.preventDefault(); uploadArea.classList.add('dragover'); });
    uploadArea.addEventListener('dragleave', () => uploadArea.classList.remove('dragover'));
    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        const files = e.dataTransfer.files;
        if (files.length > 0) handleFileSelect(files[0]);
    });
    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) handleFileSelect(e.target.files[0]);
    });
}
function handleFileSelect(file) {
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
    if (!allowedTypes.includes(file.type)) { showNotification('❌ Tipo de archivo no permitido', 'error'); return; }
    if (file.size > 5 * 1024 * 1024) { showNotification('❌ El archivo es demasiado grande (máx. 5MB)', 'error'); return; }
    if (!filePreview) return;
    filePreview.innerHTML = `
        <div style="background: #e8f5e8; padding: 15px; border-radius: 8px; border: 1px solid #28a745;">
            <strong>📎 Archivo seleccionado:</strong> ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)
        </div>`;
}

(function setupTabs() {
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    if (!tabBtns.length || !tabContents.length) return;

    function activateTab(btn) {
        tabBtns.forEach(b => b.classList.remove('active'));
        tabContents.forEach(c => c.classList.remove('active'));
        btn.classList.add('active');
        const id = btn.dataset.tab;
        const content = document.getElementById(id);
        if (content) content.classList.add('active');
    }

    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => activateTab(btn));
    });

    document.addEventListener('DOMContentLoaded', () => {
        const active = document.querySelector('.tab-btn.active') || tabBtns[0];
        if (active) activateTab(active);
    });
})();

if (aportesForm) {
    aportesForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        const semana = Number(periodoSelect ? periodoSelect.value : 0);
        const monto = Number($id('monto') ? $id('monto').value : 0);
        if (isNaN(monto) || monto <= 0 || monto > 1000000) {
            alert('El monto ingresado no es válido o excede el límite permitido.');
            return false;
        }
        const payload = { usuario_id: userData.id, semana, monto };
        try {
            showNotification('📤 Registrando aporte...', 'info');
            const response = await fetch(`${API_BASE_URL}/cooperativa/pagos.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + TOKEN },
                body: JSON.stringify(payload)
            });
            const result = await response.json();
            if (result.success) {
                if (paymentsBody) {
                    const newRow = paymentsBody.insertRow(0);
                    newRow.innerHTML = `
                        <td>${semana}</td>
                        <td>$${parseFloat(monto).toFixed(2)}</td>
                        <td>${new Date().toLocaleDateString('es-ES')}</td>
                        <td style="cursor: pointer;">📲</td>`;
                }
                
                aportesForm.reset();
                cargarSemanas();
                filePreview && (filePreview.innerHTML = '');
                showNotification('✅ Aporte registrado correctamente', 'success');
            } else {
                throw new Error(result.message || 'Error al registrar el aporte');
            }
        } catch (err) {
            console.error('Error:', err);
            showNotification('❌ Error al registrar el aporte', 'error');
        }
    });
}

async function cargarPagos() {
    try {
        const resp = await fetch(`${API_BASE_URL}/cooperativa/pagos.php`, {
            headers: { 'Authorization': `Bearer ${TOKEN}` }
        });
        const result = await resp.json();
        if (!result.success) throw new Error(result.message || 'Error al cargar los pagos');
        if (!paymentsBody) return;
        paymentsBody.innerHTML = '';
        result.data.forEach(pago => {
            const newRow = paymentsBody.insertRow();
            newRow.innerHTML = `
                <td>${pago.semana}</td>
                <td>$${parseFloat(pago.monto).toFixed(2)}</td>
                <td>${new Date(pago.fecha).toLocaleDateString('es-ES')}</td>
                <td style="cursor: pointer;">📲</td>`;
        });
    } catch (err) {
        console.error('Error cargando pagos:', err);
        showNotification('❌ Error al cargar los pagos', 'error');
    }
}

if (editForm) {
    editForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        const formData = {
            nombre: $id('editNombre') ? $id('editNombre').value : '',
            apellido: $id('editApellido') ? $id('editApellido').value : '',
            correo: $id('editCorreo') ? $id('editCorreo').value : '',
            direccion: $id('editDireccion') ? $id('editDireccion').value : ''
        };
        try {
            const request = await fetch(`${API_BASE_URL}/perfil.php`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + TOKEN },
                body: JSON.stringify(formData)
            });
            const response = await request.json();
            if (response.success) {
                userData = response.data;
                updateUserInfo();
                closeEditModal();
                showNotification('✅ Información actualizada', 'success');
            } else {
                throw new Error(response.message || 'Error al actualizar');
            }
        } catch (err) {
            console.error('Error:', err);
            showNotification('❌ Error al actualizar la información del socio', 'error');
        }
    });
}

function generarSemanas() {
    const semanas = [];
    const fechaActual = new Date();
    for (let i = -2; i <= 4; i++) {
        const fecha = new Date(fechaActual);
        fecha.setDate(fecha.getDate() + (i * 7));
        const año = fecha.getFullYear();
        const numeroSemana = getWeekNumber(fecha);
        const inicioSemana = new Date(fecha);
        inicioSemana.setDate(fecha.getDate() - fecha.getDay() + 1);
        const finSemana = new Date(inicioSemana);
        finSemana.setDate(inicioSemana.getDate() + 6);
        const formatoFecha = (date) => `${date.getDate().toString().padStart(2, '0')}/${(date.getMonth() + 1).toString().padStart(2, '0')}`;
        semanas.push({
            value: `${año}-W${numeroSemana}`,
            label: `Semana ${numeroSemana} (${formatoFecha(inicioSemana)} - ${formatoFecha(finSemana)})`
        });
    }
    return semanas;
}
function getWeekNumber(date) {
    const d = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()));
    const dayNum = d.getUTCDay() || 7;
    d.setUTCDate(d.getUTCDate() + 4 - dayNum);
    const yearStart = new Date(Date.UTC(d.getUTCFullYear(), 0, 1));
    return Math.ceil(((d.getTime() - yearStart.getTime()) / 86400000 + 1) / 7);
}
function poblarSemanas() {
    if (!semanaSelect) return;
    const semanas = generarSemanas();
    semanaSelect.innerHTML = '<option value="">Seleccionar semana</option>';
    semanas.forEach(sem => {
        const option = document.createElement('option');
        option.value = sem.value;
        option.textContent = sem.label;
        semanaSelect.appendChild(option);
    });
}

function actualizarEstadoHoras() {
    if (!horasInput) return;
    const horasTrabajadas = parseInt(horasInput.value) || 0;
    const horasFaltantes = Math.max(0, HORAS_REQUERIDAS - horasTrabajadas);
    const montoPago = horasFaltantes * TARIFA_POR_HORA;

    if (horasInput.value) {
        horasStatus && horasStatus.classList.remove('hidden');
        if (horasFaltantes > 0) {
            horasStatus && (horasStatus.innerHTML = `<span class="text-red font-medium">Te faltan ${horasFaltantes} horas</span>`);
            alertaHoras && alertaHoras.classList.remove('hidden');
            alertaTexto && (alertaTexto.textContent = `Te faltan ${horasFaltantes} horas. Debes registrar un motivo y seleccionar una acción.`);
            seccionJustificacion && seccionJustificacion.classList.remove('hidden');
            if (motivoTextarea) motivoTextarea.required = true;
        } else {
            horasStatus && (horasStatus.innerHTML = `<span class="text-green font-medium">¡Horas completas!</span>`);
            alertaHoras && alertaHoras.classList.add('hidden');
            seccionJustificacion && seccionJustificacion.classList.add('hidden');
            if (motivoTextarea) motivoTextarea.required = false;
        }
    } else {
        horasStatus && horasStatus.classList.add('hidden');
        alertaHoras && alertaHoras.classList.add('hidden');
        seccionJustificacion && seccionJustificacion.classList.add('hidden');
    }

    if (montoTotal) montoTotal.textContent = `$${montoPago}`;
    if (montoDetalle) montoDetalle.textContent = `${horasFaltantes} horas × $${TARIFA_POR_HORA}/hora = $${montoPago}`;
}

function manejarCambioAccion() {
    const accion = document.querySelector('input[name="accion"]:checked');
    if (accion && accion.value === 'pago') {
        montoPagoDiv && montoPagoDiv.classList.remove('hidden');
    } else {
        montoPagoDiv && montoPagoDiv.classList.add('hidden');
    }
}

function crearBadgeEstado(estado) {
    const badge = document.createElement('span');
    badge.className = 'badge';
    switch (estado) {
        case 'completo': badge.className += ' badge-green'; badge.innerHTML = '✓ Completo'; break;
        case 'pendiente': badge.className += ' badge-yellow'; badge.innerHTML = '⚠ Pendiente'; break;
        case 'exonerado': badge.className += ' badge-blue'; badge.innerHTML = '✕ Exonerado'; break;
        default: badge.className += ' badge-gray'; badge.textContent = estado;
    }
    return badge;
}

function actualizarHistorial() {
    if (!historialVacio || !tablaHistorial || !historialBody) {
        console.warn('Elementos de historial no encontrados en el DOM.');
        return;
    }

    if (!Array.isArray(registros) || registros.length === 0) {
        historialVacio.classList.remove('hidden');
        tablaHistorial.classList.add('hidden');
        historialBody.innerHTML = '';
        return;
    }

    historialVacio.classList.add('hidden');
    tablaHistorial.classList.remove('hidden');
    historialBody.innerHTML = '';

    registros.forEach(registro => {
        const row = document.createElement('tr');
        row.className = 'hover:bg-gray-50';

        const faltantes = Math.max(0, HORAS_REQUERIDAS - (registro.horas_registradas || 0));
        const accionTexto = registro.tipo_compensacion === 'exoneracion' ? 'Exoneración'
            : registro.tipo_compensacion === 'pago_compensatorio' ? 'Pago compensatorio'
                : '-';

        row.innerHTML = `
            <td class="py-3 px-2 text-gray-dark">${registro.semana}</td>
            <td class="py-3 px-2 font-medium text-gray-dark">${registro.horas_registradas || 0}h</td>
            <td class="py-3 px-2">${faltantes > 0 ? `<span class="text-red font-medium">${faltantes}h</span>` : `<span class="text-green font-medium">0h</span>`}</td>
            <td class="py-3 px-2 text-gray-dark">${registro.motivo_inasistencia || '-'}<br><small>${accionTexto}</small></td>
            <td class="py-3 px-2 estado-cell"></td>
        `;

        const estadoCell = row.querySelector('.estado-cell');
        if (estadoCell) estadoCell.appendChild(crearBadgeEstado(registro.estado || 'pendiente'));
        historialBody.appendChild(row);
    });
}

async function cargarHistorial() {
    if (!userData || !userData.id) {
        registros = [];
        actualizarHistorial();
        return;
    }
    try {
        const resp = await fetch(`${API_BASE_URL}/cooperativa/horarios.php?socio_id=${userData.id}`, {
            headers: { 'Authorization': `Bearer ${TOKEN}` }
        });
        const result = await resp.json();
        if (!result.success) throw new Error(result.message || 'Error al cargar historial');
        registros = Array.isArray(result.data) ? result.data : [];
        actualizarHistorial();
    } catch (err) {
        console.error('Error cargando historial:', err);
        showNotification('❌ Error al cargar historial de horas', 'error');
    }
}

async function manejarEnvioHoras(e) {
    if (e) e.preventDefault();
    if (!formHoras) return;

    const horasTrabajadas = parseInt(horasInput ? horasInput.value : 0) || 0;
    const horasFaltantes = Math.max(0, HORAS_REQUERIDAS - horasTrabajadas);
    const accionSelecionada = document.querySelector('input[name="accion"]:checked');

    if (!semanaSelect || !semanaSelect.value) { alert('Seleccioná la semana'); return; }
    if (!horasInput || isNaN(horasTrabajadas)) { alert('Ingresá horas válidas'); return; }

    if (horasFaltantes > 0) {
        if (!motivoTextarea || !motivoTextarea.value.trim()) { alert('Ingresá motivo'); return; }
        if (!accionSelecionada) { alert('Seleccioná una acción'); return; }
        if (accionSelecionada.value === 'pago') {
            
        }
    }

    const payload = {
        socio_id: userData.id,
        semana: semanaSelect.value,
        horas_registradas: horasTrabajadas,
        motivo_inasistencia: horasFaltantes > 0 ? (motivoTextarea.value || null) : null,
        tipo_compensacion: horasFaltantes > 0 ? (accionSelecionada.value === 'pago' ? 'pago_compensatorio' : 'exoneracion') : 'ninguna',
        pago_compensatorio_id: (accionSelecionada && accionSelecionada.value === 'pago') ? 1 : null
    };

    submitBtn && (submitBtn.disabled = true);
    if (submitBtn) submitBtn.innerHTML = `<div class="spinner mr-2"></div> Guardando...`;

    try {
        const resp = await fetch(`${API_BASE_URL}/cooperativa/horarios.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${TOKEN}` },
            body: JSON.stringify(payload)
        });
        const result = await resp.json();
        if (result.success) {
            showNotification('✅ Registro guardado correctamente', 'success');
            await cargarHistorial();
            formHoras.reset();
            actualizarEstadoHoras();
            manejarCambioAccion();
        } else {
            showNotification('❌ ' + (result.message || 'Error al guardar'), 'error');
        }
    } catch (err) {
        console.error('Error al guardar:', err);
        showNotification('❌ Error al guardar registro', 'error');
    } finally {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = `
                <svg class="icon mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3-3m0 0l-3 3m3-3v12"></path>
                </svg>
                Guardar Registro`;
        }
    }
}

function getUserInfo() {
    fetch(`${API_BASE_URL}/perfil.php`, { headers: { 'Authorization': `Bearer ${TOKEN}` } })
        .then(res => res.json())
        .then(response => {
            if (response.success) {
                userData = response.data;
                updateUserInfo();
                cargarHistorial();
            } else {
                alert(response.message || 'No autorizado');
                localStorage.removeItem('jwt');
                window.location.href = 'login.html';
            }
        })
        .catch(err => {
            console.error(err);
            window.location.href = 'login.html';
        });
}

function cargarSemanas() {
    if (!TOKEN) return;
    fetch(`${API_BASE_URL}/cooperativa/pagos.php?action=semanas`, {
        headers: { 'Authorization': `Bearer ${TOKEN}` }
    })
        .then(res => res.json())
        .then(response => {
            if (!response.success) { console.warn('No se obtuvieron semanas del backend'); return; }
            if (!periodoSelect) return;
            periodoSelect.innerHTML = '';
            const { semanaActual, semanasPagas } = response.data;
            for (let i = semanaActual; i >= 1; i--) {
                const option = document.createElement('option');
                option.value = i;
                option.textContent = `Semana ${i}`;
                if (Array.isArray(semanasPagas) && semanasPagas.includes(i)) {
                    option.disabled = true;
                    option.textContent += " (Paga)";
                }
                periodoSelect.appendChild(option);
            }
        })
        .catch(err => { console.error("Error cargando semanas:", err); });
}

document.addEventListener('DOMContentLoaded', function () {
    if (!TOKEN) {
        window.location.href = 'login.html';
        return;
    }
    loadTheme();
    updateUserInfo();
    poblarSemanas();
    cargarPagos();
    getUserInfo();
    cargarSemanas();

    horasInput && horasInput.addEventListener('input', actualizarEstadoHoras);
    document.addEventListener('change', function (e) {
        if (e.target && e.target.name === 'accion') manejarCambioAccion();
    });
    formHoras && formHoras.addEventListener('submit', manejarEnvioHoras);

    if (sidebarToggle) sidebarToggle.addEventListener('click', () => {
        const sidebar = document.querySelector('.sidebar');
        if (sidebar) sidebar.classList.toggle('collapsed');
    });

    showNotification('👋 ¡Bienvenido/a al portal de la cooperativa!', 'success');
});
