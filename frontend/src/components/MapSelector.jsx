import { MapContainer, TileLayer, Marker, useMapEvents } from 'react-leaflet';
import { useState } from 'react';
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';

// Fix for default marker icon issue with webpack
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon-2x.png',
  iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png',
  shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
});


// Center: Cusco, Perú
const CUSCO_CENTER = [-13.5319, -71.9675];

function LocationMarker({ position, setPosition }) {
  const map = useMapEvents({
    click(e) {
      setPosition([e.latlng.lat, e.latlng.lng]);
      map.flyTo(e.latlng, map.getZoom());
    }
  });

  return position === null ? null : (
    <Marker position={position}></Marker>
  );
}

export default function MapSelector({ onLocationSelect }) {
  const [position, setPosition] = useState(null);
  const [map, setMap] = useState(null);
  const [isLoadingLocation, setIsLoadingLocation] = useState(false);
  const [locationError, setLocationError] = useState(null);

  const handlePositionChange = (newPosition) => {
    setPosition(newPosition);
    onLocationSelect({ lat: newPosition[0], lng: newPosition[1] });
  };

  const handleGetCurrentLocation = () => {
    setIsLoadingLocation(true);
    setLocationError(null);

    if (!navigator.geolocation) {
      setLocationError('Tu navegador no soporta geolocalización');
      setIsLoadingLocation(false);
      return;
    }

    navigator.geolocation.getCurrentPosition(
      (position) => {
        const { latitude, longitude } = position.coords;
        const newPosition = [latitude, longitude];
        handlePositionChange(newPosition);

        // Centrar el mapa en la ubicación actual
        if (map) {
          map.flyTo(newPosition, 16);
        }

        setIsLoadingLocation(false);
      },
      (error) => {
        let errorMessage = 'No se pudo obtener tu ubicación';
        switch (error.code) {
          case error.PERMISSION_DENIED:
            errorMessage = 'Permiso denegado. Por favor, permite el acceso a tu ubicación.';
            break;
          case error.POSITION_UNAVAILABLE:
            errorMessage = 'Información de ubicación no disponible.';
            break;
          case error.TIMEOUT:
            errorMessage = 'Tiempo de espera agotado al obtener la ubicación.';
            break;
        }
        setLocationError(errorMessage);
        setIsLoadingLocation(false);
      },
      {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 0
      }
    );
  };

  return (
    <div className="mb-4">
        <div className="flex justify-between items-center mb-2">
          <label htmlFor="map" className="block text-sm font-medium text-gray-700">
              Ubica el problema en el mapa (haz click para seleccionar)
          </label>
          <button
            type="button"
            onClick={handleGetCurrentLocation}
            disabled={isLoadingLocation}
            className="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:bg-gray-400 disabled:cursor-not-allowed"
          >
            {isLoadingLocation ? (
              <>
                <svg className="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                  <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Obteniendo...
              </>
            ) : (
              <>
                <svg className="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Usar mi ubicación
              </>
            )}
          </button>
        </div>

        {locationError && (
          <div className="mb-2 p-2 bg-red-100 border border-red-400 text-red-700 text-sm rounded">
            {locationError}
          </div>
        )}

        <MapContainer
            center={CUSCO_CENTER}
            zoom={14}
            className="h-96 w-full rounded-lg shadow-md z-0"
            id="map"
            whenCreated={setMap}
            >
            <TileLayer
                attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
            />
            <LocationMarker position={position} setPosition={handlePositionChange} />
        </MapContainer>
        {position && (
            <div className="p-2 mt-2 bg-primary-light text-sm text-gray-800 rounded">
                <strong>Coordenadas seleccionadas:</strong><br/>
                Latitud: {position[0].toFixed(6)}, Longitud: {position[1].toFixed(6)}
            </div>
        )}
    </div>
  );
}
