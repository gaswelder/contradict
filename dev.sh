#!/bin/sh
php -S localhost:8080 -t public &
(cd app && yarn dev)
