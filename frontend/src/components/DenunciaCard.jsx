const getStatusBadgeColor = (status) => {
    switch (status) {
        case 'registrada':
            return 'bg-blue-100 text-blue-800';
        case 'en_revision':
            return 'bg-yellow-100 text-yellow-800';
        case 'asignada':
            return 'bg-indigo-100 text-indigo-800';
        case 'en_proceso':
            return 'bg-purple-100 text-purple-800';
        case 'resuelta':
            return 'bg-green-100 text-green-800';
        case 'cerrada':
            return 'bg-gray-100 text-gray-800';
        case 'rechazada':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-200 text-gray-900';
    }
};

export default function DenunciaCard({ denuncia }) {
    const { codigo, titulo, estado, created_at, usuario_nombre } = denuncia;

    const formattedDate = new Date(created_at).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });

    return (
        <div className="bg-white rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300 ease-in-out overflow-hidden">
            <div className="p-6">
                <div className="flex justify-between items-start mb-2">
                    <p className="text-sm text-gray-500">{codigo}</p>
                    <span className={`px-2.5 py-0.5 rounded-full text-xs font-semibold ${getStatusBadgeColor(estado)}`}>
                        {estado.replace('_', ' ')}
                    </span>
                </div>
                <h3 className="text-lg font-bold text-gray-800 mb-2">{titulo}</h3>
                <div className="text-sm text-gray-600">
                    <p>
                        Reportado por: <span className="font-medium">{usuario_nombre || 'Anónimo'}</span>
                    </p>
                    <p>
                        Fecha: <span className="font-medium">{formattedDate}</span>
                    </p>
                </div>
            </div>
            <div className="bg-gray-50 px-6 py-3 text-right">
                <a href="#" className="text-sm font-medium text-primary hover:text-primary-dark">
                    Ver detalles &rarr;
                </a>
            </div>
        </div>
    );
}
