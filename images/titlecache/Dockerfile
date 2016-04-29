FROM debian:jessie

MAINTAINER Simeon Ackermann <amseon@web.de>

# Let the conatiner know that there is no tty
ENV DEBIAN_FRONTEND noninteractive
ENV DOCKERIZE_VERSION=0.2.0

# update package index
RUN apt-get clean && apt-get update

# install some basic packages
# install the nginx server with PHP, PHP-memcache, PHP-APC and PHP-Pecl
# install curl and git for saft and composer
RUN apt-get install -y \
    nginx-light \
    php5 php5-fpm php5-common php5-cli php5-dev php-pear php5-curl \
    unixodbc php5-odbc \
    php5-librdf \
    php5-memcached php5-memcache \
    php-apc \
    curl netcat git wget

# install dockerize
RUN wget https://github.com/jwilder/dockerize/releases/download/v${DOCKERIZE_VERSION}/dockerize-linux-amd64-v${DOCKERIZE_VERSION}.tar.gz
RUN tar -C /usr/local/bin -xzvf dockerize-linux-amd64-v${DOCKERIZE_VERSION}.tar.gz

# Install Mongo for PHP
RUN echo '' | pecl install mongo
RUN echo "extension=mongo.so" >> /etc/php5/fpm/php.ini

# Install redis for PHP
RUN git clone https://github.com/phpredis/phpredis.git /phpredis
RUN cd /phpredis && phpize && ./configure && make && make install
RUN echo "extension=redis.so" >> /etc/php5/fpm/php.ini

# Add virtuoso odbc dependency to me able to connecto to virtuoso
COPY libvirtodbc0_7.2_amd64.deb /
RUN dpkg -i /libvirtodbc0_7.2_amd64.deb

# add nginx config
COPY nginx.conf /etc/nginx/sites-enabled/default

# configure odbc for virtuoso
COPY odbc.ini /etc/

# prepare /var/www and add Saft-TitleCache
RUN rm -rf /var/www/*
WORKDIR /var/www/
# COPY index.php composer.json ./
# RUN mkdir ./config ./results

# install composer and update to install dependecies (Saft, Nette, Redis, ...)
# RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
# RUN composer update

# Add Saft 0.2.3 with Zend as Cache Backend hardcoded
ADD Zend-TitleCache.tar.gz /
RUN mv /Zend-TitleCache/* /var/www/

# Add startscript and start
COPY titlecache.sh /titlecache.sh

VOLUME /var/www/results

CMD ["/bin/bash", "/titlecache.sh"]

# expose the HTTP port to the outer world
EXPOSE 80
