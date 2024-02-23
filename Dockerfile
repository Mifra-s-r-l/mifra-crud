# Scegli l'immagine base
FROM php:8.3-cli

# Installa le dipendenze e le estensioni PHP
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    libssl-dev \ 
    && docker-php-ext-install gd mbstring xml pdo_mysql

# Installa Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Installa l'estensione MongoDB per PHP
RUN pecl install mongodb && docker-php-ext-enable mongodb

# Mantiene il container in esecuzione
CMD ["tail", "-f", "/dev/null"]
