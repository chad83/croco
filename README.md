# Croco Crawler

This is a simple asynchronous web crawler that uses Symfony.

## Requirements

- PHP 7+
- MySQL

## Installing

Clone the repository and run `composer install`

Copy `.env` into `.env.local` and modify the `DATABASE_URL` to connect to your MySQL database.
To use the database for asynchronous messaging, set
```shell
MESSENGER_TRANSPORT_DSN=doctrine://default
```

Run the migrations:
```shell
php bin/console doctrine:migrations:migrate
```
## Running

To run the web service you can use `symfony` CLI:
```shell
symfony server:start
```

To run the asynchronous message processor:
```shell
php bin/console messenger:consume async
```

The service can also be run behind a web server like Apache or nginx.

## Usage

Access the REST API over HTTP.

To create a job, supply the site to crawl as `site` and indicate if you want to bypass caching
by supplying the boolean value `force_crawl`:
```http request
POST /run
Content-Type: application/json

{
    "site": "http://www.example.com",
    "force_crawl": false
}

```

The response will contain a `jobId` value that can be used to query for the status and
results of the crawl.

Get the status:
```http request
GET /{jobId}/status
```

The response indicates the `status` of the crawl as one of `pending`, `running`, or `finished`.

Get the results:
```http request
GET /{jobId}/results
```

The response contains information about the job and about the pages and files that were found.