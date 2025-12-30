/**
 * Obtiene la ruta del dashboard correspondiente según el rol del usuario
 * @param {string} userRole - El rol del usuario (admin, supervisor, operador, ciudadano)
 * @returns {string} La ruta del dashboard correspondiente
 */
export const getDashboardRoute = (userRole) => {
    const dashboardRoutes = {
        'admin': '/admin/dashboard',
        'supervisor': '/supervisor/dashboard',
        'operador': '/operador/dashboard',
        'ciudadano': '/ciudadano/mis-denuncias'
    };

    return dashboardRoutes[userRole] || '/';
};

/**
 * Obtiene el nombre del dashboard según el rol
 * @param {string} userRole - El rol del usuario
 * @returns {string} El nombre descriptivo del dashboard
 */
export const getDashboardName = (userRole) => {
    const dashboardNames = {
        'admin': 'Dashboard de Administrador',
        'supervisor': 'Dashboard de Supervisor',
        'operador': 'Dashboard de Operador',
        'ciudadano': 'Mis Denuncias'
    };

    return dashboardNames[userRole] || 'Dashboard';
};
