import { useState, useEffect, useRef } from 'react';
import { denunciaService } from '../services/denunciaService';
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js';
import { Pie } from 'react-chartjs-2';

ChartJS.register(ArcElement, Tooltip, Legend);

const getStatusBadgeColor = (status) => {
    // (Re-using the same helper function)
    // ...
};

export default function Dashboard() {
    const [denuncias, setDenuncias] = useState([]);
    const [stats, setStats] = useState({});
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState(null);
    
    // Data for forms
    const [categorias, setCategorias] = useState([]);
    const [areas, setAreas] = useState([]);

    const fetchData = async () => {
        try {
            setIsLoading(true);
            const [denunciasData, categoriasData, areasData] = await Promise.all([
                denunciaService.getDenuncias(),
                denunciaService.getCategorias(),
                denunciaService.getAreas()
            ]);
            
            const records = denunciasData.records || [];
            setDenuncias(records);
            setCategorias(categoriasData.records || []);
            setAreas(areasData.records || []);
            
            // Calculate stats
            const statusCounts = records.reduce((acc, d) => {
                acc[d.estado] = (acc[d.estado] || 0) + 1;
                return acc;
            }, {});
            setStats({ statusCounts });

        } catch (err) {
            setError(err.message || 'No se pudieron cargar los datos del dashboard.');
        } finally {
            setIsLoading(false);
        }
    };

    useEffect(() => {
        fetchData();
    }, []);

    const handleUpdate = async (denunciaId, newStatus, newAreaId) => {
        try {
            const denunciaToUpdate = denuncias.find(d => d.id === denunciaId);
            if (!denunciaToUpdate) return;
            
            const payload = {
                id: denunciaId,
                estado: newStatus || denunciaToUpdate.estado,
                area_asignada_id: newAreaId || denunciaToUpdate.area_asignada_id,
                // Keep other fields the same
                titulo: denunciaToUpdate.titulo,
                descripcion: denunciaToUpdate.descripcion,
                categoria_id: denunciaToUpdate.categoria_id,
            };

            await denunciaService.updateDenuncia(payload);
            // Refresh data to show changes
            await fetchData();

        } catch (error) {
            alert("Error al actualizar la denuncia: " + error.message);
        }
    };

    const chartData = {
        labels: stats.statusCounts ? Object.keys(stats.statusCounts) : [],
        datasets: [
            {
                label: '# de Denuncias',
                data: stats.statusCounts ? Object.values(stats.statusCounts) : [],
                backgroundColor: [
                    'rgba(54, 162, 235, 0.7)', // registrada
                    'rgba(255, 206, 86, 0.7)', // en_revision
                    'rgba(75, 192, 192, 0.7)', // resuelta
                    'rgba(153, 102, 255, 0.7)', // en_proceso
                    'rgba(255, 99, 132, 0.7)', // rechazada
                ],
            },
        ],
    };

    if (isLoading) return <div className="text-center p-10">Cargando Dashboard...</div>;
    if (error) return <div className="text-center p-10 text-red-500">Error: {error}</div>;

    return (
        <div className="container mx-auto p-4">
            <h1 className="text-3xl font-bold text-primary mb-6">Dashboard Municipal</h1>
            
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <div className="lg:col-span-1 bg-white p-6 rounded-lg shadow-lg">
                    <h2 className="text-xl font-bold mb-4">Denuncias por Estado</h2>
                    {denuncias.length > 0 ? <Pie data={chartData} /> : <p>No hay datos para mostrar.</p>}
                </div>
                <div className="lg:col-span-2 bg-white p-6 rounded-lg shadow-lg">
                     <h2 className="text-xl font-bold mb-4">Gestión de Denuncias</h2>
                     <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Área Asignada</th>
                                </tr>
                            </thead>
                            <tbody className="bg-white divide-y divide-gray-200">
                                {denuncias.map(d => (
                                    <tr key={d.id}>
                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{d.codigo}</td>
                                        <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{d.titulo}</td>
                                        <td className="px-6 py-4 whitespace-nowrap text-sm">
                                            <select 
                                                defaultValue={d.estado}
                                                onChange={(e) => handleUpdate(d.id, e.target.value, null)}
                                                className="block w-full p-1 border-gray-300 rounded-md"
                                            >
                                                <option value="registrada">Registrada</option>
                                                <option value="en_revision">En Revisión</option>
                                                <option value="asignada">Asignada</option>
                                                <option value="en_proceso">En Proceso</option>
                                                <option value="resuelta">Resuelta</option>
                                                <option value="rechazada">Rechazada</option>
                                            </select>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap text-sm">
                                            <select 
                                                defaultValue={d.area_asignada_id || ""}
                                                onChange={(e) => handleUpdate(d.id, 'asignada', e.target.value)}
                                                className="block w-full p-1 border-gray-300 rounded-md"
                                            >
                                                <option value="">Sin asignar</option>
                                                {areas.map(area => <option key={area.id} value={area.id}>{area.nombre}</option>)}
                                            </select>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                     </div>
                </div>
            </div>
        </div>
    );
}
