import api from './api';

const authService = {
  /**
   * Iniciar sesión
   */
  login: async (usuario, password) => {
    try {
      const response = await api.post('/api/auth/login', {
        usuario,
        password
      });

      if (response.data.success) {
        // Guardar token y usuario en localStorage
        localStorage.setItem('token', response.data.data.token);
        localStorage.setItem('usuario', JSON.stringify(response.data.data.usuario));
      }

      return response.data;
    } catch (error) {
      if (error.response && error.response.data) {
        return error.response.data;
      }
      return {
        success: false,
        error: 'Error de conexión con el servidor'
      };
    }
  },

  /**
   * Cerrar sesión
   */
  logout: async () => {
    try {
      await api.post('/api/auth/logout');
    } catch (error) {
      console.error('Error al cerrar sesión:', error);
    } finally {
      // Limpiar localStorage siempre
      localStorage.removeItem('token');
      localStorage.removeItem('usuario');
    }
  },

  /**
   * Verificar si el token es válido
   */
  verifyToken: async () => {
    try {
      const response = await api.post('/api/auth/verify-token');
      return response.data.success;
    } catch (error) {
      return false;
    }
  },

  /**
   * Verificar si el usuario está autenticado
   */
  isAuthenticated: () => {
    return localStorage.getItem('token') !== null;
  },

  /**
   * Obtener usuario actual
   */
  getCurrentUser: () => {
    const usuario = localStorage.getItem('usuario');
    if (!usuario) return null;

    try {
      return JSON.parse(usuario);
    } catch (error) {
      // Si el usuario está en formato incorrecto, limpiar localStorage
      console.warn('Usuario en localStorage tiene formato inválido, limpiando...');
      localStorage.removeItem('usuario');
      localStorage.removeItem('token');
      return null;
    }
  },

  /**
   * Obtener token actual
   */
  getToken: () => {
    return localStorage.getItem('token');
  }
};

export default authService;
