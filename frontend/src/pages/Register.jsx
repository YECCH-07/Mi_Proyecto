import { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import axios from 'axios';

const API_URL = 'http://localhost/DENUNCIA%20CIUDADANA/backend/api';

export default function Register() {
    const [formData, setFormData] = useState({
        dni: '',
        nombres: '',
        apellidos: '',
        email: '',
        password: '',
        telefono: '',
    });
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [error, setError] = useState(null);
    const navigate = useNavigate();

    const handleInputChange = (e) => {
        const { name, value } = e.target;
        setFormData(prev => ({ ...prev, [name]: value }));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError(null);

        // Basic validation
        if (!formData.dni || !formData.nombres || !formData.apellidos || !formData.email || !formData.password) {
            setError('Por favor, completa todos los campos obligatorios.');
            return;
        }

        setIsSubmitting(true);
        try {
            await axios.post(`${API_URL}/auth/register.php`, formData);
            alert('¡Registro exitoso! Ahora puedes iniciar sesión.');
            navigate('/login');
            
        } catch (err) {
            setError(err.response?.data?.message || 'Ocurrió un error al registrar. Por favor, intenta de nuevo.');
        } finally {
            setIsSubmitting(false);
        }
    };

    return (
        <div className="container mx-auto p-4 max-w-md">
            <div className="bg-white p-8 rounded-lg shadow-lg mt-10">
                <h1 className="text-3xl font-bold text-primary mb-6 text-center">Crear Cuenta</h1>
                
                {error && (
                    <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <strong className="font-bold">Error: </strong>
                        <span className="block sm:inline">{error}</span>
                    </div>
                )}

                <form onSubmit={handleSubmit} noValidate>
                    <div className="mb-4">
                        <label htmlFor="dni" className="block text-sm font-medium text-gray-700">DNI</label>
                        <input type="text" name="dni" id="dni" value={formData.dni} onChange={handleInputChange} className="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary" required />
                    </div>
                    <div className="mb-4">
                        <label htmlFor="nombres" className="block text-sm font-medium text-gray-700">Nombres</label>
                        <input type="text" name="nombres" id="nombres" value={formData.nombres} onChange={handleInputChange} className="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary" required />
                    </div>
                    <div className="mb-4">
                        <label htmlFor="apellidos" className="block text-sm font-medium text-gray-700">Apellidos</label>
                        <input type="text" name="apellidos" id="apellidos" value={formData.apellidos} onChange={handleInputChange} className="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary" required />
                    </div>
                    <div className="mb-4">
                        <label htmlFor="email" className="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" id="email" value={formData.email} onChange={handleInputChange} className="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary" required />
                    </div>
                    <div className="mb-4">
                        <label htmlFor="password" className="block text-sm font-medium text-gray-700">Contraseña</label>
                        <input type="password" name="password" id="password" value={formData.password} onChange={handleInputChange} className="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary" required />
                    </div>
                     <div className="mb-6">
                        <label htmlFor="telefono" className="block text-sm font-medium text-gray-700">Teléfono (Opcional)</label>
                        <input type="text" name="telefono" id="telefono" value={formData.telefono} onChange={handleInputChange} className="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary" />
                    </div>
                    <div className="text-center">
                        <button type="submit" disabled={isSubmitting} className="w-full inline-flex justify-center py-2 px-6 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:bg-gray-400">
                            {isSubmitting ? 'Registrando...' : 'Registrar'}
                        </button>
                    </div>
                </form>
                 <p className="text-sm text-center text-gray-600 mt-4">
                    ¿Ya tienes una cuenta? <Link to="/login" className="font-medium text-primary hover:text-primary-dark">Inicia sesión aquí</Link>
                </p>
            </div>
        </div>
    );
}
