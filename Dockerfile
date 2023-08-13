FROM debian as base
RUN apt-get update; apt-get install -y curl php-curl composer; \
    curl -fsSL https://deb.nodesource.com/setup_19.x | bash -; \
    curl -sL https://dl.yarnpkg.com/debian/pubkey.gpg | gpg --dearmor | tee /usr/share/keyrings/yarnkey.gpg >/dev/null; \
    echo "deb [signed-by=/usr/share/keyrings/yarnkey.gpg] https://dl.yarnpkg.com/debian stable main" | tee /etc/apt/sources.list.d/yarn.list; \
    apt-get update; apt-get install -y nodejs yarn

FROM base
WORKDIR /usr/src/myapp
COPY package.json yarn.lock composer.json composer.lock ./
RUN composer update; yarn
COPY . ./
RUN NODE_OPTIONS=--no-experimental-fetch yarn build
ENV DATABASE_DIR /usr/src/myapp/data-mounted/
RUN echo "<?php" > public/index.php; echo "require '../backend/main.php';" >> public/index.php
CMD [ "php", "-S", "0:8081", "-t", "public" ]
