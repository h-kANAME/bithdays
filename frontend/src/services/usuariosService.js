import api from './api';

const usuariosService = {
  /**
   * Obtener todos los usuarios con paginación y búsqueda
   */
  getAll: async (page = 1, limit = 15, search = '') => {
    try {
      const response = await api.get('/api/usuarios', {
        params: { page, limit, search }
      });
      return response.data;
    } catch (error) {
      console.error('Error al obtener usuarios:', error);
      return {
        success: false,
        error: 'Error al obtener usuarios'
      };
    }
  },

  /**
   * Obtener un usuario por ID
   */
  getById: async (id) => {
    try {
      const response = await api.get(`/api/usuarios/${id}`);
      return response.data;
    } catch (error) {
      console.error('Error al obtener usuario:', error);
      return {
        success: false,
        error: 'Error al obtener usuario'
      };
    }
  },

  /**
   * Crear nuevo usuario
   */
  create: async (data) => {
    try {
      const response = await api.post('/api/usuarios', data);
      return response.data;
    } catch (error) {
      console.error('Error al crear usuario:', error);
      if (error.response && error.response.data) {
        return error.response.data;
      }
      return {
        success: false,
        error: 'Error al crear usuario'
      };
    }
  },

  /**
   * Actualizar usuario
   */
  update: async (id, data) => {
    try {
      const response = await api.put(`/api/usuarios/${id}`, data);
      return response.data;
    } catch (error) {
      console.error('Error al actualizar usuario:', error);
      if (error.response && error.response.data) {
        return error.response.data;
      }
      return {
        success: false,
        error: 'Error al actualizar usuario'
      };
    }
  },

  /**
   * Eliminar usuario
   */
  delete: async (id) => {
    try {
      const response = await api.delete(`/api/usuarios/${id}`);
      return response.data;
    } catch (error) {
      console.error('Error al eliminar usuario:', error);
      if (error.response && error.response.data) {
        return error.response.data;
      }
      return {
        success: false,
        error: 'Error al eliminar usuario'
      };
    }
  }
};

export default usuariosService;
