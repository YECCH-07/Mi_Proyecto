function initializeMap() {
    const defaultCoords = [-12.046374, -77.042793]; // Lima, Peru
    map = L.map('map').setView(defaultCoords, 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    marker = L.marker(defaultCoords, { draggable: true }).addTo(map);
    updateAddressFromCoords(defaultCoords[0], defaultCoords[1]); // Initial address lookup

    marker.on('dragend', function (e) {
        const latlng = e.target.getLatLng();
        document.getElementById('lat').value = latlng.lat;
        document.getElementById('lng').value = latlng.lng;
        updateAddressFromCoords(latlng.lat, latlng.lng);
    });

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const userCoords = [position.coords.latitude, position.coords.longitude];
            map.setView(userCoords, 16);
            marker.setLatLng(userCoords);
            document.getElementById('lat').value = userCoords[0];
            document.getElementById('lng').value = userCoords[1];
            updateAddressFromCoords(userCoords[0], userCoords[1]);
        }, function() {
            console.log('Geolocation failed or was denied.');
        });
    }
}

async function updateAddressFromCoords(lat, lng) {
    const addressInput = document.getElementById('locationAddress');
    addressInput.value = "Buscando dirección...";
    try {
        const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`);
        const data = await response.json();
        if (data && data.display_name) {
            addressInput.value = data.display_name;
        } else {
            addressInput.value = "No se pudo encontrar la dirección.";
        }
    } catch (error) {
        console.error("Reverse geocoding failed:", error);
        addressInput.value = "Error al obtener la dirección.";
    }
}
