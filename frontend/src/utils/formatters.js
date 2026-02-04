/**
 * Formateadores de datos
 * Timezone: America/Argentina/Buenos_Aires (UTC-3)
 */

/**
 * Formatear fecha en formato DD/MM/YYYY
 */
export const formatDate = (dateString) => {
  if (!dateString) return '';

  // Parsear la fecha asumiendo que viene en formato YYYY-MM-DD (solo fecha, sin hora)
  const [year, month, day] = dateString.split('T')[0].split('-');

  return `${day}/${month}/${year}`;
};

/**
 * Formatear fecha para input type="date" (YYYY-MM-DD)
 */
export const formatDateForInput = (dateString) => {
  if (!dateString) return '';

  // Si ya viene en formato correcto (YYYY-MM-DD), retornarla directamente
  if (/^\d{4}-\d{2}-\d{2}/.test(dateString)) {
    return dateString.split('T')[0];
  }

  const date = new Date(dateString);
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');

  return `${year}-${month}-${day}`;
};

/**
 * Calcular edad desde fecha de nacimiento
 */
export const calculateAge = (birthDate) => {
  if (!birthDate) return null;

  // Usar fecha local de Buenos Aires (UTC-3)
  const today = new Date();

  // Parsear la fecha de nacimiento sin convertir timezone
  const [year, month, day] = birthDate.split('T')[0].split('-').map(Number);

  let age = today.getFullYear() - year;
  const monthDiff = today.getMonth() + 1 - month;

  if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < day)) {
    age--;
  }

  return age;
};

/**
 * Capitalizar primera letra
 */
export const capitalize = (str) => {
  if (!str) return '';
  return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
};

/**
 * Capitalizar cada palabra
 */
export const capitalizeWords = (str) => {
  if (!str) return '';
  return str
    .split(' ')
    .map(word => capitalize(word))
    .join(' ');
};

/**
 * Truncar texto
 */
export const truncate = (str, maxLength = 50) => {
  if (!str || str.length <= maxLength) return str;
  return str.substring(0, maxLength) + '...';
};

export default {
  formatDate,
  formatDateForInput,
  calculateAge,
  capitalize,
  capitalizeWords,
  truncate
};
