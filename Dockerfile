# Usar a imagem oficial do PHP 8.3 com Apache
FROM php:8.3-apache

# Instalar extensões necessárias (caso precise)
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copiar o conteúdo do projeto para o diretório /var/www/html/
COPY ./ /var/www/html/

# Configurar o diretório de trabalho
WORKDIR /var/www/html/

# Configurar Apache para que o arquivo home.php seja exibido na raiz
RUN echo 'DirectoryIndex home.php' > /etc/apache2/mods-enabled/dir.conf

# Expor a porta 80
EXPOSE 80
