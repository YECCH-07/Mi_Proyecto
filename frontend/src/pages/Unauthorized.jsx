import { Link } from 'react-router-dom';
import { useAuth } from '../hooks/useAuth';

export default function Unauthorized() {
    const { isAuthenticated, userRole } = useAuth();

    return (
        <div className="container mx-auto p-4 max-w-2xl">
            <div className="bg-white p-8 rounded-lg shadow-lg mt-20 text-center">
                <div className="mb-6">
                    <svg
                        className="mx-auto h-24 w-24 text-red-500"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth={2}
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                        />
                    </svg>
                </div>

                <h1 className="text-3xl font-bold text-gray-800 mb-4">Acceso No Autorizado</h1>
                <p className="text-gray-600 mb-6">
                    No tienes permisos para acceder a esta página.
                </p>

                {isAuthenticated && userRole && (
                    <p className="text-sm text-gray-500 mb-6">
                        Tu rol actual es: <span className="font-semibold">{userRole}</span>
                    </p>
                )}

                <div className="space-x-4">
                    <Link
                        to="/"
                        className="inline-block bg-primary hover:bg-primary-dark text-white font-semibold py-2 px-6 rounded-lg transition"
                    >
                        Ir al Inicio
                    </Link>
                    {isAuthenticated ? (
                        <Link
                            to={
                                userRole === 'admin' ? '/admin/dashboard' :
                                userRole === 'supervisor' ? '/supervisor/dashboard' :
                                userRole === 'operador' ? '/operador/dashboard' :
                                '/ciudadano/mis-denuncias'
                            }
                            className="inline-block bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-6 rounded-lg transition"
                        >
                            Ir a Mi Panel
                        </Link>
                    ) : (
                        <Link
                            to="/login"
                            className="inline-block bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-6 rounded-lg transition"
                        >
                            Iniciar Sesión
                        </Link>
                    )}
                </div>
            </div>
        </div>
    );
}
