// map.js - Manejo del mapa con Leaflet

let map, marker;

function initializeMap() {
    // Verificar que el contenedor existe y está visible
    const mapContainer = document.getElementById('map');
    if (!mapContainer) {
        console.error('Contenedor del mapa no encontrado');
        return;
    }
    
    // Si el mapa ya existe, no reinicializar
    if (map) {
        console.log('Mapa ya inicializado');
        map.invalidateSize(); // Ajustar tamaño por si cambió el contenedor
        return;
    }
    
    try {
        const defaultCoords = [-12.046374, -77.042793]; // Lima, Perú
        
        map = L.map('map').setView(defaultCoords, 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19
        }).addTo(map);

        marker = L.marker(defaultCoords, { draggable: true }).addTo(map);
        
        // Actualizar coordenadas iniciales
        document.getElementById('lat').value = defaultCoords[0];
        document.getElementById('lng').value = defaultCoords[1];
        updateAddressFromCoords(defaultCoords[0], defaultCoords[1]);

        // Evento cuando se arrastra el marcador
        marker.on('dragend', function (e) {
            const latlng = e.target.getLatLng();
            document.getElementById('lat').value = latlng.lat;
            document.getElementById('lng').value = latlng.lng;
            updateAddressFromCoords(latlng.lat, latlng.lng);
        });
        
        // Permitir hacer clic en el mapa para mover el marcador
        map.on('click', function(e) {
            marker.setLatLng(e.latlng);
            document.getElementById('lat').value = e.latlng.lat;
            document.getElementById('lng').value = e.latlng.lng;
            updateAddressFromCoords(e.latlng.lat, e.latlng.lng);
        });

        console.log('✓ Mapa inicializado correctamente');
        
        // Intentar obtener ubicación del usuario
        getUserLocation();
        
    } catch (error) {
        console.error('Error al inicializar mapa:', error);
    }
}

function getUserLocation() {
    if (navigator.geolocation) {
        console.log('Solicitando ubicación del usuario...');
        
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const userCoords = [position.coords.latitude, position.coords.longitude];
                console.log('Ubicación del usuario obtenida:', userCoords);
                
                map.setView(userCoords, 16);
                marker.setLatLng(userCoords);
                document.getElementById('lat').value = userCoords[0];
                document.getElementById('lng').value = userCoords[1];
                updateAddressFromCoords(userCoords[0], userCoords[1]);
            }, 
            function(error) {
                console.log('Geolocalización fallida o denegada:', error.message);
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    }
}

async function updateAddressFromCoords(lat, lng) {
    const addressInput = document.getElementById('locationAddress');
    if (!addressInput) return;
    
    addressInput.value = "Buscando dirección...";
    
    try {
        const response = await fetch(
            `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18`,
            {
                headers: {
                    'User-Agent': 'PlataformaDenuncias/1.0'
                }
            }
        );
        
        const data = await response.json();
        
        if (data && data.display_name) {
            addressInput.value = data.display_name;
            console.log('Dirección encontrada:', data.display_name);
        } else {
            addressInput.value = `Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}`;
        }
    } catch (error) {
        console.error("Error en geocodificación inversa:", error);
        addressInput.value = `Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}`;
    }
}

function resetMap() {
    if (map && marker) {
        const defaultCoords = [-12.046374, -77.042793];
        map.setView(defaultCoords, 13);
        marker.setLatLng(defaultCoords);
        document.getElementById('lat').value = defaultCoords[0];
        document.getElementById('lng').value = defaultCoords[1];
        updateAddressFromCoords(defaultCoords[0], defaultCoords[1]);
        console.log('Mapa reseteado');
    }
}

// Exponer funciones globalmente
window.initializeMap = initializeMap;
window.resetMap = resetMap;

// Auto-inicializar cuando el tab de denuncia sea visible
document.addEventListener('DOMContentLoaded', () => {
    console.log('map.js cargado');
    
    // Observar cambios en el tab de denuncia
    const observer = new MutationObserver((mutations) => {
        const denunciaTab = document.getElementById('denuncia');
        if (denunciaTab && denunciaTab.style.display !== 'none' && !map) {
            console.log('Tab de denuncia visible, inicializando mapa...');
            setTimeout(initializeMap, 100);
        }
    });
    
    const denunciaTab = document.getElementById('denuncia');
    if (denunciaTab) {
        observer.observe(denunciaTab, {
            attributes: true,
            attributeFilter: ['style']
        });
        
        // Si ya está visible, inicializar inmediatamente
        if (denunciaTab.style.display !== 'none') {
            setTimeout(initializeMap, 100);
        }
    }
});