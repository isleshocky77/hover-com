FROM php:7.4-cli
RUN apt-get update \
    && apt-get install -y git zip \
    && rm -rf /var/lib/apt/lists/* \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
WORKDIR /app
ADD ./ .
RUN php /usr/local/bin/composer install --no-dev

ENTRYPOINT ["./bin/console"]
CMD ["list"]
