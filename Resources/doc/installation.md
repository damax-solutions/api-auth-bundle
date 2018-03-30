# Installation

Add `damax/api-auth-bundle` to your `composer.json`:

```bash
$ composer require lcobucci/jwt:4.*@dev damax/api-auth-bundle
```

__Note__: The library [lcobucci/jwt](https://github.com/lcobucci/jwt) will be installed automatically as soon as new stable version is released.

Register bundle in the kernel (for pre _Symfony 4.0_ applications):

```php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = [
        // ...

        new Damax\Bundle\ApiAuthBundle\DamaxApiAuthBundle(),
    ];
}
```

__Note__: This step is not required if you use [symfony/flex](https://github.com/symfony/flex).
