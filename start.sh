#!/usr/bin/env bash

docker-compose down && docker-compose up --build -d
docker-compose run composer install --ignore-platform-reqs --no-interaction --no-progress --quiet 

wait ${!}

docker exec orders_delivery_apis_new_php php artisan migrate
docker exec orders_delivery_apis_new_php php artisan db:seed

docker exec orders_delivery_apis_new_php php ./vendor/phpunit/phpunit/phpunit ./tests/Unit
docker exec orders_delivery_apis_new_php php ./vendor/phpunit/phpunit/phpunit ./tests/Feature

exit 0