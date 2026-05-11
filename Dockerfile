# Usamos una imagen oficial de PHP con Apache
FROM php:8.2-apache

# Instalamos dependencias del sistema y extensiones de PHP necesarias para Yii2
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    && docker-php-ext-install pdo pdo_mysql zip

# Habilitamos mod_rewrite para las URLs amigables
RUN a2enmod rewrite

# Copiamos los archivos de tu proyecto al contenedor
COPY . /var/www/html/

# Damos permisos a la carpeta para que Apache pueda leer/escribir (importante para assets y runtime en Yii)
RUN chown -R www-data:www-data /var/www/html/web/assets \
    && chown -R www-data:www-data /var/www/html/runtime

# Exponemos el puerto 80
EXPOSE 80