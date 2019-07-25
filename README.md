# Sample solution for RESTful APIs to place/take/list orders using Laravel 5

## About

It's a sample solution to build RESTful APIs using following tech stack
- [Docker](https://www.docker.com/) as the container service to isolate the environment
- [PHP](https://php.net/) to develop backend support
- [Nginx](https://www.nginx.com/) as a web server with load balancer, mail proxy and HTTP cache features
- [MySql](https://www.mysql.com/) as the database layer
- [Laravel](https://laravel.com/) as the application framework

## How To Install & Run

1.  Clone the repo
2.  Set Google Distance Matrix API key `MAP_API_KEY` variable in `.env` (environment file) located in `./www` directory
3.  Run `./start.sh` to build docker containers (assuming docker is already installed on host machine), executing migration and PHP-Unit test cases
4.  Messages are fetched from locale file `message.php` in `./www/lang` directory

## API Documentation With Swagger

1. Swagger API docs can be accessed at URL http://localhost:8080/docs
2. Swagger JSON can be assessed at URL http://localhost:8080/swagger/swagger.json

## Code Coverage Report

1. Code coverage report can be accessed at URL http://localhost:8080/codecoverage/

## Manually Migrating Tables And Data Seeding

1. To run migrations manually use this command `docker exec orders_delivery_apis_php php artisan migrate`
2. To run data import manually use this command `docker exec orders_delivery_apis_php php artisan db:seed`

## Manually Starting The Docker And Test Cases

1. You can run `docker-compose up` from terminal
2. Server can be accessed at `http://localhost:8080`
3. Run manual testcase suite:
	- Unit Tests: `docker exec orders_delivery_apis_php php ./vendor/phpunit/phpunit/phpunit /var/www/html/tests/Unit`
	- Integration Tests: `docker exec orders_delivery_apis_php php ./vendor/phpunit/phpunit/phpunit /var/www/html/tests/Feature`

## API Reference Documentation

1. Place Order: `http://localhost:8080/orders`

    POST Method - to create new order with origin and distination

    - Header :
        - POST /orders HTTP/1.1
        - Host: localhost:8080
        - Content-Type: application/json

    - Post-Data :
    ```
         {
            "origin" :["29.15394", "75.72294"],
            "destination" :["28.4601", "77.02635"]
         }
    ```

    - Responses :
    ```
            {
              "id": 111,
              "distance": 112233,
              "status": "UNASSIGNED"
            }
    ```

        Code                    Description
        - 200                   successful operation
        - 400                   Api request denied or not responding
        - 422                   Invalid Request Parameter

2. Take Order: `http://localhost:8080/orders/:id`

    PATCH method to update status for taken.(Handled simultaneous update request from multiple users at the same time with response status 409)

    - Header :
        - PATCH /orders/44 HTTP/1.1
        - Host: localhost:8080
        - Content-Type: application/json
    - Post-Data :
    ```
         {
            "status" : "TAKEN"
         }
    ```

    - Responses :
    ```
            {
              "status": "SUCCESS"
            }
    ```

        Code                    Description
        - 200                   successful operation
        - 422                   Invalid Request Parameter
        - 409                   Order already taken
        - 417                   Invalid Order Id

3. List Orders: `http://localhost:8080/orders?page=:page&limit=:limit`

    GET Method - to fetch orders with page number and limit
    
    - Header :
        - GET /orders?page=1&limit=5 HTTP/1.1
        - Host: localhost:8080
        - Content-Type: application/json

    - Responses :

    ```
            [
              {
                "id": 1,
                "distance": 1234,
                "status": "TAKEN"
              },
              {
                "id": 2,
                "distance": 2345,
                "status": "UNASSIGNED"
              },
              {
                "id": 3,
                "distance": 3456,
                "status": "UNASSIGNED"
              },
              {
                "id": 4,
                "distance": 4567,
                "status": "UNASSIGNED"
              },
              {
                "id": 5,
                "distance": 5678,
                "status": "UNASSIGNED"
              }
            ]
    ```

        Code                    Description
        - 200                   successful operation
        - 422                   Invalid Request Parameter
        - 500                   Internal Server Error

## App Structure

**./tests**

- This folder contains integraton and unit test cases, written under `/tests/Feature` and `/tests/Unit` respectively

**./app**

- It contains all the application configuration file and controllers and models
- Migration files are written in `database/migrations` directory
	- To run manually migrations use the following command `docker exec orders_delivery_apis_php php artisan migrate`
- Dummy data seeding is performed using faker in `database/seeds` directory
	- To run manually data import use the following command `docker exec orders_delivery_apis_php php artisan db:seed`
- `OrdersApiController` contains all the APIs:
    - localhost:8080/orders - POST method to create new order with origin and destination params
    - localhost:8080/orders - PATCH method to update status for taken, also handled race condition, only 1 order can be TAKEN
    - localhost:8080/orders?page=1&limit=5 - GET url to fetch orders with page and limit

**.env**

- It contains project related configurations e.g. app-configs, Google API Key, database connection
