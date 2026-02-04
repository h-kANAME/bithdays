import api from './api';

const destinatariosService = {
  /**
   * Obtener todos los destinatarios con paginación y búsqueda
   */
  getAll: async (page = 1, limit = 15, search = '') => {
    try {
      const response = await api.get('/api/destinatarios', {
        params: { page, limit, search }
      });
      return response.data;
    } catch (error) {
      console.error('Error al obtener destinatarios:', error);
      return {
        success: false,
        error: 'Error al obtener destinatarios'
      };
    }
  },

  /**
   * Obtener un destinatario por ID
   */
  getById: async (id) => {
    try {
      const response = await api.get(`/api/destinatarios/${id}`);
      return response.data;
    } catch (error) {
      console.error('Error al obtener destinatario:', error);
      return {
        success: false,
        error: 'Error al obtener destinatario'
      };
    }
  },

  /**
   * Crear nuevo destinatario
   */
  create: async (data) => {
    try {
      const response = await api.post('/api/destinatarios', data);
      return response.data;
    } catch (error) {
      console.error('Error al crear destinatario:', error);
      if (error.response && error.response.data) {
        return error.response.data;
      }
      return {
        success: false,
        error: 'Error al crear destinatario'
      };
    }
  },

  /**
   * Actualizar destinatario
   */
  update: async (id, data) => {
    try {
      const response = await api.put(`/api/destinatarios/${id}`, data);
      return response.data;
    } catch (error) {
      console.error('Error al actualizar destinatario:', error);
      if (error.response && error.response.data) {
        return error.response.data;
      }
      return {
        success: false,
        error: 'Error al actualizar destinatario'
      };
    }
  },

  /**
   * Eliminar destinatario
   */
  delete: async (id) => {
    try {
      const response = await api.delete(`/api/destinatarios/${id}`);
      return response.data;
    } catch (error) {
      console.error('Error al eliminar destinatario:', error);
      if (error.response && error.response.data) {
        return error.response.data;
      }
      return {
        success: false,
        error: 'Error al eliminar destinatario'
      };
    }
  }
};

export default destinatariosService;
