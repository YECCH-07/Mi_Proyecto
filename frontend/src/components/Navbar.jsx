import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../hooks/useAuth';
import { getDashboardRoute } from '../utils/navigation';

export default function Navbar() {
  const { isAuthenticated, userRole, userName, logout } = useAuth();
  const navigate = useNavigate();

  const handleLogout = () => {
    logout();
    navigate('/');
  };

  const getDashboardLink = () => {
    return getDashboardRoute(userRole);
  };

  return (
    <nav className="bg-primary shadow-lg">
      <div className="container mx-auto px-4">
        <div className="flex justify-between items-center py-3">
          {/* Logo y Título - Lado Izquierdo */}
          <div className="flex items-center space-x-4">
            {/* Logo de la Organización */}
            <Link to="/" className="flex items-center space-x-3 hover:opacity-90 transition">
              <img
                src="/logo-municipalidad.png"
                alt="Logo Municipalidad"
                className="h-16 w-auto object-contain"
                onError={(e) => {
                  // Fallback si no existe el logo
                  e.target.style.display = 'none';
                  e.target.nextSibling.style.display = 'flex';
                }}
              />
              {/* Placeholder si no hay logo */}
              <div className="h-16 w-16 flex items-center justify-center hidden">
                <span className="text-white text-4xl">🏛️</span>
              </div>

              <div className="hidden md:block">
                <h1 className="text-white text-xl font-bold leading-tight">
                  Sistema de Denuncias
                </h1>
                <p className="text-white/80 text-xs">
                  Ciudadana
                </p>
              </div>
            </Link>
          </div>

          {/* Navegación - Lado Derecho */}
          <div className="flex items-center space-x-4">
            {isAuthenticated ? (
              // Usuario Logueado: Solo "Mi Panel" y "Cerrar Sesión"
              <>
                <Link
                  to={getDashboardLink()}
                  className="bg-white text-primary hover:bg-gray-100 px-4 py-2 rounded-md text-sm font-semibold transition"
                >
                  Mi Panel
                </Link>
                <div className="border-l border-white/30 pl-4 ml-2 flex items-center space-x-3">
                  <span className="text-white text-sm font-medium hidden md:inline">
                    {userName || 'Usuario'}
                  </span>
                  <button
                    onClick={handleLogout}
                    className="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-semibold transition"
                  >
                    Cerrar Sesión
                  </button>
                </div>
              </>
            ) : (
              // Usuario NO Logueado: Solo "Consulta tu Denuncia", "Registrarse", "Iniciar Sesión"
              <>
                <Link
                  to="/consulta"
                  className="text-white hover:text-gray-200 px-3 py-2 rounded-md text-sm font-medium hidden md:inline"
                >
                  Consulta tu Denuncia
                </Link>
                <Link
                  to="/register"
                  className="text-white hover:text-gray-200 px-3 py-2 rounded-md text-sm font-medium"
                >
                  Registrarse
                </Link>
                <Link
                  to="/login"
                  className="bg-white text-primary hover:bg-gray-100 px-4 py-2 rounded-md text-sm font-semibold transition"
                >
                  Iniciar Sesión
                </Link>
              </>
            )}
          </div>
        </div>
      </div>
    </nav>
  );
}
