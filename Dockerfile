FROM php:8.2-apache

# habilita extensões
RUN docker-php-ext-install pdo_mysql

# diga ao Apache qual será o ServerName (desaparece o aviso AH00558)
RUN echo "ServerName panel.phz.one" >> /etc/apache2/apache2.conf

# se sua app usa sub-pasta public, mude o DocumentRoot
# (exemplo para Laravel/Slim; remova se não precisar)
# RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|' /etc/apache2/sites-available/000-default.conf

# copie código
COPY site/ /var/www/html/

# garanta permissões
RUN chown -R www-data:www-data /var/www/html
