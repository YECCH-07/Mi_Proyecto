// main.js - Sistema de denuncias ciudadanas

const API_URL = 'http://localhost:3000/api';
let currentUser = null;

// ==============================================
// INICIALIZACIÓN
// ==============================================
document.addEventListener('DOMContentLoaded', () => {
    console.log('Sistema de denuncias iniciado');
    
    checkServerConnection();
    loadUserSession();
    setupNavigation();
    setupForms();
    
    // Mostrar tab inicial
    showTab('login');
});

// ==============================================
// NAVEGACIÓN ENTRE TABS
// ==============================================
function setupNavigation() {
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const href = link.getAttribute('href');
            const tabId = href.replace('#', '');
            showTab(tabId);
        });
    });
}

function showTab(tabId) {
    // Ocultar todos
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.style.display = 'none';
    });
    
    // Mostrar seleccionado
    const selectedTab = document.getElementById(tabId);
    if (selectedTab) {
        selectedTab.style.display = 'block';
        
        // Si es el tab de denuncia, inicializar mapa
        if (tabId === 'denuncia' && window.initializeMap) {
            setTimeout(() => window.initializeMap(), 100);
        }
    }
    
    // Actualizar nav activo
    document.querySelectorAll('.nav-link').forEach(link => {
        link.classList.remove('active');
    });
    const activeLink = document.querySelector(`a[href="#${tabId}"]`);
    if (activeLink) activeLink.classList.add('active');
    
    console.log('Tab activo:', tabId);
}

// ==============================================
// SESIÓN DE USUARIO
// ==============================================
function loadUserSession() {
    const user = localStorage.getItem('currentUser');
    if (user) {
        currentUser = JSON.parse(user);
        console.log('Usuario en sesión:', currentUser);
        updateNavbarForLoggedUser();
    }
}

function saveUserSession(user) {
    currentUser = user;
    localStorage.setItem('currentUser', JSON.stringify(user));
    updateNavbarForLoggedUser();
}

function clearUserSession() {
    currentUser = null;
    localStorage.removeItem('currentUser');
    location.reload();
}

function updateNavbarForLoggedUser() {
    if (!currentUser) return;
    
    const navbar = document.querySelector('.navbar-nav');
    navbar.innerHTML = `
        <li class="nav-item">
            <span class="nav-link">Hola, ${currentUser.first_name}</span>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#denuncia">Nueva Denuncia</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#seguimiento">Seguimiento</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#" onclick="clearUserSession()">Cerrar Sesión</a>
        </li>
    `;
    setupNavigation();
}

// ==============================================
// CONFIGURAR FORMULARIOS
// ==============================================
function setupForms() {
    setupLoginForm();
    setupRegistroForm();
    setupDenunciaForm();
    setupSeguimientoForm();
}

// LOGIN
function setupLoginForm() {
    const form = document.getElementById('loginForm');
    if (!form) return;
    
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const email = document.getElementById('loginEmail').value;
        const password = document.getElementById('loginPassword').value;
        const btn = form.querySelector('button[type="submit"]');
        
        btn.disabled = true;
        btn.textContent = 'Iniciando sesión...';
        
        try {
            const response = await fetch(`${API_URL}/auth/login`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, password })
            });
            
            const result = await response.json();
            
            if (response.ok && result.success) {
                alert('¡Inicio de sesión exitoso!');
                saveUserSession(result.user);
                showTab('denuncia');
                form.reset();
            } else {
                alert(result.error || 'Error al iniciar sesión');
            }
        } catch (error) {
            console.error('Error login:', error);
            alert('Error de conexión: ' + error.message);
        } finally {
            btn.disabled = false;
            btn.textContent = 'Ingresar';
        }
    });
}

// REGISTRO
function setupRegistroForm() {
    const form = document.getElementById('registroForm');
    if (!form) return;
    
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = {
            dni: document.getElementById('dni').value,
            first_name: document.getElementById('firstName').value,
            last_name: document.getElementById('lastName').value,
            email: document.getElementById('email').value,
            phone: document.getElementById('phone').value,
            address: document.getElementById('address').value,
            password: document.getElementById('password').value,
            user_type: 'citizen'
        };
        
        const btn = form.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.textContent = 'Registrando...';
        
        try {
            const response = await fetch(`${API_URL}/auth/register`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });
            
            const result = await response.json();
            
            if (response.ok && result.success) {
                alert('¡Registro exitoso! Ahora puedes iniciar sesión.');
                showTab('login');
                form.reset();
            } else {
                alert(result.error || 'Error al registrarse');
            }
        } catch (error) {
            console.error('Error registro:', error);
            alert('Error de conexión: ' + error.message);
        } finally {
            btn.disabled = false;
            btn.textContent = 'Registrarse';
        }
    });
}

