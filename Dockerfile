FROM php:8.2-apache

# Enable Apache rewrite + useful PHP extensions
RUN a2enmod rewrite \
 && docker-php-ext-install pdo pdo_mysql mysqli

# Set Apache DocumentRoot to /var/www/html
ENV APACHE_DOCUMENT_ROOT=/var/www/html
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
 && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Copy custom Apache config (optional, but helps)
COPY apache.conf /etc/apache2/conf-available/z-app.conf
RUN a2enconf z-app

# Copy your app code
COPY . /var/www/html/

# Permissions (uploads folder, if you use it at runtime)
RUN mkdir -p /var/www/html/uploads \
 && chown -R www-data:www-data /var/www/html/uploads

EXPOSE 80
