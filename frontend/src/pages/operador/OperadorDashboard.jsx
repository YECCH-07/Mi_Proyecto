import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { denunciaService } from '../../services/denunciaService';
import { useAuth } from '../../hooks/useAuth';

export default function OperadorDashboard() {
    const { userName } = useAuth();
    const [denuncias, setDenuncias] = useState([]);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState(null);

    const fetchData = async () => {
        try {
            setIsLoading(true);
            const denunciasData = await denunciaService.getDenuncias();
            const records = denunciasData.records || [];

            // Filter only denuncias that are in process or assigned
            const filteredDenuncias = records.filter(d =>
                d.estado === 'en_proceso' || d.estado === 'asignada'
            );

            setDenuncias(filteredDenuncias);

        } catch (err) {
            setError(err.message || 'No se pudieron cargar las denuncias.');
        } finally {
            setIsLoading(false);
        }
    };

    useEffect(() => {
        fetchData();
    }, []);

    const handleUpdate = async (denunciaId, newStatus) => {
        try {
            const denunciaToUpdate = denuncias.find(d => d.id === denunciaId);
            if (!denunciaToUpdate) return;

            const payload = {
                id: denunciaId,
                estado: newStatus,
                area_asignada_id: denunciaToUpdate.area_asignada_id,
                titulo: denunciaToUpdate.titulo,
                descripcion: denunciaToUpdate.descripcion,
                categoria_id: denunciaToUpdate.categoria_id,
            };

            await denunciaService.updateDenuncia(payload);
            await fetchData();

        } catch (error) {
            alert("Error al actualizar la denuncia: " + error.message);
        }
    };

    if (isLoading) return <div className="text-center p-10">Cargando Dashboard...</div>;
    if (error) return <div className="text-center p-10 text-red-500">Error: {error}</div>;

    return (
        <div className="container mx-auto p-4">
            <div className="mb-6">
                <h1 className="text-3xl font-bold text-primary">Panel de Operador</h1>
                <p className="text-gray-600">Bienvenido, {userName}</p>
            </div>

            {/* Statistics Card */}
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                <div className="bg-white p-6 rounded-lg shadow-lg">
                    <h3 className="text-lg font-semibold text-gray-700">Denuncias Asignadas</h3>
                    <p className="text-4xl font-bold text-primary mt-2">
                        {denuncias.filter(d => d.estado === 'asignada').length}
                    </p>
                </div>
                <div className="bg-white p-6 rounded-lg shadow-lg">
                    <h3 className="text-lg font-semibold text-gray-700">En Proceso</h3>
                    <p className="text-4xl font-bold text-primary mt-2">
                        {denuncias.filter(d => d.estado === 'en_proceso').length}
                    </p>
                </div>
            </div>

            {/* Denuncias Table */}
            <div className="bg-white p-6 rounded-lg shadow-lg">
                <h2 className="text-xl font-bold mb-4">Denuncias Asignadas a Mí</h2>
                <p className="text-sm text-gray-600 mb-4">Solo denuncias en proceso o asignadas que debo atender</p>
                {denuncias.length === 0 ? (
                    <p className="text-gray-500 text-center py-8">No tienes denuncias asignadas actualmente.</p>
                ) : (
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Código</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Título</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Categoría</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                    <th className="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Acciones</th>
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
                                                {d.categoria_nombre || 'Sin categoría'}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <span className={`px-3 py-1 rounded-full text-xs font-semibold ${
                                                d.estado === 'asignada' ? 'bg-yellow-100 text-yellow-800 border border-yellow-300' :
                                                d.estado === 'en_proceso' ? 'bg-blue-100 text-blue-800 border border-blue-300' :
                                                d.estado === 'resuelta' ? 'bg-green-100 text-green-800 border border-green-300' :
                                                'bg-gray-100 text-gray-800 border border-gray-300'
                                            }`}>
                                                {d.estado === 'asignada' ? 'Asignada' :
                                                 d.estado === 'en_proceso' ? 'En Proceso' :
                                                 d.estado === 'resuelta' ? 'Resuelta' : d.estado}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap text-center">
                                            <Link
                                                to={`/operador/denuncia/${d.id}`}
                                                className="inline-flex items-center px-4 py-2 bg-primary hover:bg-primary-dark text-white text-sm font-medium rounded-md transition shadow-sm"
                                            >
                                                <span className="mr-2">👁️</span>
                                                Ver Detalle
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
