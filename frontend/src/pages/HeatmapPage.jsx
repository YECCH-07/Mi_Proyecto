import { useState, useEffect } from 'react';
import { MapContainer, TileLayer } from 'react-leaflet';
import L from 'leaflet';
import 'leaflet.heat'; // Import Leaflet.heat
import { denunciaService } from '../services/denunciaService';

// Fix for default marker icon issue with webpack
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon-2x.png',
  iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png',
  shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
});

// Center: Cusco, Perú
const CUSCO_CENTER = [-13.5319, -71.9675];

const HeatmapLayer = ({ points }) => {
  const map = L.useMap();
  useEffect(() => {
    if (map && points.length > 0) {
      const heat = L.heatLayer(points, {
        radius: 25,
        blur: 15,
        maxZoom: 17,
        gradient: {
          0.4: 'blue',
          0.6: 'cyan',
          0.7: 'lime',
          0.8: 'yellow',
          1.0: 'red'
        }
      }).addTo(map);

      // Clean up on unmount
      return () => {
        map.removeLayer(heat);
      };
    }
  }, [map, points]);

  return null;
};

export default function HeatmapPage() {
  const [points, setPoints] = useState([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchLocations = async () => {
      try {
        setIsLoading(true);
        const data = await denunciaService.getDenunciasLocations();
        // Format data for leaflet.heat: [latitude, longitude, intensity]
        // Intensity can be 1 for all points, or based on some denuncia property
        const heatmapPoints = data.records.map(loc => [loc.lat, loc.lng, 1]); 
        setPoints(heatmapPoints);
      } catch (err) {
        setError(err.message || 'Error al cargar las ubicaciones de las denuncias.');
      } finally {
        setIsLoading(false);
      }
    };

    fetchLocations();
  }, []);

  if (isLoading) return <div className="text-center p-10">Cargando mapa de calor...</div>;
  if (error) return <div className="text-center p-10 text-red-500">Error: {error}</div>;

  return (
    <div className="container mx-auto p-4">
      <h1 className="text-3xl font-bold text-primary mb-6">Mapa de Calor de Incidencias</h1>
      <div className="bg-white p-2 rounded-lg shadow-lg">
        <MapContainer 
          center={CUSCO_CENTER} 
          zoom={13} 
          className="h-[600px] w-full rounded-lg z-0"
        >
          <TileLayer
            attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
          />
          <HeatmapLayer points={points} />
        </MapContainer>
      </div>
    </div>
  );
}
