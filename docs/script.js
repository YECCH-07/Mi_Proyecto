let isMapInitialized = false;

document.addEventListener('DOMContentLoaded', function() {
    showTab('login');
    setupEventListeners();
});

function setupEventListeners() {
    // Tab switching
    document.querySelectorAll('.nav-link').forEach(link => {
        if (!link) return;
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const tabName = e.target.getAttribute('href').substring(1);
            showTab(tabName);
        });
    });

    // Form submissions
    const loginForm = document.getElementById('loginForm');
    if (loginForm) loginForm.addEventListener('submit', loginUsuario);

    const registroForm = document.getElementById('registroForm');
    if (registroForm) registroForm.addEventListener('submit', registrarUsuario);

    const denunciaForm = document.getElementById('denunciaForm');
    if (denunciaForm) denunciaForm.addEventListener('submit', registrarDenuncia);

    // File preview
    const fileInput = document.getElementById('evidenceFiles');
    if (fileInput) {
        fileInput.addEventListener('change', () => {
            const previewContainer = document.getElementById('file-preview');
            if (!previewContainer) return;
            previewContainer.innerHTML = '';
            const files = fileInput.files;
            if (files.length > 5) {
                alert('No puede seleccionar más de 5 archivos.');
                fileInput.value = '';
                return;
            }
            for (const file of files) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.createElement('img');
                    preview.src = e.target.result;
                    preview.style.height = '60px';
                    preview.style.margin = '5px';
                    preview.style.borderRadius = '5px';
                    previewContainer.appendChild(preview);
                }
                if (file.type.startsWith('image/')) {
                    reader.readAsDataURL(file);
                } else {
                    const fileIcon = document.createElement('div');
                    fileIcon.textContent = `📄 ${file.name}`;
                    fileIcon.style.margin = '5px';
                    previewContainer.appendChild(fileIcon);
                }
            }
        });
    }
}

function showTab(tabName) {
    document.querySelectorAll('.tab-content').forEach(tab => {
        if (tab) tab.style.display = 'none';
    });
    document.querySelectorAll('.nav-link').forEach(link => {
        if (link) link.classList.remove('active');
    });
    
    const selectedTab = document.getElementById(tabName);
    if (selectedTab) selectedTab.style.display = 'block';

    const activeLink = document.querySelector(`.nav-link[href*="${tabName}"]`);
    if (activeLink) activeLink.classList.add('active');

    if (tabName === 'denuncia') {
        if (!isMapInitialized) {
            initializeMap();
            isMapInitialized = true;
        } else {
            // Ensure map is visible and sized correctly after tab switch
            setTimeout(() => {
                if (map) map.invalidateSize();
            }, 10);
        }
    }
}

async function registrarDenuncia(e) {
    e.preventDefault();
    const form = document.getElementById('denunciaForm');
    const formData = new FormData(form);

    formData.set('lat', document.getElementById('lat').value);
    formData.set('lng', document.getElementById('lng').value);

    const token = localStorage.getItem('userToken');
    if (token) {
        try {
            const userId = JSON.parse(atob(token.split('.')[1])).id;
            formData.append('userId', userId);
        } catch (error) {
            console.error('Error decoding token:', error);
        }
    }

    try {
        const response = await fetch('http://localhost:3000/api/denuncias', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        if (response.ok) {
            alert(`Denuncia registrada con éxito.\nSu código de seguimiento es: ${result.trackingId}`);
            form.reset();
            document.getElementById('file-preview').innerHTML = '';
            showTab('seguimiento');
        } else {
            alert('Error: ' + result.error);
        }
    } catch (error) {
        console.error('Report submission failed:', error);
        alert('No se pudo conectar con el servidor. Inténtelo más tarde.');
    }
}

async function registrarUsuario(e) {
    e.preventDefault();
    const form = document.getElementById('registroForm');
    const payload = {
        dni: form.dni.value,
        first_name: form.firstName.value,
        last_name: form.lastName.value,
        email: form.email.value,
        phone: form.phone.value,
        address: form.address.value,
        password: form.password.value
    };

    try {
        const response = await fetch('http://localhost:3000/api/users/citizens', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const result = await response.json();
        if (response.ok) {
            alert(result.message);
            form.reset();
            showTab('login');
        } else {
            alert('Error: ' + result.error);
        }
    } catch (error) {
        console.error('Registration failed:', error);
        alert('No se pudo conectar con el servidor.');
    }
}

async function loginUsuario(e) {
    e.preventDefault();
    const form = document.getElementById('loginForm');
    const payload = {
        email: form.loginEmail.value,
        password: form.loginPassword.value
    };

    try {
        const response = await fetch('http://localhost:3000/api/auth/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const result = await response.json();
        if (response.ok) {
            localStorage.setItem('userToken', result.token);
            alert(result.message);
            showTab('denuncia');
        } else {
            alert('Error: ' + result.error);
        }
    } catch (error) {
        console.error('Login failed:', error);
        alert('No se pudo conectar con el servidor.');
    }
}