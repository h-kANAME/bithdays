FROM php:8.1-apache

# Instalar extensiones PHP necesarias
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Instalar otras herramientas útiles
RUN apt-get update && apt-get install -y \
    curl \
    zip \
    unzip \
    git \
    && rm -rf /var/lib/apt/lists/*

# Habilitar módulos de Apache necesarios
RUN a2enmod rewrite headers

# Crear configuración de VirtualHost directamente
COPY <<EOF /etc/apache2/sites-available/000-default.conf
<VirtualHost *:80>
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/html/public

    <Directory /var/www/html/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/error.log
    CustomLog \${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOF

# Configurar directorio de trabajo
WORKDIR /var/www/html

# Copiar archivos
COPY . /var/www/html

# Permisos
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html

# Exponer puerto
EXPOSE 80

# Iniciar Apache
CMD ["apache2-foreground"]
