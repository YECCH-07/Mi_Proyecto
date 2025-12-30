import { Navigate } from 'react-router-dom';
import { useAuth } from '../hooks/useAuth';

const ProtectedRoute = ({ children, allowedRoles }) => {
    const { isAuthenticated, userRole, isLoading } = useAuth();

    // Show loading state while checking authentication
    if (isLoading) {
        return (
            <div className="flex items-center justify-center min-h-screen">
                <div className="text-xl text-gray-600">Verificando autenticación...</div>
            </div>
        );
    }

    // If not authenticated, redirect to login
    if (!isAuthenticated) {
        return <Navigate to="/login" replace />;
    }

    // If specific roles are required, check if user has permission
    if (allowedRoles && allowedRoles.length > 0 && !allowedRoles.includes(userRole)) {
        // Redirect to unauthorized page or home
        return <Navigate to="/unauthorized" replace />;
    }

    // User is authenticated and authorized, render children
    return children;
};

export default ProtectedRoute;
