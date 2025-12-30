import { useState, useEffect } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import axios from 'axios';
import { jwtDecode } from 'jwt-decode';

const API_URL = 'http://localhost/DENUNCIA%20CIUDADANA/backend/api';

export default function Login() {
    const [formData, setFormData] = useState({
        email: '',
        password: '',
    });
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [error, setError] = useState(null);
    const navigate = useNavigate();

    // Check if user is already logged in and redirect
    useEffect(() => {
        const token = localStorage.getItem('jwt');
        if (token) {
            try {
                const decoded = jwtDecode(token);
                // Check if token is not expired
                if (decoded.exp * 1000 > Date.now()) {
                    redirectByRole(decoded.data.rol);
                } else {
                    localStorage.removeItem('jwt');
                }
            } catch (error) {
                localStorage.removeItem('jwt');
            }
        }
    }, []);

    const redirectByRole = (role) => {
        switch (role) {
            case 'admin':
                navigate('/admin/dashboard', { replace: true });
                break;
            case 'supervisor':
                navigate('/supervisor/dashboard', { replace: true });
                break;
            case 'operador':
                navigate('/operador/dashboard', { replace: true });
                break;
            case 'ciudadano':
            default:
                navigate('/ciudadano/mis-denuncias', { replace: true });
                break;
        }
    };

    const handleInputChange = (e) => {
        const { name, value } = e.target;
        setFormData(prev => ({ ...prev, [name]: value }));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError(null);

        if (!formData.email || !formData.password) {
            setError('Por favor, ingresa tu email y contraseña.');
            return;
        }

        setIsSubmitting(true);
        try {
            console.log('[Login] Intentando login...');
            const response = await axios.post(`${API_URL}/auth/login.php`, formData);
            const { jwt } = response.data;

            console.log('[Login] Token recibido:', jwt ? 'SÍ' : 'NO');

            // Store the token in local storage
            localStorage.setItem('jwt', jwt);

            // Verify token was saved
            const savedToken = localStorage.getItem('jwt');
            console.log('[Login] Token guardado en localStorage:', savedToken ? 'SÍ' : 'NO');

            // Decode token to get user role
            const decoded = jwtDecode(jwt);
            const userRole = decoded.data.rol;
            const userName = `${decoded.data.nombres} ${decoded.data.apellidos}`;

            console.log('[Login] Usuario:', userName, 'Rol:', userRole);

            // Redirect based on user role
            console.log('[Login] Redirigiendo según rol...');
            redirectByRole(userRole);

        } catch (err) {
            console.error('[Login] Error:', err);
            setError(err.response?.data?.message || 'Ocurrió un error al iniciar sesión.');
        } finally {
            setIsSubmitting(false);
        }
    };

    return (
        <div className="container mx-auto p-4 max-w-md">
            <div className="bg-white p-8 rounded-lg shadow-lg mt-10">
                <h1 className="text-3xl font-bold text-primary mb-6 text-center">Iniciar Sesión</h1>
                
                {error && (
                    <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <strong className="font-bold">Error: </strong>
                        <span className="block sm:inline">{error}</span>
                    </div>
                )}

                <form onSubmit={handleSubmit} noValidate>
                    <div className="mb-4">
                        <label htmlFor="email" className="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" id="email" value={formData.email} onChange={handleInputChange} className="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary" required />
                    </div>
                    <div className="mb-6">
                        <label htmlFor="password" className="block text-sm font-medium text-gray-700">Contraseña</label>
                        <input type="password" name="password" id="password" value={formData.password} onChange={handleInputChange} className="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary" required />
                    </div>
                    <div className="text-center">
                        <button type="submit" disabled={isSubmitting} className="w-full inline-flex justify-center py-2 px-6 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:bg-gray-400">
                            {isSubmitting ? 'Ingresando...' : 'Iniciar Sesión'}
                        </button>
                    </div>
                </form>
                 <p className="text-sm text-center text-gray-600 mt-4">
                    ¿No tienes una cuenta? <Link to="/register" className="font-medium text-primary hover:text-primary-dark">Regístrate aquí</Link>
                </p>
            </div>
        </div>
    );
}
