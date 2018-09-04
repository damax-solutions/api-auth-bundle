# Basic JWT usage

## Intro

To enable _JWT_ authentication the configuration could be as simple as:

```yaml
damax_api_auth:
    jwt: '%env(APP_SECRET)%'
```

By default the _HS256 (HMAC SHA256)_ hashing algorithm is applied. It configures _symmetric_ signer i.e. same secret is used to sign and verify a token.

#### Routing

Make sure _login_ route is present. You can use the provided controller or register your own:

```yaml
damax_api_auth_login:
    resource: '@DamaxApiAuthBundle/Controller/SecurityController.php'
    type: annotation
    prefix: /api
    defaults: { _format: json }
```

Default [SecurityController](../../Controller/SecurityController.php) requires [NelmioApiDocBundle](https://github.com/nelmio/NelmioApiDocBundle) to be installed.

#### Security

Two services are registered: `damax.api_auth.jwt.authenticator` and `damax.api_auth.jwt.handler`. Consider below `security.yml` configuration:

```yaml
security:
    encoders:
        Symfony\Component\Security\Core\User\User: argon2i

    providers:
        in_memory:
            memory:
                users:
                    admin: { password: "$argon2i$v=19$m=1024,t=2,p=2$WTJhQmtQVXVKT2RXZkZoYw$Jz5CC0x+N15FoUPv35cjU27Z1ckM6x7d8J2BULq6mEk", roles: ROLE_API }

    firewalls:
        login:
            pattern: ^/api/(login|doc)$
            stateless: true
            anonymous: true
            json_login:
                check_path: security_login
                success_handler: damax.api_auth.jwt.handler
                failure_handler: damax.api_auth.jwt.handler

        main:
            pattern: ^/api/
            stateless: true
            guard: { authenticator: damax.api_auth.jwt.authenticator }
```

Above example uses _in_memory_ provider. To encode your password, please, use:

```bash
$ ./bin/console security:encode-password --empty-salt Qwerty12
```

Test login functionality:

```bash
$ curl -X POST https://domain.abc/api/login -d '{"username": "admin", "password": "Qwerty12"}'
```

In order to access any `/api` route valid _JWT_ token is required.

## Extractors

By default _JWT_ is expected to be found in `Authorization` header with `Bearer` prefix.

Example `cURL` command:

```bash
$ curl https://domain.abc/api/endpoints -H "Authorization: Bearer jwt"
```

To fine tune extractors to search for a token in cookie, query or header, consider the following:

```yaml
damax_api_auth:
    jwt:
        signer: '%env(APP_SECRET)%'
        extractors:
            - { type: query, name: token }
            - { type: cookie, name: token }
            - { type: header, name: 'X-Auth-Token' }
            - { type: header, name: 'X-Auth', prefix: Token }
```

All the following `cURL` requests are accepted for authentication:

```bash
$ curl https://domain.abc/api/endpoints?token=jwt
$ curl --cookie "token=jwt" https://domain.abc/api/endpoints
$ curl -H "X-Auth-Token: jwt" https://domain.abc/api/endpoints
$ curl -H "X-Auth: Token jwt" https://domain.abc/api/endpoints
```

## Claims

Basic _JWT_ payload looks something like this:

```json
{
  "iat": "1536070684.622099",
  "nbf": "1536070684.622099",
  "exp": "1536074284.622099",
  "sub": "admin",
  "roles": [
    "api"
  ]
}
```

It has 3 _timestamp_ claims, subject and custom `roles` attribute extracted from _Symfony_'s user. The default `ttl` is 1 hour.

You can change `ttl` value, add `issuer` and `audience` claims:

```yaml
damax_api_auth:
    jwt:
        signer: '%env(APP_SECRET)%'
        builder:
            ttl: 86400
            issuer: Symfony
            audience: App
```

The payload now looks the following:

```json
{
  "iat": "1536074376.173030",
  "nbf": "1536074376.173030",
  "exp": "1536160776.173030",
  "iss": "Symfony",
  "aud": "App",
  "sub": "admin",
  "roles": [
    "api"
  ]
}
```

In order to validate `issuer` and `audience` in _JWT_ add the following:

```yaml
damax_api_auth:
    jwt:
        signer: '%env(APP_SECRET)%'
        parser:
            audience: App
            issuers:
                - Symfony
                - Zend
```

Parser and builder claims may be different. One application can just issue signed tokens, while other performs the work.
In above example in order _JWT_ to validate the issuer must be either `Symfony` or `Zend` and `audience` must be `App`.

You can add custom claims to the payload by implementing [Claims](../../Jwt/Claims.php) interface e.g.:

```php
namespace App\Security;

use Damax\Bundle\ApiAuthBundle\Jwt\Claims;
use Symfony\Component\Security\Core\User\UserInterface;

class IntlClaims implements Claims
{
    public function resolve(UserInterface $user): array
    {
        return [
            self::LOCALE => 'en',
            self::TIMEZONE => 'Europe/London',
        ];
    }
}
```

Then register in container:

```xml
<service class="App\Security\IntlClaims">
    <tag name="damax.api_auth.jwt_claims" />
</service>
```

Payload body is the following:

```json
{
  "locale": "en",
  "zoneinfo": "Europe/London",
  "iat": "1536076146.265026",
  "nbf": "1536076146.265026",
  "exp": "1536162546.265026",
  "iss": "Symfony",
  "aud": "App",
  "sub": "admin",
  "roles": [
    "api"
  ]
}
```

#### Identity

In order to load a user from _UserProvider_ the `sub` claim is used. Supplied _JWT_ may have different identity field.

```yaml
damax_api_auth:
    jwt:
        signer: '%env(APP_SECRET)%'
        identity_claim: email
```

Now the `email` field is used as user's identity.

## Authentication response

You can customize authentication response by implementing [ResponseFactory](../../Security/ResponseFactory.php), register service in container and specify in config: 

```yaml
damax_api_auth:
    response_factory_service_id: App\Security\JwtResponseFactory
```

Example:

```php
namespace App\Security;

use Damax\Bundle\ApiAuthBundle\Security\JsonResponseFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class JwtResponseFactory extends JsonResponseFactory
{
    public function fromToken(string $token): Response
    {
        $data = [
            'data' => ['token' => $token],
            'custom' => ['foo' => 'bar', 'baz' => 'qux'],
        ];

        return JsonResponse::create($data);
    }
}
```

See [this section](api-key.md#error-response) how to customize error response.

## Next

Read next how to [configure asymmetric JWT signer](jwt-advanced.md).
