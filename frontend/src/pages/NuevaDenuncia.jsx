import { useState } from 'react';
import { useNavigate } from 'react-router-dom'; // Assuming you use react-router for navigation
import { denunciaService } from '../services/denunciaService';
import MapSelector from '../components/MapSelector';

// You might want to fetch these from your API later
const dummyCategorias = [
    { id: 1, nombre: 'Baches en la Vía' },
    { id: 2, nombre: 'Falta de Alumbrado Público' },
    { id: 3, nombre: 'Acumulación de Basura' },
    { id: 4, nombre: 'Señalización Defectuosa' },
    { id: 5, nombre: 'Otro' },
];

export default function NuevaDenuncia() {
    const [formData, setFormData] = useState({
        titulo: '',
        descripcion: '',
        categoria_id: '',
        latitud: null,
        longitud: null,
    });
    const [files, setFiles] = useState([]);
    const [filePreviews, setFilePreviews] = useState([]);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [error, setError] = useState(null);
    const navigate = useNavigate();
    const MAX_FILES = 10;

    const handleInputChange = (e) => {
        const { name, value } = e.target;
        setFormData(prev => ({ ...prev, [name]: value }));
    };

    const handleFileChange = (e) => {
        const selectedFiles = Array.from(e.target.files);

        // Validar número máximo de archivos
        if (files.length + selectedFiles.length > MAX_FILES) {
            setError(`Solo puedes subir un máximo de ${MAX_FILES} fotos`);
            return;
        }

        // Validar tipo de archivo (solo imágenes)
        const validFiles = selectedFiles.filter(file => {
            if (!file.type.startsWith('image/')) {
                setError(`El archivo ${file.name} no es una imagen válida`);
                return false;
            }
            return true;
        });

        // Crear previews para las nuevas imágenes
        const newPreviews = validFiles.map(file => ({
            file,
            preview: URL.createObjectURL(file),
            name: file.name
        }));

        setFiles(prev => [...prev, ...validFiles]);
        setFilePreviews(prev => [...prev, ...newPreviews]);

        // Limpiar el input para permitir seleccionar los mismos archivos de nuevo si es necesario
        e.target.value = '';
    };

    const handleRemoveFile = (index) => {
        // Revocar la URL del preview para liberar memoria
        URL.revokeObjectURL(filePreviews[index].preview);

        setFiles(prev => prev.filter((_, i) => i !== index));
        setFilePreviews(prev => prev.filter((_, i) => i !== index));
    };

    const handleLocationSelect = (location) => {
        setFormData(prev => ({
            ...prev,
            latitud: location.lat,
            longitud: location.lng
        }));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError(null);

        // Basic validation
        if (!formData.titulo || !formData.descripcion || !formData.categoria_id || !formData.latitud) {
            setError('Por favor, completa todos los campos obligatorios y selecciona una ubicación en el mapa.');
            return;
        }

        setIsSubmitting(true);
        try {
            // Step 1: Create the denuncia record
            const newDenuncia = await denunciaService.createDenuncia(formData);

            // Step 2: Upload all files if any
            if (files.length > 0 && newDenuncia.id) {
                const uploadPromises = files.map(file =>
                    denunciaService.uploadEvidencia(file, newDenuncia.id)
                );

                await Promise.all(uploadPromises);
            }

            // Step 3: Limpiar previews
            filePreviews.forEach(preview => URL.revokeObjectURL(preview.preview));

            // Step 4: Redirect on success
            alert(`¡Denuncia registrada con éxito! Código: ${newDenuncia.codigo}\n${files.length > 0 ? `${files.length} foto(s) adjuntada(s)` : ''}`);
            navigate('/'); // Redirect to home or a "my denuncias" page

        } catch (err) {
            setError(err.message || 'Ocurrió un error al registrar la denuncia. Por favor, intenta de nuevo.');
        } finally {
            setIsSubmitting(false);
        }
    };

    return (
        <div className="container mx-auto p-4 max-w-3xl">
            <div className="bg-white p-8 rounded-lg shadow-lg">
                <h1 className="text-3xl font-bold text-primary mb-6">Registrar Nueva Denuncia</h1>
                
                {error && (
                    <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <strong className="font-bold">Error: </strong>
                        <span className="block sm:inline">{error}</span>
                    </div>
                )}

                <form onSubmit={handleSubmit} noValidate>
                    <div className="mb-4">
                        <label htmlFor="titulo" className="block text-sm font-medium text-gray-700">Título de la denuncia</label>
                        <input
                            type="text"
                            name="titulo"
                            id="titulo"
                            value={formData.titulo}
                            onChange={handleInputChange}
                            className="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary"
                            required
                        />
                    </div>

                    <div className="mb-4">
                        <label htmlFor="descripcion" className="block text-sm font-medium text-gray-700">Describe el problema</label>
                        <textarea
                            name="descripcion"
                            id="descripcion"
                            rows="4"
                            value={formData.descripcion}
                            onChange={handleInputChange}
                            className="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary"
                            required
                        ></textarea>
                    </div>

                    <div className="mb-4">
                        <label htmlFor="categoria_id" className="block text-sm font-medium text-gray-700">Categoría</label>
                        <select
                            name="categoria_id"
                            id="categoria_id"
                            value={formData.categoria_id}
                            onChange={handleInputChange}
                            className="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary"
                            required
                        >
                            <option value="">Selecciona una categoría...</option>
                            {dummyCategorias.map(cat => (
                                <option key={cat.id} value={cat.id}>{cat.nombre}</option>
                            ))}
                        </select>
                    </div>

                    <MapSelector onLocationSelect={handleLocationSelect} />

                    <div className="mb-6">
                        <label htmlFor="file" className="block text-sm font-medium text-gray-700 mb-2">
                            Adjuntar evidencias fotográficas (opcional - máximo {MAX_FILES} fotos)
                        </label>
                        <input
                            type="file"
                            name="file"
                            id="file"
                            multiple
                            accept="image/*"
                            onChange={handleFileChange}
                            className="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-light file:text-primary hover:file:bg-red-100"
                            disabled={files.length >= MAX_FILES}
                        />
                        <p className="mt-1 text-xs text-gray-500">
                            {files.length > 0 ? `${files.length} foto(s) seleccionada(s)` : 'Selecciona una o más imágenes'}
                        </p>

                        {/* Preview de las imágenes */}
                        {filePreviews.length > 0 && (
                            <div className="mt-4 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                                {filePreviews.map((preview, index) => (
                                    <div key={index} className="relative group">
                                        <img
                                            src={preview.preview}
                                            alt={`Preview ${index + 1}`}
                                            className="w-full h-32 object-cover rounded-lg border-2 border-gray-200"
                                        />
                                        <button
                                            type="button"
                                            onClick={() => handleRemoveFile(index)}
                                            className="absolute top-1 right-1 bg-red-600 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200 hover:bg-red-700"
                                            title="Eliminar imagen"
                                        >
                                            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                        <p className="text-xs text-gray-600 mt-1 truncate" title={preview.name}>
                                            {preview.name}
                                        </p>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>

                    <div className="text-right">
                        <button
                            type="submit"
                            disabled={isSubmitting}
                            className="inline-flex justify-center py-2 px-6 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:bg-gray-400"
                        >
                            {isSubmitting ? 'Registrando...' : 'Registrar Denuncia'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}
