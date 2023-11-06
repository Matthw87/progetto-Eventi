ARG PHP_VERSION
FROM webdevops/php-apache-dev:$PHP_VERSION


ARG PHP_VERSION
RUN apt-get update \
    && apt-get -y install xvfb libfontconfig wkhtmltopdf
   
EXPOSE 80
