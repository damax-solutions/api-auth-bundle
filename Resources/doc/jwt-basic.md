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
                    admin: { password: "$argon2i$v=19$m=1024,t=2,p=2$WTJhQmtQVXVKT2RXZkZoYw$Jz5CC0x+N15FoUPv35cjU27Z1ckM6x7d8J2BULq6mEk" }

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

Above is the example for _in_memory_ provider. To encode your password, please, use:

```bash
$ ./bin/console security:encode-password --empty-salt Qwerty12
```

Test login functionality:

```bash
$ curl -X POST https://domain.abc/api/login -d '{"username": "admin", "password": "Qwerty12"}'
```

In order to access any `/api` route retrieved _JWT_ token is required.

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