// DENUNCIA
function setupDenunciaForm() {
    const form = document.getElementById('denunciaForm');
    if (!form) return;
    
    // Preview de archivos
    const fileInput = document.getElementById('evidenceFiles');
    if (fileInput) {
        fileInput.addEventListener('change', handleFilePreview);
    }
    
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        // Validar ubicación
        const lat = document.getElementById('lat').value;
        const lng = document.getElementById('lng').value;
        
        if (!lat || !lng) {
            alert('⚠️ Por favor, marca la ubicación del problema en el mapa');
            return;
        }
        
        // Crear FormData
        const formData = new FormData();
        
        // Obtener valores del formulario
        const categorySelect = document.getElementById('category');
        const selectedOption = categorySelect.options[categorySelect.selectedIndex];
        
        formData.append('title', selectedOption.text); // Texto completo de la opción
        formData.append('category', selectedOption.value); // Valor de la categoría
        formData.append('description', document.getElementById('description').value);
        formData.append('lat', lat);
        formData.append('lng', lng);
        formData.append('address', document.getElementById('locationAddress').value);
        
        // Agregar userId si está logueado
        if (currentUser) {
            formData.append('userId', currentUser.user_id);
        }
        
        // Agregar archivos
        const files = fileInput.files;
        if (files.length > 5) {
            alert('Máximo 5 archivos permitidos');
            return;
        }
        
        for (let i = 0; i < files.length; i++) {
            formData.append('evidenceFiles', files[i]);
        }
        
        console.log('Enviando denuncia...');
        console.log('Título:', selectedOption.text);
        console.log('Categoría:', selectedOption.value);
        console.log('Ubicación:', lat, lng);
        console.log('Archivos:', files.length);
        
        const btn = form.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.textContent = 'Enviando denuncia...';
        
        try {
            const response = await fetch(`${API_URL}/denuncias`, {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            console.log('Respuesta del servidor:', result);
            
            if (response.ok && result.success) {
                alert(`✅ ¡Denuncia creada exitosamente!\n\n` +
                      `Número de seguimiento:\n${result.trackingId}\n\n` +
                      `Guarda este código para consultar el estado de tu denuncia.`);
                
                // Limpiar formulario
                form.reset();
                document.getElementById('file-preview').innerHTML = '';
                
                // Resetear mapa
                if (window.resetMap) {
                    window.resetMap();
                }
                
                // Ir a seguimiento
                showTab('seguimiento');
                document.getElementById('trackingIdInput').value = result.trackingId;
            } else {
                alert('❌ Error: ' + (result.error || 'No se pudo crear la denuncia'));
            }
        } catch (error) {
            console.error('Error al enviar denuncia:', error);
            alert('❌ Error de conexión: ' + error.message);
        } finally {
            btn.disabled = false;
            btn.textContent = 'Enviar Denuncia';
        }
    });
}

// Preview de archivos
function handleFilePreview(event) {
    const files = event.target.files;
    const preview = document.getElementById('file-preview');
    
    if (!preview) return;
    
    preview.innerHTML = '';
    
    if (files.length > 5) {
        alert('Máximo 5 archivos permitidos');
        event.target.value = '';
        return;
    }
    
    Array.from(files).forEach((file, index) => {
        const reader = new FileReader();
        
        reader.onload = (e) => {
            const div = document.createElement('div');
            div.className = 'd-inline-block m-2';
            
            if (file.type.startsWith('image/')) {
                div.innerHTML = `
                    <img src="${e.target.result}" 
                         alt="Preview ${index + 1}" 
                         style="max-width: 100px; max-height: 100px; border-radius: 4px; border: 1px solid #ddd;">
                    <p class="text-center small mb-0">${file.name}</p>
                `;
            } else {
                div.innerHTML = `
                    <div style="width: 100px; height: 100px; border: 1px solid #ddd; display: flex; align-items: center; justify-content: center; border-radius: 4px; background: #f8f9fa;">
                        <span style="font-size: 2rem;">📄</span>
                    </div>
                    <p class="text-center small mb-0">${file.name}</p>
                `;
            }
            
            preview.appendChild(div);
        };
        
        if (file.type.startsWith('image/')) {
            reader.readAsDataURL(file);
        } else {
            // Para PDFs, solo mostrar icono
            const div = document.createElement('div');
            div.className = 'd-inline-block m-2';
            div.innerHTML = `
                <div style="width: 100px; height: 100px; border: 1px solid #ddd; display: flex; align-items: center; justify-content: center; border-radius: 4px; background: #f8f9fa;">
                    <span style="font-size: 2rem;">📄</span>
                </div>
                <p class="text-center small mb-0">${file.name}</p>
            `;
            preview.appendChild(div);
        }
    });
}

