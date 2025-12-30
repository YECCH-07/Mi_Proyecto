import { useEffect, useState } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import { denunciaService } from '../../services/denunciaService';
import { MapContainer, TileLayer, Marker, Popup } from 'react-leaflet';
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';

// Fix for default marker icon in Leaflet with Webpack/Vite
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon-2x.png',
  iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png',
  shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
});

export default function DetalleDenuncia() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [denuncia, setDenuncia] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchDenuncia = async () => {
      try {
        setLoading(true);
        const response = await denunciaService.getDenunciaById(id);

        // Backend returns single object directly, not wrapped
        if (response && response.id) {
          setDenuncia(response);
        } else {
          setError('Denuncia no encontrada');
        }
      } catch (err) {
        console.error('Error fetching denuncia:', err);
        setError(err.message || 'Error al cargar la denuncia');
      } finally {
        setLoading(false);
      }
    };

    fetchDenuncia();
  }, [id]);

  const getEstadoBadgeClass = (estado) => {
    const classes = {
      'pendiente': 'bg-yellow-100 text-yellow-800 border-yellow-300',
      'en_proceso': 'bg-blue-100 text-blue-800 border-blue-300',
      'resuelto': 'bg-green-100 text-green-800 border-green-300',
      'rechazado': 'bg-red-100 text-red-800 border-red-300'
    };
    return classes[estado] || 'bg-gray-100 text-gray-800 border-gray-300';
  };

  const getEstadoLabel = (estado) => {
    const labels = {
      'pendiente': 'Pendiente',
      'en_proceso': 'En Proceso',
      'resuelto': 'Resuelto',
      'rechazado': 'Rechazado'
    };
    return labels[estado] || estado;
  };

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gray-50">
        <div className="text-center">
          <div className="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
          <p className="mt-4 text-gray-600">Cargando detalles de la denuncia...</p>
        </div>
      </div>
    );
  }

  if (error || !denuncia) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gray-50">
        <div className="bg-white p-8 rounded-lg shadow-md max-w-md w-full text-center">
          <div className="text-red-500 text-5xl mb-4">⚠️</div>
          <h2 className="text-2xl font-bold text-gray-800 mb-2">Error</h2>
          <p className="text-gray-600 mb-6">{error || 'Denuncia no encontrada'}</p>
          <button
            onClick={() => navigate('/ciudadano/mis-denuncias')}
            className="bg-primary hover:bg-primary-dark text-white px-6 py-2 rounded-md font-semibold transition"
          >
            Volver a Mis Denuncias
          </button>
        </div>
      </div>
    );
  }

  const hasLocation = denuncia.latitud && denuncia.longitud;
  const position = hasLocation ? [parseFloat(denuncia.latitud), parseFloat(denuncia.longitud)] : null;

  return (
    <div className="min-h-screen bg-gray-50 py-8">
      <div className="container mx-auto px-4 max-w-5xl">
        {/* Breadcrumb */}
        <div className="mb-6">
          <nav className="flex items-center space-x-2 text-sm text-gray-600">
            <Link to="/ciudadano/mis-denuncias" className="hover:text-primary transition">
              Mis Denuncias
            </Link>
            <span>/</span>
            <span className="text-gray-900 font-medium">Detalle de Denuncia</span>
          </nav>
        </div>

        {/* Header */}
        <div className="bg-white rounded-lg shadow-md p-6 mb-6">
          <div className="flex flex-col md:flex-row md:items-center md:justify-between">
            <div className="mb-4 md:mb-0">
              <h1 className="text-3xl font-bold text-gray-900 mb-2">{denuncia.titulo}</h1>
              <div className="flex items-center space-x-3">
                <span className="text-gray-600 font-mono text-sm">
                  Código: <span className="font-bold text-primary">{denuncia.codigo}</span>
                </span>
                <span className={`px-3 py-1 rounded-full text-sm font-semibold border ${getEstadoBadgeClass(denuncia.estado)}`}>
                  {getEstadoLabel(denuncia.estado)}
                </span>
              </div>
            </div>
            <button
              onClick={() => navigate('/ciudadano/mis-denuncias')}
              className="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-md font-semibold transition flex items-center space-x-2"
            >
              <span>←</span>
              <span>Volver</span>
            </button>
          </div>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* Main Content */}
          <div className="lg:col-span-2 space-y-6">
            {/* Description */}
            <div className="bg-white rounded-lg shadow-md p-6">
              <h2 className="text-xl font-bold text-gray-900 mb-4 flex items-center">
                <span className="text-2xl mr-2">📝</span>
                Descripción
              </h2>
              <p className="text-gray-700 leading-relaxed whitespace-pre-wrap">
                {denuncia.descripcion || 'Sin descripción'}
              </p>
            </div>

            {/* Map */}
            {hasLocation ? (
              <div className="bg-white rounded-lg shadow-md p-6">
                <h2 className="text-xl font-bold text-gray-900 mb-4 flex items-center">
                  <span className="text-2xl mr-2">📍</span>
                  Ubicación
                </h2>
                <div className="h-96 rounded-lg overflow-hidden border-2 border-gray-200">
                  <MapContainer
                    center={position}
                    zoom={16}
                    style={{ height: '100%', width: '100%' }}
                    scrollWheelZoom={false}
                  >
                    <TileLayer
                      attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                      url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                    />
                    <Marker position={position}>
                      <Popup>
                        <div className="text-center">
                          <p className="font-bold">{denuncia.titulo}</p>
                          <p className="text-sm text-gray-600">{denuncia.direccion || 'Ubicación del incidente'}</p>
                        </div>
                      </Popup>
                    </Marker>
                  </MapContainer>
                </div>
                <div className="mt-3 text-sm text-gray-600">
                  <p>📌 Coordenadas: {denuncia.latitud}, {denuncia.longitud}</p>
                  {denuncia.direccion && <p>📍 Dirección: {denuncia.direccion}</p>}
                </div>
              </div>
            ) : (
              <div className="bg-white rounded-lg shadow-md p-6">
                <h2 className="text-xl font-bold text-gray-900 mb-4 flex items-center">
                  <span className="text-2xl mr-2">📍</span>
                  Ubicación
                </h2>
                <div className="bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
                  <div className="text-gray-400 text-5xl mb-3">📍</div>
                  <p className="text-gray-600">No se registró ubicación para esta denuncia</p>
                  {denuncia.direccion && (
                    <p className="mt-2 text-sm text-gray-700">
                      <strong>Dirección:</strong> {denuncia.direccion}
                    </p>
                  )}
                </div>
              </div>
            )}
          </div>

          {/* Sidebar */}
          <div className="space-y-6">
            {/* Details Card */}
            <div className="bg-white rounded-lg shadow-md p-6">
              <h2 className="text-xl font-bold text-gray-900 mb-4 flex items-center">
                <span className="text-2xl mr-2">ℹ️</span>
                Información
              </h2>
              <dl className="space-y-3">
                <div>
                  <dt className="text-sm font-semibold text-gray-600">Categoría</dt>
                  <dd className="text-gray-900 mt-1">{denuncia.categoria_nombre || 'Sin categoría'}</dd>
                </div>
                <div>
                  <dt className="text-sm font-semibold text-gray-600">Fecha de registro</dt>
                  <dd className="text-gray-900 mt-1">
                    {new Date(denuncia.fecha_registro || denuncia.created_at).toLocaleDateString('es-PE', {
                      day: '2-digit',
                      month: 'long',
                      year: 'numeric',
                      hour: '2-digit',
                      minute: '2-digit'
                    })}
                  </dd>
                </div>
                {denuncia.area_nombre && (
                  <div>
                    <dt className="text-sm font-semibold text-gray-600">Área asignada</dt>
                    <dd className="text-gray-900 mt-1">{denuncia.area_nombre}</dd>
                  </div>
                )}
                <div>
                  <dt className="text-sm font-semibold text-gray-600">Estado</dt>
                  <dd className="mt-1">
                    <span className={`px-3 py-1 rounded-full text-sm font-semibold border inline-block ${getEstadoBadgeClass(denuncia.estado)}`}>
                      {getEstadoLabel(denuncia.estado)}
                    </span>
                  </dd>
                </div>
                {denuncia.prioridad && (
                  <div>
                    <dt className="text-sm font-semibold text-gray-600">Prioridad</dt>
                    <dd className="text-gray-900 mt-1 capitalize">{denuncia.prioridad}</dd>
                  </div>
                )}
                <div>
                  <dt className="text-sm font-semibold text-gray-600">Anónima</dt>
                  <dd className="text-gray-900 mt-1">{denuncia.es_anonima ? 'Sí' : 'No'}</dd>
                </div>
              </dl>
            </div>

            {/* Actions Card */}
            <div className="bg-primary-light rounded-lg shadow-md p-6 border-2 border-primary/20">
              <h3 className="text-lg font-bold text-gray-900 mb-3">¿Necesitas ayuda?</h3>
              <p className="text-sm text-gray-700 mb-4">
                Si tienes preguntas sobre el estado de tu denuncia, puedes consultar usando tu código.
              </p>
              <Link
                to="/consulta"
                className="block w-full bg-primary hover:bg-primary-dark text-white text-center px-4 py-2 rounded-md font-semibold transition"
              >
                Consultar Estado
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
