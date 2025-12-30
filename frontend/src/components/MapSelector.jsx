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

  const handlePositionChange = (newPosition) => {
    setPosition(newPosition);
    onLocationSelect({ lat: newPosition[0], lng: newPosition[1] });
  };

  return (
    <div className="mb-4">
        <label htmlFor="map" className="block text-sm font-medium text-gray-700 mb-2">
            Ubica el problema en el mapa (haz click para seleccionar)
        </label>
        <MapContainer 
            center={CUSCO_CENTER} 
            zoom={14} 
            className="h-96 w-full rounded-lg shadow-md z-0"
            id="map"
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
