FROM debian
COPY . /usr/src/myapp
WORKDIR /usr/src/myapp
RUN apt update; apt install -y composer
RUN composer update
CMD [ "php", "-S", "0:8081", "-t", "public" ]
