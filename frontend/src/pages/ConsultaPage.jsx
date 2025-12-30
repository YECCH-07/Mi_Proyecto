import { useState } from 'react';
import { denunciaService } from '../services/denunciaService';

const getStatusBadgeColor = (status) => {
    // (Re-using the same helper function from DenunciaCard)
    switch (status) {
        case 'registrada': return 'bg-blue-100 text-blue-800';
        case 'en_revision': return 'bg-yellow-100 text-yellow-800';
        case 'asignada': return 'bg-indigo-100 text-indigo-800';
        case 'en_proceso': return 'bg-purple-100 text-purple-800';
        case 'resuelta': return 'bg-green-100 text-green-800';
        case 'cerrada': return 'bg-gray-100 text-gray-800';
        case 'rechazada': return 'bg-red-100 text-red-800';
        default: return 'bg-gray-200 text-gray-900';
    }
};

export default function ConsultaPage() {
    const [codigo, setCodigo] = useState('');
    const [denuncia, setDenuncia] = useState(null);
    const [seguimiento, setSeguimiento] = useState([]);
    const [isLoading, setIsLoading] = useState(false);
    const [error, setError] = useState(null);

    const handleSearch = async (e) => {
        e.preventDefault();
        if (!codigo) return;

        setIsLoading(true);
        setError(null);
        setDenuncia(null);
        setSeguimiento([]);

        try {
            const denunciaData = await denunciaService.getDenunciaByCodigo(codigo);
            setDenuncia(denunciaData);
            
            if (denunciaData.id) {
                const seguimientoData = await denunciaService.getSeguimiento(denunciaData.id);
                setSeguimiento(seguimientoData.records || []);
            }
        } catch (err) {
            setError(err.message || `No se encontró ninguna denuncia con el código "${codigo}".`);
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <div className="container mx-auto p-4 max-w-3xl">
            <div className="bg-white p-8 rounded-lg shadow-lg mb-6">
                <h1 className="text-3xl font-bold text-primary mb-4">Consulta tu Denuncia</h1>
                <p className="text-gray-600 mb-6">Ingresa el código único que recibiste al registrar tu denuncia para ver su estado actual y el historial de seguimiento.</p>
                <form onSubmit={handleSearch} className="flex items-center gap-2">
                    <input
                        type="text"
                        value={codigo}
                        onChange={(e) => setCodigo(e.target.value.toUpperCase())}
                        placeholder="Ej: DU-2025-000001"
                        className="flex-grow mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary"
                    />
                    <button
                        type="submit"
                        disabled={isLoading}
                        className="inline-flex justify-center py-2 px-6 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:bg-gray-400"
                    >
                        {isLoading ? 'Buscando...' : 'Buscar'}
                    </button>
                </form>
            </div>

            {error && (
                <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg text-center" role="alert">
                    <p>{error}</p>
                </div>
            )}

            {denuncia && (
                <div className="bg-white p-8 rounded-lg shadow-lg">
                    <div className="flex justify-between items-start mb-4">
                        <div>
                            <h2 className="text-2xl font-bold text-gray-800">{denuncia.titulo}</h2>
                            <p className="text-sm text-gray-500">{denuncia.codigo}</p>
                        </div>
                        <span className={`px-3 py-1 rounded-full text-sm font-semibold ${getStatusBadgeColor(denuncia.estado)}`}>
                            {denuncia.estado}
                        </span>
                    </div>
                    <div className="border-t border-gray-200 pt-4 mt-4">
                        <h3 className="text-lg font-semibold text-primary mb-3">Historial de Estados</h3>
                        <div className="relative border-l-2 border-gray-200 ml-3">
                            {seguimiento.length > 0 ? (
                                seguimiento.map((item, index) => (
                                <div key={item.id} className="mb-6 ml-6">
                                    <span className="absolute flex items-center justify-center w-6 h-6 bg-blue-100 rounded-full -left-3 ring-8 ring-white">
                                        <svg className="w-2.5 h-2.5 text-blue-800" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4Z"/>
                                        </svg>
                                    </span>
                                    <h4 className="flex items-center mb-1 text-base font-semibold text-gray-900">
                                        {item.estado_nuevo}
                                        {index === seguimiento.length - 1 && <span className="bg-green-100 text-green-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded ml-3">Actual</span>}
                                    </h4>
                                    <time className="block mb-2 text-sm font-normal leading-none text-gray-400">
                                        {new Date(item.created_at).toLocaleString('es-ES')}
                                    </time>
                                    <p className="text-sm text-gray-600">"{item.comentario}" - <span className="font-medium">{item.usuario_nombre || 'Sistema'}</span></p>
                                </div>
                            ))
                            ) : (
                                <p className="text-sm text-gray-500 ml-6">No hay historial de seguimiento disponible.</p>
                            )}
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
