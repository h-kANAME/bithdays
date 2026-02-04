import React from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { ThemeProvider } from '@mui/material/styles';
import CssBaseline from '@mui/material/CssBaseline';
import { AuthProvider } from './contexts/AuthContext';
import theme from './styles/theme';
import Login from './components/Auth/Login';
import PrivateRoute from './components/Auth/PrivateRoute';
import MainLayout from './components/Layout/MainLayout';
import Dashboard from './components/Dashboard/Dashboard';
import UsuariosList from './components/Usuarios/UsuariosList';
import DestinatariosList from './components/Destinatarios/DestinatariosList';

function App() {
  // Usar basename solo en producción
  const basename = process.env.NODE_ENV === 'production' ? '/birthdays' : '';

  return (
    <ThemeProvider theme={theme}>
      <CssBaseline />
      <AuthProvider>
        <BrowserRouter basename={basename}>
          <Routes>
            <Route path="/login" element={<Login />} />
            <Route path="/" element={<Navigate to="/dashboard" replace />} />

            <Route
              path="/dashboard"
              element={
                <PrivateRoute>
                  <MainLayout>
                    <Dashboard />
                  </MainLayout>
                </PrivateRoute>
              }
            />

            <Route
              path="/usuarios"
              element={
                <PrivateRoute>
                  <MainLayout>
                    <UsuariosList />
                  </MainLayout>
                </PrivateRoute>
              }
            />

            <Route
              path="/destinatarios"
              element={
                <PrivateRoute>
                  <MainLayout>
                    <DestinatariosList />
                  </MainLayout>
                </PrivateRoute>
              }
            />
          </Routes>
        </BrowserRouter>
      </AuthProvider>
    </ThemeProvider>
  );
}

export default App;
