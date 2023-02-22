FROM debian as base
RUN apt-get update; apt-get install -y curl php-curl composer
RUN curl -fsSL https://deb.nodesource.com/setup_19.x | bash -
RUN curl -sL https://dl.yarnpkg.com/debian/pubkey.gpg | gpg --dearmor | tee /usr/share/keyrings/yarnkey.gpg >/dev/null
RUN echo "deb [signed-by=/usr/share/keyrings/yarnkey.gpg] https://dl.yarnpkg.com/debian stable main" | tee /etc/apt/sources.list.d/yarn.list
RUN apt-get update; apt-get install -y nodejs yarn

FROM base
COPY . /usr/src/myapp
WORKDIR /usr/src/myapp
RUN composer update; yarn; NODE_OPTIONS=--no-experimental-fetch yarn build
ENV DATABASE_DIR /usr/src/myapp/data-mounted/
CMD [ "php", "-S", "0:8081", "-t", "public" ]
