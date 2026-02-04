FROM node:18-alpine

# Configurar directorio de trabajo
WORKDIR /app

# Copiar archivos de dependencias
COPY package.json package-lock.json* ./

# Instalar dependencias
RUN npm install --legacy-peer-deps --force

# Copiar código fuente
COPY . .

# Exponer puerto
EXPOSE 3000

# Iniciar servidor de desarrollo
CMD ["npm", "start"]
