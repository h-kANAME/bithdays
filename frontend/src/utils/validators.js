/**
 * Validadores de formularios
 */

export const validators = {
  /**
   * Validar campo requerido
   */
  required: (value, fieldName = 'Este campo') => {
    if (!value || value.toString().trim() === '') {
      return `${fieldName} es requerido`;
    }
    return null;
  },

  /**
   * Validar email
   */
  email: (value) => {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(value)) {
      return 'Email inválido';
    }
    return null;
  },

  /**
   * Validar longitud mínima
   */
  minLength: (value, min, fieldName = 'Este campo') => {
    if (value.length < min) {
      return `${fieldName} debe tener al menos ${min} caracteres`;
    }
    return null;
  },

  /**
   * Validar solo letras y espacios
   */
  alpha: (value, fieldName = 'Este campo') => {
    const alphaRegex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/;
    if (!alphaRegex.test(value)) {
      return `${fieldName} solo puede contener letras`;
    }
    return null;
  },

  /**
   * Validar fecha
   */
  date: (value, fieldName = 'La fecha') => {
    const date = new Date(value);
    if (isNaN(date.getTime())) {
      return `${fieldName} no es válida`;
    }
    return null;
  },

  /**
   * Validar que la fecha no sea futura
   */
  notFutureDate: (value, fieldName = 'La fecha') => {
    const date = new Date(value);
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    if (date > today) {
      return `${fieldName} no puede ser futura`;
    }
    return null;
  }
};

/**
 * Validar formulario completo
 */
export const validateForm = (data, rules) => {
  const errors = {};

  Object.keys(rules).forEach(field => {
    const fieldRules = rules[field];
    const value = data[field];

    for (const rule of fieldRules) {
      const error = rule(value);
      if (error) {
        errors[field] = error;
        break; // Solo mostrar el primer error
      }
    }
  });

  return {
    isValid: Object.keys(errors).length === 0,
    errors
  };
};

export default validators;
