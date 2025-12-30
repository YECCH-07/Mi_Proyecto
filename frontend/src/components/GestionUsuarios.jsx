import { useState, useEffect } from 'react';
import axios from 'axios';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost/DENUNCIA%20CIUDADANA/backend/api';

export default function GestionUsuarios() {
    const [usuarios, setUsuarios] = useState([]);
    const [areas, setAreas] = useState([]);
    const [isLoading, setIsLoading] = useState(true);
    const [showModal, setShowModal] = useState(false);
    const [formData, setFormData] = useState({
        dni: '',
        nombres: '',
        apellidos: '',
        email: '',
        telefono: '',
        password: '',
        rol: 'operador',
        area_id: ''
    });
    const [formErrors, setFormErrors] = useState([]);

    useEffect(() => {
        fetchData();
    }, []);

    const fetchData = async () => {
        try {
            setIsLoading(true);
            const token = localStorage.getItem('jwt');

            const [usuariosRes, areasRes] = await Promise.all([
                axios.get(`${API_URL}/usuarios/read.php`, {
                    headers: { 'Authorization': `Bearer ${token}` }
                }),
                axios.get(`${API_URL}/areas/read.php`, {
                    headers: { 'Authorization': `Bearer ${token}` }
                })
            ]);

            setUsuarios(usuariosRes.data.data || []);
            setAreas(areasRes.data.records || []);
        } catch (err) {
            console.error('Error fetching data:', err);
            alert('Error al cargar datos: ' + (err.response?.data?.message || err.message));
        } finally {
            setIsLoading(false);
        }
    };

    const handleAreaChange = async (usuarioId, newAreaId) => {
        try {
            const token = localStorage.getItem('jwt');
            const payload = {
                id: usuarioId,
                area_id: newAreaId === "" ? null : parseInt(newAreaId)
            };

            await axios.put(`${API_URL}/usuarios/update.php`, payload, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                }
            });

            setUsuarios(prevUsuarios =>
                prevUsuarios.map(u =>
                    u.id === usuarioId
                        ? { ...u, area_id: payload.area_id, area_nombre: areas.find(a => a.id == newAreaId)?.nombre || null }
                        : u
                )
            );

            alert('Área asignada exitosamente');
        } catch (error) {
            console.error('Error updating usuario:', error);
            alert('Error al asignar área: ' + (error.response?.data?.message || error.message));
        }
    };

    const handleInputChange = (e) => {
        const { name, value } = e.target;
        setFormData(prev => ({
            ...prev,
            [name]: value
        }));
    };

    const resetForm = () => {
        setFormData({
            dni: '',
            nombres: '',
            apellidos: '',
            email: '',
            telefono: '',
            password: '',
            rol: 'operador',
            area_id: ''
        });
        setFormErrors([]);
    };

    const handleCreateUsuario = async (e) => {
        e.preventDefault();
        setFormErrors([]);

        try {
            const token = localStorage.getItem('jwt');
            const payload = {
                ...formData,
                area_id: formData.area_id === '' ? null : parseInt(formData.area_id)
            };

            const response = await axios.post(`${API_URL}/usuarios/create.php`, payload, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                }
            });

            if (response.data.success) {
                alert('Usuario creado exitosamente');
                setShowModal(false);
                resetForm();
                fetchData(); // Recargar la lista de usuarios
            }
        } catch (error) {
            console.error('Error creating usuario:', error);

            if (error.response?.data?.errors) {
                setFormErrors(error.response.data.errors);
            } else {
                alert('Error al crear usuario: ' + (error.response?.data?.message || error.message));
            }
        }
    };

    if (isLoading) {
        return <div className="text-center p-10">Cargando usuarios...</div>;
    }

    return (
        <div className="bg-white p-6 rounded-lg shadow-lg">
            <div className="flex justify-between items-center mb-6">
                <div>
                    <h2 className="text-2xl font-bold">Gestión de Usuarios</h2>
                    <p className="text-sm text-gray-600 mt-1">
                        Crear y administrar usuarios del sistema
                    </p>
                </div>
                <button
                    onClick={() => setShowModal(true)}
                    className="bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-md font-medium transition"
                >
                    + Crear Usuario
                </button>
            </div>

            <div className="overflow-x-auto">
                <table className="min-w-full divide-y divide-gray-200">
                    <thead className="bg-gray-50">
                        <tr>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                DNI
                            </th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Nombre Completo
                            </th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Email
                            </th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Rol
                            </th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Área Asignada
                            </th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Estado
                            </th>
                        </tr>
                    </thead>
                    <tbody className="bg-white divide-y divide-gray-200">
                        {usuarios.map(usuario => (
                            <tr key={usuario.id}>
                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {usuario.dni}
                                </td>
                                <td className="px-6 py-4 whitespace-nowrap">
                                    <div className="text-sm font-medium text-gray-900">
                                        {usuario.nombres} {usuario.apellidos}
                                    </div>
                                </td>
                                <td className="px-6 py-4 whitespace-nowrap">
                                    <div className="text-sm text-gray-500">{usuario.email}</div>
                                </td>
                                <td className="px-6 py-4 whitespace-nowrap">
                                    <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${
                                        usuario.rol === 'admin' ? 'bg-purple-100 text-purple-800' :
                                        usuario.rol === 'supervisor' ? 'bg-blue-100 text-blue-800' :
                                        usuario.rol === 'operador' ? 'bg-green-100 text-green-800' :
                                        'bg-gray-100 text-gray-800'
                                    }`}>
                                        {usuario.rol}
                                    </span>
                                </td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {usuario.rol === 'operador' || usuario.rol === 'supervisor' ? (
                                        <select
                                            value={usuario.area_id || ""}
                                            onChange={(e) => handleAreaChange(usuario.id, e.target.value)}
                                            className="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm rounded-md"
                                        >
                                            <option value="">Sin asignar</option>
                                            {areas.map(area => (
                                                <option key={area.id} value={area.id}>
                                                    {area.nombre}
                                                </option>
                                            ))}
                                        </select>
                                    ) : (
                                        <span className="text-gray-400 italic">N/A</span>
                                    )}
                                </td>
                                <td className="px-6 py-4 whitespace-nowrap">
                                    <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${
                                        usuario.activo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                    }`}>
                                        {usuario.activo ? 'Activo' : 'Inactivo'}
                                    </span>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            {usuarios.length === 0 && (
                <div className="text-center py-10 text-gray-500">
                    No hay usuarios registrados
                </div>
            )}

            {/* Modal para crear usuario */}
            {showModal && (
                <div className="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                    <div className="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
                        <div className="flex justify-between items-center mb-4">
                            <h3 className="text-xl font-bold text-gray-900">Crear Nuevo Usuario</h3>
                            <button
                                onClick={() => { setShowModal(false); resetForm(); }}
                                className="text-gray-400 hover:text-gray-600 text-2xl font-bold"
                            >
                                &times;
                            </button>
                        </div>

                        {formErrors.length > 0 && (
                            <div className="mb-4 bg-red-50 border border-red-200 rounded-md p-3">
                                <ul className="list-disc list-inside text-sm text-red-600">
                                    {formErrors.map((error, index) => (
                                        <li key={index}>{error}</li>
                                    ))}
                                </ul>
                            </div>
                        )}

                        <form onSubmit={handleCreateUsuario}>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">
                                        DNI <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        name="dni"
                                        value={formData.dni}
                                        onChange={handleInputChange}
                                        maxLength="8"
                                        required
                                        className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                                        placeholder="12345678"
                                    />
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">
                                        Rol <span className="text-red-500">*</span>
                                    </label>
                                    <select
                                        name="rol"
                                        value={formData.rol}
                                        onChange={handleInputChange}
                                        required
                                        className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                                    >
                                        <option value="operador">Operador</option>
                                        <option value="supervisor">Supervisor</option>
                                        <option value="admin">Administrador</option>
                                        <option value="ciudadano">Ciudadano</option>
                                    </select>
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">
                                        Nombres <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        name="nombres"
                                        value={formData.nombres}
                                        onChange={handleInputChange}
                                        required
                                        className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                                    />
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">
                                        Apellidos <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        name="apellidos"
                                        value={formData.apellidos}
                                        onChange={handleInputChange}
                                        required
                                        className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                                    />
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">
                                        Email <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="email"
                                        name="email"
                                        value={formData.email}
                                        onChange={handleInputChange}
                                        required
                                        className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                                    />
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">
                                        Teléfono
                                    </label>
                                    <input
                                        type="text"
                                        name="telefono"
                                        value={formData.telefono}
                                        onChange={handleInputChange}
                                        className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                                        placeholder="987654321"
                                    />
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">
                                        Contraseña <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="password"
                                        name="password"
                                        value={formData.password}
                                        onChange={handleInputChange}
                                        required
                                        minLength="6"
                                        className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                                        placeholder="Mínimo 6 caracteres"
                                    />
                                </div>

                                {(formData.rol === 'operador' || formData.rol === 'supervisor') && (
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-1">
                                            Área {formData.rol === 'operador' && <span className="text-red-500">*</span>}
                                        </label>
                                        <select
                                            name="area_id"
                                            value={formData.area_id}
                                            onChange={handleInputChange}
                                            required={formData.rol === 'operador'}
                                            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                                        >
                                            <option value="">Seleccionar área</option>
                                            {areas.map(area => (
                                                <option key={area.id} value={area.id}>
                                                    {area.nombre}
                                                </option>
                                            ))}
                                        </select>
                                    </div>
                                )}
                            </div>

                            <div className="mt-6 flex justify-end gap-3">
                                <button
                                    type="button"
                                    onClick={() => { setShowModal(false); resetForm(); }}
                                    className="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="submit"
                                    className="px-4 py-2 bg-primary text-white rounded-md hover:bg-primary-dark transition"
                                >
                                    Crear Usuario
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </div>
    );
}
