ARG PHP_VERSION
FROM webdevops/php-apache:8.1

COPY . /app
RUN chmod 777 -R /app/cache

RUN printf "#!/bin/bash \
\ncd /app \
\nmkdir -p media \
\nmkdir -p media/files \
\nmkdir -p media/images \
\nmkdir -p upload \
\nmkdir -p upload/attachments \
\nmkdir -p upload/images \
\nchmod 755 -R media && chmod 755 -R upload \
\nif [ -d 'modules/pagecomposer' ]; then \
\nchmod 755 -R modules/pagecomposer/media \
\nmkdir -p modules/pagecomposer/media/js \
\nmkdir -p modules/pagecomposer/media/css \
\nchmod -R 755 modules/pagecomposer/media; \
\nfi" > /app/init.sh

ARG PHP_VERSION
RUN apt-get update \
    && apt-get -y install xvfb libfontconfig wkhtmltopdf

RUN export COMPOSER_ALLOW_SUPERUSER=1
RUN cd /app && composer install

EXPOSE 80