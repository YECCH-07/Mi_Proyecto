document.addEventListener('DOMContentLoaded', function() {
    // Show the login tab by default
    showTab('login');
    initializeMap();
    setupEventListeners();
});

let map, marker;

function initializeMap() {
    // Default view if geolocation fails or is denied
    const defaultCoords = [-12.046374, -77.042793]; // Lima, Peru

    map = L.map('map').setView(defaultCoords, 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    marker = L.marker(defaultCoords, { draggable: true }).addTo(map);

    // --- Event Listeners for Map ---
    // Update hidden fields on marker drag
    marker.on('dragend', function (e) {
        const latlng = e.target.getLatLng();
        document.getElementById('lat').value = latlng.lat;
        document.getElementById('lng').value = latlng.lng;
    });

    // Get user's location
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const userCoords = [position.coords.latitude, position.coords.longitude];
            map.setView(userCoords, 16);
            marker.setLatLng(userCoords);
            // Update hidden fields immediately
            document.getElementById('lat').value = userCoords[0];
            document.getElementById('lng').value = userCoords[1];
        }, function() {
            console.log('Geolocation failed or was denied.');
        });
    }
}

function setupEventListeners() {
    // Tab switching
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const tabName = e.target.getAttribute('href').substring(1);
            showTab(tabName);
        });
    });

    // Form submissions
    document.getElementById('registroForm').addEventListener('submit', registrarUsuario);
    document.getElementById('loginForm').addEventListener('submit', loginUsuario);
    document.getElementById('denunciaForm').addEventListener('submit', registrarDenuncia);

    // File preview
    const fileInput = document.getElementById('evidenceFiles');
    fileInput.addEventListener('change', () => {
        const previewContainer = document.getElementById('file-preview');
        previewContainer.innerHTML = ''; // Clear previous previews
        const files = fileInput.files;
        if (files.length > 5) {
            alert('No puede seleccionar más de 5 archivos.');
            fileInput.value = ''; // Clear the selection
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
                // For non-image files like PDF
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

    // Refresh map size if the denuncia tab is shown
    if (tabName === 'denuncia') {
        setTimeout(() => map.invalidateSize(), 10);
    }
}

// --- FORM HANDLERS ---

async function registrarDenuncia(e) {
    e.preventDefault();
    const form = document.getElementById('denunciaForm');
    const formData = new FormData(form);

    // Ensure lat/lng are set from the marker
    formData.set('lat', document.getElementById('lat').value);
    formData.set('lng', document.getElementById('lng').value);

    // Add user ID if logged in (from localStorage)
    const token = localStorage.getItem('userToken');
    if (token) {
        const userId = JSON.parse(atob(token.split('.')[1])).id;
        formData.append('userId', userId);
    }

    try {
        const response = await fetch('http://localhost:3000/api/denuncias', {
            method: 'POST',
            body: formData // No Content-Type header needed, browser sets it for FormData
        });

        const result = await response.json();

        if (response.ok) {
            alert(`Denuncia registrada con éxito.\nSu código de seguimiento es: ${result.trackingId}`);
            form.reset();
            document.getElementById('file-preview').innerHTML = '';
            showTab('seguimiento'); // Or a success message tab
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
    // ... (existing registration logic)
}

async function loginUsuario(e) {
    e.preventDefault();
    // ... (existing login logic, but now we store the token)
    const email = document.getElementById('loginEmail').value;
    const password = document.getElementById('loginPassword').value;

    try {
        const response = await fetch('http://localhost:3000/api/auth/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, password })
        });

        const result = await response.json();

        if (response.ok) {
            localStorage.setItem('userToken', result.token);
            alert(result.message);
            showTab('denuncia'); // Switch to new report tab after login
        } else {
            alert('Error: ' + result.error);
        }
    } catch (error) {
        console.error('Login failed:', error);
        alert('No se pudo conectar con el servidor. Inténtelo más tarde.');
    }
}
