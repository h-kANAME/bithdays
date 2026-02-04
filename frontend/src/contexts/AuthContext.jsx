import React, { createContext, useState, useContext, useEffect } from 'react';
import authService from '../services/authService';

const AuthContext = createContext(null);

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    // Verificar si hay un usuario en localStorage al cargar
    const checkAuth = async () => {
      const isAuth = authService.isAuthenticated();
      if (isAuth) {
        const usuario = authService.getCurrentUser();

        // Confiar en el token almacenado inicialmente
        // La verificación real se hará cuando el interceptor de axios detecte un 401
        setUser(usuario);
      }
      setLoading(false);
    };

    checkAuth();
  }, []);

  const login = async (usuario, password) => {
    const result = await authService.login(usuario, password);

    if (result.success) {
      setUser(result.data.usuario);
    }

    return result;
  };

  const logout = async () => {
    await authService.logout();
    setUser(null);
  };

  const value = {
    user,
    login,
    logout,
    isAuthenticated: !!user,
    loading
  };

  return (
    <AuthContext.Provider value={value}>
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth debe ser usado dentro de un AuthProvider');
  }
  return context;
};

export default AuthContext;
