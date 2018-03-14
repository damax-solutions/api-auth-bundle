# DamaxApiAuthBundle

[![Build Status](https://travis-ci.org/lakiboy/damax-api-auth-bundle.svg?branch=master)](https://travis-ci.org/lakiboy/damax-api-auth-bundle) [![Coverage Status](https://coveralls.io/repos/lakiboy/damax-api-auth-bundle/badge.svg?branch=master&service=github)](https://coveralls.io/github/lakiboy/damax-api-auth-bundle?branch=master) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/lakiboy/damax-api-auth-bundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/lakiboy/damax-api-auth-bundle/?branch=master)

## Development

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
