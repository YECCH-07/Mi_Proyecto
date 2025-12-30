import axios from 'axios';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost/DENUNCIA%20CIUDADANA/backend/api';

// Function to get the JWT from local storage (or wherever you store it)
const getAuthToken = () => {
    return localStorage.getItem('jwt');
};

// Create an axios instance with default headers
const apiClient = axios.create({
    baseURL: API_URL,
    headers: {
        'Content-Type': 'application/json',
    },
});

// Add a request interceptor to include the token in every request
apiClient.interceptors.request.use(
    config => {
        const token = getAuthToken();
        console.log('[Interceptor] Token encontrado:', token ? 'SÍ' : 'NO');
        if (token) {
            config.headers['Authorization'] = `Bearer ${token}`;
            console.log('[Interceptor] Header Authorization agregado');
        } else {
            console.warn('[Interceptor] No hay token en localStorage');
        }
        return config;
    },
    error => {
        console.error('[Interceptor Request Error]', error);
        return Promise.reject(error);
    }
);

// Add a response interceptor to handle errors
apiClient.interceptors.response.use(
    response => {
        console.log('[Interceptor Response] Success:', response.config.url);
        return response;
    },
    error => {
        console.error('[Interceptor Response Error]', {
            url: error.config?.url,
            status: error.response?.status,
            message: error.response?.data?.message,
            fullError: error.response?.data
        });

        // If 401, token might be invalid or expired
        if (error.response?.status === 401) {
            console.warn('[Interceptor] 401 detectado - Token inválido o expirado');
            // Optionally, you could redirect to login here
            // window.location.href = '/login';
        }

        return Promise.reject(error);
    }
);


// Service methods
const createDenuncia = async (denunciaData) => {
    try {
        const response = await apiClient.post('/denuncias/create.php', denunciaData);
        return response.data;
    } catch (error) {
        console.error("Error creating denuncia:", error.response?.data || error.message);
        throw error.response?.data || error;
    }
};

const uploadEvidencia = async (file, denunciaId) => {
    const formData = new FormData();
    formData.append('file', file);
    formData.append('denuncia_id', denunciaId);

    try {
        const response = await axios.post(`${API_URL}/archivos/upload.php`, formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
                'Authorization': `Bearer ${getAuthToken()}`
            },
        });
        return response.data;
    } catch (error) {
        console.error("Error uploading file:", error.response?.data || error.message);
        throw error.response?.data || error;
    }
};

const getDenuncias = async () => {
    try {
        const response = await apiClient.get('/denuncias/read.php');
        return response.data;
    } catch (error) {
        console.error("Error fetching denuncias:", error.response?.data || error.message);
        throw error.response?.data || error;
    }
};

const getDenunciaByCodigo = async (codigo) => {
    try {
        const response = await apiClient.get(`/denuncias/read.php?codigo=${codigo}`);
        return response.data;
    } catch (error) {
        console.error(`Error fetching denuncia with code ${codigo}:`, error.response?.data || error.message);
        throw error.response?.data || error;
    }
};

const getDenunciaById = async (id) => {
    try {
        const response = await apiClient.get(`/denuncias/read.php?id=${id}`);
        return response.data;
    } catch (error) {
        console.error(`Error fetching denuncia with id ${id}:`, error.response?.data || error.message);
        throw error.response?.data || error;
    }
};

const getSeguimiento = async (denunciaId) => {
    try {
        const response = await apiClient.get(`/seguimiento/read.php?denuncia_id=${denunciaId}`);
        return response.data;
    } catch (error) {
        console.error(`Error fetching seguimiento for denuncia ${denunciaId}:`, error.response?.data || error.message);
        throw error.response?.data || error;
    }
};

const updateDenuncia = async (denunciaData) => {
    try {
        const response = await apiClient.put('/denuncias/update.php', denunciaData);
        return response.data;
    } catch (error) {
        console.error("Error updating denuncia:", error.response?.data || error.message);
        throw error.response?.data || error;
    }
};

const getCategorias = async () => {
    try {
        const response = await apiClient.get('/categorias/read.php');
        return response.data;
    } catch (error) {
        console.error("Error fetching categories:", error.response?.data || error.message);
        throw error.response?.data || error;
    }
};

const getAreas = async () => {
    try {
        const response = await apiClient.get('/areas/read.php');
        return response.data;
    } catch (error) {
        console.error("Error fetching areas:", error.response?.data || error.message);
        throw error.response?.data || error;
    }
};

const getDenunciasLocations = async () => {
    try {
        const response = await apiClient.get('/denuncias/locations.php');
        return response.data;
    } catch (error) {
        console.error("Error fetching denuncia locations:", error.response?.data || error.message);
        throw error.response?.data || error;
    }
};

const deleteDenuncia = async (denunciaId) => {
    try {
        const response = await apiClient.delete('/denuncias/delete.php', {
            data: { id: denunciaId }
        });
        return response.data;
    } catch (error) {
        console.error(`Error deleting denuncia ${denunciaId}:`, error.response?.data || error.message);
        throw error.response?.data || error;
    }
};


const getStatsByStatus = async () => {
    try {
        const response = await apiClient.get('/estadisticas/denuncias_por_estado.php');
        return response.data;
    } catch (error) {
        console.error("Error fetching stats by status:", error.response?.data || error.message);
        throw error.response?.data || error;
    }
};

const getStatsByCategory = async () => {
    try {
        const response = await apiClient.get('/estadisticas/denuncias_por_categoria.php');
        return response.data;
    } catch (error) {
        console.error("Error fetching stats by category:", error.response?.data || error.message);
        throw error.response?.data || error;
    }
};


export const denunciaService = {
    createDenuncia,
    uploadEvidencia,
    getDenuncias,
    getDenunciaByCodigo,
    getDenunciaById,
    getSeguimiento,
    updateDenuncia,
    deleteDenuncia,
    getCategorias,
    getAreas,
    getDenunciasLocations,
    getStatsByStatus,
    getStatsByCategory,
};
