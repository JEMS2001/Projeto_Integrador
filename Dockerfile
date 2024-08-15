# Usar a imagem oficial do PHP 8.3 com Apache
FROM php:8.3-apache

# Instalar extensões necessárias (caso precise)
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copiar o conteúdo do projeto para o diretório /var/www/html/
COPY ./ /var/www/html/

# Ajustar permissões para o diretório /var/www/html/
RUN chown -R www-data:www-data /var/www/html/ \
    && chmod -R 755 /var/www/html/

# Configurar Apache para que o arquivo home.php seja exibido na raiz
RUN echo '<VirtualHost *:80>\n\
    ServerAdmin webmaster@localhost\n\
    DocumentRoot /var/www/html\n\
    <Directory /var/www/html>\n\
        Options Indexes FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Ativar o módulo rewrite do Apache (se necessário)
RUN a2enmod rewrite

# Expor a porta 80
EXPOSE 80