// SEGUIMIENTO
function setupSeguimientoForm() {
    const btn = document.getElementById('searchReportBtn');
    if (!btn) return;
    
    btn.addEventListener('click', async () => {
        const trackingId = document.getElementById('trackingIdInput').value.trim();
        
        if (!trackingId) {
            alert('Por favor, ingresa un código de seguimiento');
            return;
        }
        
        console.log('Buscando denuncia:', trackingId);
        
        btn.disabled = true;
        btn.textContent = 'Buscando...';
        
        try {
            const response = await fetch(`${API_URL}/denuncias`);
            const result = await response.json();
            
            if (response.ok && result.success) {
                const report = result.reports.find(r => r.tracking_id === trackingId);
                
                if (report) {
                    displayReportStatus(report);
                } else {
                    document.getElementById('reportStatusResult').innerHTML = `
                        <div class="alert alert-warning">
                            <strong>⚠️ No encontrado</strong><br>
                            No se encontró ninguna denuncia con el código: <strong>${trackingId}</strong>
                        </div>
                    `;
                }
            } else {
                throw new Error('Error al buscar la denuncia');
            }
        } catch (error) {
            console.error('Error búsqueda:', error);
            document.getElementById('reportStatusResult').innerHTML = `
                <div class="alert alert-danger">
                    <strong>❌ Error</strong><br>
                    ${error.message}
                </div>
            `;
        } finally {
            btn.disabled = false;
            btn.textContent = 'Buscar';
        }
    });
}

function displayReportStatus(report) {
    const statusConfig = {
        'received': { color: 'warning', icon: '🔴', text: 'Recibida' },
        'in_progress': { color: 'info', icon: '🟡', text: 'En Proceso' },
        'resolved': { color: 'success', icon: '🟢', text: 'Resuelta' },
        'rejected': { color: 'danger', icon: '⚫', text: 'Rechazada' }
    };
    
    const config = statusConfig[report.status] || { color: 'secondary', icon: '⚪', text: 'Desconocido' };
    
    const html = `
        <div class="card mt-3">
            <div class="card-header bg-${config.color} text-white">
                <h5 class="mb-0">${config.icon} Denuncia ${report.tracking_id}</h5>
            </div>
            <div class="card-body">
                <h6 class="card-title">${report.title}</h6>
                <table class="table table-sm">
                    <tr>
                        <th>Categoría:</th>
                        <td>${report.category || 'No especificada'}</td>
                    </tr>
                    <tr>
                        <th>Fecha de creación:</th>
                        <td>${new Date(report.created_at).toLocaleString('es-PE', {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        })}</td>
                    </tr>
                    <tr>
                        <th>Estado actual:</th>
                        <td>
                            <span class="badge bg-${config.color}">${config.icon} ${config.text}</span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    `;
    
    document.getElementById('reportStatusResult').innerHTML = html;
}

// ==============================================
// UTILIDADES
// ==============================================
async function checkServerConnection() {
    try {
        const response = await fetch(`${API_URL}/health`);
        const data = await response.json();
        console.log('✓ Servidor conectado:', data);
        return true;
    } catch (error) {
        console.error('✗ Error de conexión con servidor:', error);
        
        if (!sessionStorage.getItem('serverErrorShown')) {
            alert('⚠️ No se puede conectar con el servidor.\n\n' +
                  'Asegúrate de que esté corriendo:\n' +
                  'cd backend\n' +
                  'node src/index.js');
            sessionStorage.setItem('serverErrorShown', 'true');
        }
        return false;
    }
}

// Exponer funciones globales
window.clearUserSession = clearUserSession;
window.showTab = showTab;