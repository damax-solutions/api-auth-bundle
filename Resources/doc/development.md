# Development

Build image:

```bash
$ docker build -t damax-api-auth-bundle .
```

Install dependencies:

```bash
$ docker run --rm -v $(pwd):/app -w /app damax-api-auth-bundle composer install
```

Fix php coding standards:

```bash
$ docker run --rm -v $(pwd):/app -w /app damax-api-auth-bundle ./vendor/bin/php-cs-fixer fix
```

Running tests:

```bash
$ docker run --rm -v $(pwd):/app -w /app -e SYMFONY_PHPUNIT_VERSION=6.5 damax-api-auth-bundle ./vendor/bin/simple-phpunit
$ docker run --rm -v $(pwd):/app -w /app -e SYMFONY_PHPUNIT_VERSION=6.5 damax-api-auth-bundle ./bin/phpunit-coverage
```
