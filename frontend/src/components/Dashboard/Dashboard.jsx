import React, { useState } from 'react';
import { Box, Typography, Grid, Card, CardContent, Button, Alert, CircularProgress } from '@mui/material';
import { People, Email, Cake, Send } from '@mui/icons-material';
import api from '../../services/api';

const Dashboard = () => {
  const [loading, setLoading] = useState(false);
  const [result, setResult] = useState(null);
  const [error, setError] = useState(null);

  const handleCheckBirthdays = async () => {
    setLoading(true);
    setError(null);
    setResult(null);

    try {
      const response = await api.get('/api/cron/check-birthdays');
      setResult(response.data);
    } catch (err) {
      setError(err.response?.data?.error || 'Error al ejecutar la verificación de cumpleaños');
    } finally {
      setLoading(false);
    }
  };

  return (
    <Box>
      <Typography variant="h4" gutterBottom>
        Dashboard
      </Typography>

      <Grid container spacing={3} sx={{ mt: 2 }}>
        <Grid item xs={12} md={4}>
          <Card>
            <CardContent>
              <Box sx={{ display: 'flex', alignItems: 'center', mb: 2 }}>
                <People color="primary" sx={{ fontSize: 40, mr: 2 }} />
                <Box>
                  <Typography variant="h5">Usuarios</Typography>
                  <Typography variant="body2" color="textSecondary">
                    Total registrados
                  </Typography>
                </Box>
              </Box>
            </CardContent>
          </Card>
        </Grid>

        <Grid item xs={12} md={4}>
          <Card>
            <CardContent>
              <Box sx={{ display: 'flex', alignItems: 'center', mb: 2 }}>
                <Email color="secondary" sx={{ fontSize: 40, mr: 2 }} />
                <Box>
                  <Typography variant="h5">Destinatarios</Typography>
                  <Typography variant="body2" color="textSecondary">
                    Emails configurados
                  </Typography>
                </Box>
              </Box>
            </CardContent>
          </Card>
        </Grid>

        <Grid item xs={12} md={4}>
          <Card>
            <CardContent>
              <Box sx={{ display: 'flex', alignItems: 'center', mb: 2 }}>
                <Cake color="success" sx={{ fontSize: 40, mr: 2 }} />
                <Box>
                  <Typography variant="h5">Cumpleaños</Typography>
                  <Typography variant="body2" color="textSecondary">
                    Próximos cumpleaños
                  </Typography>
                </Box>
              </Box>
            </CardContent>
          </Card>
        </Grid>
      </Grid>

      {/* Sección de verificación manual de cumpleaños */}
      <Box sx={{ mt: 4 }}>
        <Card>
          <CardContent>
            <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', mb: 2 }}>
              <Box>
                <Typography variant="h6" gutterBottom>
                  Verificación Manual de Cumpleaños
                </Typography>
                <Typography variant="body2" color="textSecondary">
                  Ejecuta manualmente la verificación de cumpleaños del día y envío de emails
                </Typography>
              </Box>
              <Button
                variant="contained"
                color="primary"
                startIcon={loading ? <CircularProgress size={20} color="inherit" /> : <Send />}
                onClick={handleCheckBirthdays}
                disabled={loading}
                size="large"
              >
                {loading ? 'Ejecutando...' : 'Verificar Cumpleaños'}
              </Button>
            </Box>

            {/* Mensaje de éxito */}
            {result && (
              <Alert severity="success" sx={{ mt: 2 }}>
                <Typography variant="body2" fontWeight="bold">
                  {result.message}
                </Typography>
                {result.data && (
                  <Box sx={{ mt: 1 }}>
                    <Typography variant="caption" display="block">
                      📅 Fecha: {result.data.fecha}
                    </Typography>
                    <Typography variant="caption" display="block">
                      🎂 Cumpleaños encontrados: {result.data.cumpleaños_encontrados || result.data.cumpleanos_encontrados || 0}
                    </Typography>
                    <Typography variant="caption" display="block">
                      ✉️ Emails enviados: {result.data.emails_enviados || 0}
                    </Typography>
                    {result.data.cumpleanos && result.data.cumpleanos.length > 0 && (
                      <Box sx={{ mt: 1 }}>
                        <Typography variant="caption" fontWeight="bold">Cumpleaños del día:</Typography>
                        {result.data.cumpleanos.map((cumple, index) => (
                          <Typography key={index} variant="caption" display="block" sx={{ ml: 1 }}>
                            • {cumple.nombre} {cumple.apellido} ({cumple.edad} años)
                          </Typography>
                        ))}
                      </Box>
                    )}
                  </Box>
                )}
              </Alert>
            )}

            {/* Mensaje de error */}
            {error && (
              <Alert severity="error" sx={{ mt: 2 }}>
                {error}
              </Alert>
            )}
          </CardContent>
        </Card>
      </Box>

      <Box sx={{ mt: 4 }}>
        <Typography variant="h6" gutterBottom>
          Bienvenido al Sistema
        </Typography>
        <Typography variant="body1" paragraph>
          Este sistema permite gestionar usuarios con sus cumpleaños y destinatarios
          que recibirán notificaciones por email.
        </Typography>
        <Typography variant="body2" color="textSecondary">
          Usa el menú lateral para navegar entre las diferentes secciones.
        </Typography>
      </Box>
    </Box>
  );
};

export default Dashboard;
