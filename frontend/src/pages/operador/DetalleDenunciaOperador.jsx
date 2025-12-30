import { useState, useEffect } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import axios from 'axios';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost/DENUNCIA%20CIUDADANA/backend/api';

export default function DetalleDenunciaOperador() {
  const { id } = useParams();
  const navigate = useNavigate();

  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [denuncia, setDenuncia] = useState(null);
  const [procesando, setProcesando] = useState(false);

  // Estado del formulario de atención
  const [nuevoEstado, setNuevoEstado] = useState('');
  const [comentario, setComentario] = useState('');
  const [resultado, setResultado] = useState(null);

  useEffect(() => {
    fetchDetalleDenuncia();
  }, [id]);

  const fetchDetalleDenuncia = async () => {
    try {
      setLoading(true);
      const token = localStorage.getItem('jwt');

      const response = await axios.get(
        `${API_URL}/denuncias/detalle_operador.php?id=${id}`,
        {
          headers: {
            'Authorization': `Bearer ${token}`
          }
        }
      );

      if (response.data.success) {
        setDenuncia(response.data.data);
        setNuevoEstado(response.data.data.denuncia.estado); // Estado actual como default
      } else {
        setError('No se pudo cargar la denuncia');
      }
    } catch (err) {
      console.error('Error fetching denuncia:', err);
      setError(err.response?.data?.message || 'Error al cargar los datos');
    } finally {
      setLoading(false);
    }
  };

  const handleActualizarEstado = async (e) => {
    e.preventDefault();

    if (!comentario.trim()) {
      alert('Por favor ingrese un comentario');
      return;
    }

    if (!window.confirm(`¿Está seguro de cambiar el estado a "${nuevoEstado}"?`)) {
      return;
    }

    try {
      setProcesando(true);
      setResultado(null);

      const token = localStorage.getItem('jwt');

      const response = await axios.post(
        `${API_URL}/denuncias/actualizar_estado.php`,
        {
          denuncia_id: parseInt(id),
          nuevo_estado: nuevoEstado,
          comentario: comentario
        },
        {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        }
      );

      if (response.data.success) {
        setResultado({
          tipo: 'success',
          mensaje: 'Estado actualizado exitosamente',
          email_enviado: response.data.data.email_enviado,
          email_destinatario: response.data.data.email_destinatario
        });

        // Limpiar formulario
        setComentario('');

        // Recargar datos
        setTimeout(() => {
          fetchDetalleDenuncia();
        }, 1500);
      }
    } catch (err) {
      console.error('Error updating estado:', err);
      setResultado({
        tipo: 'error',
        mensaje: err.response?.data?.message || 'Error al actualizar el estado'
      });
    } finally {
      setProcesando(false);
    }
  };

  const getEstadoBadgeClass = (estado) => {
    const classes = {
      'registrada': 'bg-blue-100 text-blue-800 border-blue-300',
      'en_revision': 'bg-yellow-100 text-yellow-800 border-yellow-300',
      'asignada': 'bg-purple-100 text-purple-800 border-purple-300',
      'en_proceso': 'bg-indigo-100 text-indigo-800 border-indigo-300',
      'resuelta': 'bg-green-100 text-green-800 border-green-300',
      'cerrada': 'bg-gray-100 text-gray-800 border-gray-300',
      'rechazada': 'bg-red-100 text-red-800 border-red-300'
    };
    return classes[estado] || 'bg-gray-100 text-gray-800 border-gray-300';
  };

  const getEstadoLabel = (estado) => {
    const labels = {
      'registrada': 'Registrada',
      'en_revision': 'En Revisión',
      'asignada': 'Asignada',
      'en_proceso': 'En Proceso',
      'resuelta': 'Resuelta',
      'cerrada': 'Cerrada',
      'rechazada': 'Rechazada'
    };
    return labels[estado] || estado;
  };

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gray-50">
        <div className="text-center">
          <div className="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
          <p className="mt-4 text-gray-600">Cargando información de la denuncia...</p>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gray-50">
        <div className="bg-white p-8 rounded-lg shadow-md max-w-md w-full text-center">
          <div className="text-red-500 text-5xl mb-4">⚠️</div>
          <h2 className="text-2xl font-bold text-gray-800 mb-2">Error</h2>
          <p className="text-gray-600 mb-6">{error}</p>
          <button
            onClick={() => navigate('/operador/dashboard')}
            className="bg-primary hover:bg-primary-dark text-white px-6 py-2 rounded-md font-semibold transition"
          >
            Volver al Dashboard
          </button>
        </div>
      </div>
    );
  }

  if (!denuncia) return null;

  return (
    <div className="min-h-screen bg-gray-50 py-8">
      <div className="container mx-auto px-4 max-w-7xl">
        {/* Breadcrumb */}
        <div className="mb-6">
          <nav className="flex items-center space-x-2 text-sm text-gray-600">
            <Link to="/operador/dashboard" className="hover:text-primary transition">
              Dashboard Operador
            </Link>
            <span>/</span>
            <span className="text-gray-900 font-medium">Detalle de Denuncia</span>
          </nav>
        </div>

        {/* Header */}
        <div className="bg-white rounded-lg shadow-md p-6 mb-6">
          <div className="flex flex-col md:flex-row md:items-center md:justify-between">
            <div className="mb-4 md:mb-0">
              <h1 className="text-3xl font-bold text-gray-900 mb-2">{denuncia.denuncia.titulo}</h1>
              <div className="flex items-center space-x-3 flex-wrap gap-2">
                <span className="text-gray-600 font-mono text-sm">
                  Código: <span className="font-bold text-primary">{denuncia.denuncia.codigo}</span>
                </span>
                <span className={`px-3 py-1 rounded-full text-sm font-semibold border ${getEstadoBadgeClass(denuncia.denuncia.estado)}`}>
                  {getEstadoLabel(denuncia.denuncia.estado)}
                </span>
                {denuncia.denuncia.prioridad && (
                  <span className="px-3 py-1 rounded-full text-sm font-semibold bg-orange-100 text-orange-800 border border-orange-300 capitalize">
                    Prioridad: {denuncia.denuncia.prioridad}
                  </span>
                )}
              </div>
            </div>
            <button
              onClick={() => navigate('/operador/dashboard')}
              className="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-md font-semibold transition flex items-center space-x-2"
            >
              <span>←</span>
              <span>Volver</span>
            </button>
          </div>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* Columna Principal */}
          <div className="lg:col-span-2 space-y-6">
            {/* Descripción */}
            <div className="bg-white rounded-lg shadow-md p-6">
              <h2 className="text-xl font-bold text-gray-900 mb-4 flex items-center">
                <span className="text-2xl mr-2">📝</span>
                Descripción
              </h2>
              <p className="text-gray-700 leading-relaxed whitespace-pre-wrap">
                {denuncia.denuncia.descripcion || 'Sin descripción'}
              </p>
            </div>

            {/* Ubicación y Mapa */}
            {denuncia.ubicacion.google_maps_url && (
              <div className="bg-white rounded-lg shadow-md p-6">
                <h2 className="text-xl font-bold text-gray-900 mb-4 flex items-center">
                  <span className="text-2xl mr-2">📍</span>
                  Ubicación Georeferenciada
                </h2>
                <div className="space-y-3">
                  <div className="bg-gray-50 p-4 rounded-lg">
                    <p className="text-sm text-gray-600 mb-1">Coordenadas GPS:</p>
                    <p className="font-mono text-gray-900">
                      Lat: {denuncia.ubicacion.latitud}, Lng: {denuncia.ubicacion.longitud}
                    </p>
                  </div>
                  {denuncia.ubicacion.direccion_referencia && (
                    <div className="bg-gray-50 p-4 rounded-lg">
                      <p className="text-sm text-gray-600 mb-1">Dirección de Referencia:</p>
                      <p className="text-gray-900">{denuncia.ubicacion.direccion_referencia}</p>
                    </div>
                  )}
                  <a
                    href={denuncia.ubicacion.google_maps_url}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="inline-flex items-center space-x-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-semibold transition"
                  >
                    <span>🗺️</span>
                    <span>Abrir en Google Maps</span>
                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                  </a>
                </div>
              </div>
            )}

            {/* Evidencias */}
            {denuncia.evidencias && denuncia.evidencias.length > 0 && (
              <div className="bg-white rounded-lg shadow-md p-6">
                <h2 className="text-xl font-bold text-gray-900 mb-4 flex items-center">
                  <span className="text-2xl mr-2">📷</span>
                  Evidencias ({denuncia.evidencias.length})
                </h2>
                <div className="grid grid-cols-2 md:grid-cols-3 gap-4">
                  {denuncia.evidencias.map((evidencia, index) => (
                    <div key={evidencia.id} className="border-2 border-gray-200 rounded-lg overflow-hidden hover:border-primary transition">
                      {evidencia.tipo === 'imagen' ? (
                        <a href={evidencia.archivo_url} target="_blank" rel="noopener noreferrer">
                          <img
                            src={evidencia.archivo_url}
                            alt={`Evidencia ${index + 1}`}
                            className="w-full h-48 object-cover hover:opacity-90 transition"
                          />
                        </a>
                      ) : (
                        <video
                          src={evidencia.archivo_url}
                          controls
                          className="w-full h-48 object-cover"
                        />
                      )}
                      <div className="p-2 bg-gray-50 text-xs text-gray-600">
                        {evidencia.nombre_original || `Archivo ${index + 1}`}
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            )}

            {/* Historial de Seguimiento */}
            {denuncia.seguimiento && denuncia.seguimiento.length > 0 && (
              <div className="bg-white rounded-lg shadow-md p-6">
                <h2 className="text-xl font-bold text-gray-900 mb-4 flex items-center">
                  <span className="text-2xl mr-2">📋</span>
                  Historial de Seguimiento ({denuncia.seguimiento.length})
                </h2>
                <div className="space-y-4">
                  {denuncia.seguimiento.map((registro, index) => (
                    <div key={registro.id} className="border-l-4 border-primary pl-4 py-2 bg-gray-50 rounded-r-lg">
                      <div className="flex justify-between items-start mb-2">
                        <div>
                          <span className={`inline-block px-2 py-1 text-xs rounded ${getEstadoBadgeClass(registro.estado_nuevo)}`}>
                            {getEstadoLabel(registro.estado_anterior)} → {getEstadoLabel(registro.estado_nuevo)}
                          </span>
                        </div>
                        <span className="text-xs text-gray-500">
                          {new Date(registro.created_at).toLocaleString('es-PE')}
                        </span>
                      </div>
                      <p className="text-sm text-gray-700 mb-2">{registro.comentario}</p>
                      <p className="text-xs text-gray-500">
                        Por: {registro.responsable_nombre} ({registro.responsable_rol})
                      </p>
                    </div>
                  ))}
                </div>
              </div>
            )}
          </div>

          {/* Sidebar */}
          <div className="space-y-6">
            {/* Info del Ciudadano */}
            <div className="bg-white rounded-lg shadow-md p-6">
              <h2 className="text-xl font-bold text-gray-900 mb-4 flex items-center">
                <span className="text-2xl mr-2">👤</span>
                Ciudadano
              </h2>
              <dl className="space-y-3">
                <div>
                  <dt className="text-sm font-semibold text-gray-600">Nombre</dt>
                  <dd className="text-gray-900 mt-1">{denuncia.ciudadano.nombre_completo}</dd>
                </div>
                {denuncia.ciudadano.dni && denuncia.ciudadano.dni !== 'N/A' && (
                  <div>
                    <dt className="text-sm font-semibold text-gray-600">DNI</dt>
                    <dd className="text-gray-900 mt-1">{denuncia.ciudadano.dni}</dd>
                  </div>
                )}
                {denuncia.ciudadano.email && (
                  <div>
                    <dt className="text-sm font-semibold text-gray-600">Email</dt>
                    <dd className="text-gray-900 mt-1 break-all">{denuncia.ciudadano.email}</dd>
                  </div>
                )}
                {denuncia.ciudadano.telefono && (
                  <div>
                    <dt className="text-sm font-semibold text-gray-600">Teléfono</dt>
                    <dd className="text-gray-900 mt-1">{denuncia.ciudadano.telefono}</dd>
                  </div>
                )}
              </dl>
            </div>

            {/* Información Adicional */}
            <div className="bg-white rounded-lg shadow-md p-6">
              <h2 className="text-xl font-bold text-gray-900 mb-4 flex items-center">
                <span className="text-2xl mr-2">ℹ️</span>
                Información
              </h2>
              <dl className="space-y-3">
                <div>
                  <dt className="text-sm font-semibold text-gray-600">Categoría</dt>
                  <dd className="text-gray-900 mt-1 flex items-center">
                    {denuncia.categoria.icono && <span className="mr-2">{denuncia.categoria.icono}</span>}
                    {denuncia.categoria.nombre}
                  </dd>
                </div>
                {denuncia.area && (
                  <div>
                    <dt className="text-sm font-semibold text-gray-600">Área Asignada</dt>
                    <dd className="text-gray-900 mt-1">{denuncia.area.nombre}</dd>
                    {denuncia.area.responsable && (
                      <dd className="text-sm text-gray-600 mt-1">Responsable: {denuncia.area.responsable}</dd>
                    )}
                  </div>
                )}
                <div>
                  <dt className="text-sm font-semibold text-gray-600">Fecha de Registro</dt>
                  <dd className="text-gray-900 mt-1">
                    {new Date(denuncia.denuncia.created_at).toLocaleString('es-PE', {
                      day: '2-digit',
                      month: 'long',
                      year: 'numeric',
                      hour: '2-digit',
                      minute: '2-digit'
                    })}
                  </dd>
                </div>
              </dl>
            </div>

            {/* Formulario de Atención */}
            <div className="bg-primary-light border-2 border-primary/20 rounded-lg shadow-md p-6">
              <h2 className="text-xl font-bold text-gray-900 mb-4 flex items-center">
                <span className="text-2xl mr-2">✏️</span>
                Actualizar Estado
              </h2>

              <form onSubmit={handleActualizarEstado} className="space-y-4">
                {/* Selector de Estado */}
                <div>
                  <label htmlFor="nuevoEstado" className="block text-sm font-semibold text-gray-700 mb-2">
                    Nuevo Estado *
                  </label>
                  <select
                    id="nuevoEstado"
                    value={nuevoEstado}
                    onChange={(e) => setNuevoEstado(e.target.value)}
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                    required
                  >
                    <option value="registrada">Registrada</option>
                    <option value="en_revision">En Revisión</option>
                    <option value="asignada">Asignada</option>
                    <option value="en_proceso">En Proceso</option>
                    <option value="resuelta">Resuelta</option>
                    <option value="cerrada">Cerrada</option>
                    <option value="rechazada">Rechazada</option>
                  </select>
                </div>

                {/* Comentario */}
                <div>
                  <label htmlFor="comentario" className="block text-sm font-semibold text-gray-700 mb-2">
                    Comentario de Atención *
                  </label>
                  <textarea
                    id="comentario"
                    value={comentario}
                    onChange={(e) => setComentario(e.target.value)}
                    rows={4}
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                    placeholder="Ingrese el detalle de la atención brindada..."
                    required
                  />
                  <p className="mt-1 text-xs text-gray-600">
                    Este comentario será enviado al ciudadano por correo electrónico
                  </p>
                </div>

                {/* Botón Submit */}
                <button
                  type="submit"
                  disabled={procesando}
                  className={`w-full font-bold py-3 px-4 rounded-md transition flex items-center justify-center space-x-2
                    ${procesando
                      ? 'bg-gray-400 cursor-not-allowed'
                      : 'bg-primary hover:bg-primary-dark text-white'
                    }`}
                >
                  {procesando ? (
                    <>
                      <div className="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
                      <span>Procesando...</span>
                    </>
                  ) : (
                    <>
                      <span>💾</span>
                      <span>Guardar y Notificar</span>
                    </>
                  )}
                </button>
              </form>

              {/* Resultado */}
              {resultado && (
                <div className={`mt-4 p-4 rounded-md ${resultado.tipo === 'success' ? 'bg-green-100 border border-green-300' : 'bg-red-100 border border-red-300'}`}>
                  <p className={`font-semibold ${resultado.tipo === 'success' ? 'text-green-800' : 'text-red-800'}`}>
                    {resultado.mensaje}
                  </p>
                  {resultado.email_enviado && (
                    <p className="text-sm text-green-700 mt-2">
                      ✉️ Email enviado a: {resultado.email_destinatario}
                    </p>
                  )}
                  {resultado.email_enviado === false && (
                    <p className="text-sm text-yellow-700 mt-2">
                      ⚠️ No se pudo enviar el email automáticamente
                    </p>
                  )}
                </div>
              )}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
