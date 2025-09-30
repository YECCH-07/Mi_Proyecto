document.addEventListener('DOMContentLoaded', function() {
    showTab('login');
    initializeMap();
    setupEventListeners();
});

function setupEventListeners() {
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const tabName = e.target.getAttribute('href').substring(1);
            showTab(tabName);
        });
    });

    document.getElementById('registroForm').addEventListener('submit', registrarUsuario);
    document.getElementById('loginForm').addEventListener('submit', loginUsuario);
    document.getElementById('denunciaForm').addEventListener('submit', registrarDenuncia);

    const fileInput = document.getElementById('evidenceFiles');
    fileInput.addEventListener('change', () => {
        const previewContainer = document.getElementById('file-preview');
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

function showTab(tabName) {
    document.querySelectorAll('.tab-content').forEach(tab => tab.style.display = 'none');
    document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
    
    const selectedTab = document.getElementById(tabName);
    if (selectedTab) selectedTab.style.display = 'block';

    const activeLink = document.querySelector(`.nav-link[href*="${tabName}"]`);
    if (activeLink) activeLink.classList.add('active');

    if (tabName === 'denuncia' && typeof map !== 'undefined') {
        setTimeout(() => map.invalidateSize(), 10);
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
        const userId = JSON.parse(atob(token.split('.')[1])).id;
        formData.append('userId', userId);
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
    const data = new FormData(form);
    const payload = Object.fromEntries(data.entries());
    
    // Remap names to match backend
    payload.first_name = payload.firstName;
    payload.last_name = payload.lastName;
    delete payload.firstName;
    delete payload.lastName;

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
    const data = new FormData(form);
    const payload = Object.fromEntries(data.entries());

    try {
        const response = await fetch('http://localhost:3000/api/auth/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email: payload.loginEmail, password: payload.loginPassword })
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
