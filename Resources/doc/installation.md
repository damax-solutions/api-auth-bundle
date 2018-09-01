# Installation

## Composer

Install with composer:

```bash
$ composer require damax/api-auth-bundle
```

If you plan to store api keys with _Doctrine_, then run:

```bash
$ composer require doctrine/dbal
```

In case of _Redis_:

```bash
$ composer require predis/predis # snc/redis-bundle - not required, but recommended.
```

If you need _JWT_ authentication, then run:

```bash
$ composer require lcobucci/jwt:4.*@dev # Latest 4.x version is required.
```

__Note__: In future releases different _JWT_ providers will be supported.

## Bundles

With introduction of _symfony/flex_ you don't have to worry about enabling relevant bundles, but make sure below is present in your configuration.

```php
// Symfony v4.0 example, but v3.x is also supported.
Damax\Bundle\ApiAuthBundle\DamaxApiAuthBundle::class => ['all' => true],

// For Doctrine
Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ['all' => true],

// For Redis
Snc\RedisBundle\SncRedisBundle::class => ['all' => true],
```

## Configuration

By default API key or _JWT_ authentication is turned off i.e. no configuration is needed right from the start.

## Next

Read next how to authenticate with [API keys](api-key.md).
