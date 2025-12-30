import { useState, useEffect } from 'react';
import { denunciaService } from '../../services/denunciaService';
import { useAuth } from '../../hooks/useAuth';
import { Link } from 'react-router-dom';

export default function MisDenuncias() {
    const { userName } = useAuth();
    const [denuncias, setDenuncias] = useState([]);
    const [categorias, setCategorias] = useState([]);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState(null);

    const fetchData = async () => {
        try {
            setIsLoading(true);

            // Fetch denuncias and categorias in parallel
            const [denunciasData, categoriasData] = await Promise.all([
                denunciaService.getDenuncias(),
                denunciaService.getCategorias()
            ]);

            const records = denunciasData.records || [];
            const cats = categoriasData.records || [];

            // Backend already filters by usuario_id for ciudadanos
            setDenuncias(records);
            setCategorias(cats);

        } catch (err) {
            console.error('Error fetching data:', err);
            setError(err.message || 'No se pudieron cargar tus denuncias.');
        } finally {
            setIsLoading(false);
        }
    };

    useEffect(() => {
        fetchData();
    }, []);

    const getCategoriaNombre = (categoriaId) => {
        const categoria = categorias.find(c => c.id === categoriaId);
        return categoria ? categoria.nombre : 'Sin categoría';
    };

    const getStatusBadge = (estado) => {
        const statusConfig = {
            'registrada': { color: 'bg-blue-200 text-blue-800', label: 'Registrada' },
            'en_revision': { color: 'bg-yellow-200 text-yellow-800', label: 'En Revisión' },
            'asignada': { color: 'bg-purple-200 text-purple-800', label: 'Asignada' },
            'en_proceso': { color: 'bg-indigo-200 text-indigo-800', label: 'En Proceso' },
            'resuelta': { color: 'bg-green-200 text-green-800', label: 'Resuelta' },
            'rechazada': { color: 'bg-red-200 text-red-800', label: 'Rechazada' },
        };

        const config = statusConfig[estado] || { color: 'bg-gray-200 text-gray-800', label: estado };

        return (
            <span className={`px-3 py-1 rounded-full text-xs font-semibold ${config.color}`}>
                {config.label}
            </span>
        );
    };

    if (isLoading) {
        return (
            <div className="flex items-center justify-center min-h-screen">
                <div className="text-center">
                    <div className="animate-spin rounded-full h-16 w-16 border-b-2 border-primary mx-auto mb-4"></div>
                    <p className="text-lg text-gray-600">Cargando tus denuncias...</p>
                </div>
            </div>
        );
    }

    if (error) {
        return (
            <div className="container mx-auto p-4 max-w-4xl">
                <div className="bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded-lg mt-10" role="alert">
                    <strong className="font-bold">Error: </strong>
                    <span className="block sm:inline">{error}</span>
                </div>
            </div>
        );
    }

    return (
        <div className="container mx-auto p-4 max-w-7xl">
            {/* Header with Action Button */}
            <div className="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 className="text-4xl font-bold text-primary mb-2">Mis Denuncias</h1>
                    <p className="text-gray-600">Bienvenido, <span className="font-semibold">{userName}</span></p>
                </div>
                <Link
                    to="/nueva-denuncia"
                    className="bg-primary hover:bg-primary-dark text-white font-bold py-3 px-8 rounded-lg shadow-lg transition transform hover:scale-105 flex items-center gap-2"
                >
                    <span className="text-xl">+</span>
                    Registrar Nueva Denuncia
                </Link>
            </div>

            {/* Statistics */}
            <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                <div className="bg-white p-4 rounded-lg shadow">
                    <h3 className="text-sm font-semibold text-gray-600">Total</h3>
                    <p className="text-3xl font-bold text-primary mt-1">{denuncias.length}</p>
                </div>
                <div className="bg-white p-4 rounded-lg shadow">
                    <h3 className="text-sm font-semibold text-gray-600">En Proceso</h3>
                    <p className="text-3xl font-bold text-indigo-600 mt-1">
                        {denuncias.filter(d => d.estado === 'en_proceso').length}
                    </p>
                </div>
                <div className="bg-white p-4 rounded-lg shadow">
                    <h3 className="text-sm font-semibold text-gray-600">Resueltas</h3>
                    <p className="text-3xl font-bold text-green-600 mt-1">
                        {denuncias.filter(d => d.estado === 'resuelta').length}
                    </p>
                </div>
                <div className="bg-white p-4 rounded-lg shadow">
                    <h3 className="text-sm font-semibold text-gray-600">Pendientes</h3>
                    <p className="text-3xl font-bold text-yellow-600 mt-1">
                        {denuncias.filter(d => ['registrada', 'en_revision', 'asignada'].includes(d.estado)).length}
                    </p>
                </div>
            </div>

            {/* Denuncias Table */}
            <div className="bg-white rounded-lg shadow-lg p-6">
                <h2 className="text-2xl font-bold text-gray-800 mb-6">Historial de Denuncias</h2>

                {denuncias.length === 0 ? (
                    <div className="text-center py-16">
                        <div className="text-6xl mb-4">📝</div>
                        <h3 className="text-xl font-semibold text-gray-700 mb-2">
                            Aún no has realizado ninguna denuncia
                        </h3>
                        <p className="text-gray-500 mb-6">
                            Comienza a reportar problemas en tu comunidad
                        </p>
                        <Link
                            to="/nueva-denuncia"
                            className="inline-block bg-primary hover:bg-primary-dark text-white font-bold py-3 px-8 rounded-lg shadow-lg transition"
                        >
                            Registrar mi Primera Denuncia
                        </Link>
                    </div>
                ) : (
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Código
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Título
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Categoría
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Estado
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Fecha
                                    </th>
                                    <th className="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="bg-white divide-y divide-gray-200">
                                {denuncias.map(d => (
                                    <tr key={d.id} className="hover:bg-gray-50 transition">
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <span className="text-sm font-bold text-primary">{d.codigo}</span>
                                        </td>
                                        <td className="px-6 py-4">
                                            <div className="text-sm font-semibold text-gray-900">{d.titulo}</div>
                                            {d.descripcion && (
                                                <div className="text-xs text-gray-500 mt-1 max-w-xs truncate">
                                                    {d.descripcion}
                                                </div>
                                            )}
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <span className="text-sm text-gray-700">
                                                {getCategoriaNombre(d.categoria_id)}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            {getStatusBadge(d.estado)}
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {d.fecha_registro
                                                ? new Date(d.fecha_registro).toLocaleDateString('es-ES', {
                                                    year: 'numeric',
                                                    month: 'short',
                                                    day: 'numeric'
                                                })
                                                : 'N/A'}
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap text-center">
                                            <Link
                                                to={`/ciudadano/denuncia/${d.id}`}
                                                className="inline-flex items-center px-3 py-2 bg-primary hover:bg-primary-dark text-white text-sm font-medium rounded-md transition"
                                            >
                                                <span className="mr-1">👁️</span>
                                                Ver Detalles
                                            </Link>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>
        </div>
    );
}
