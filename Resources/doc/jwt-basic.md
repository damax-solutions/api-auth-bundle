# Basic JWT usage

## Intro

To enable _JWT_ authentication the configuration could be as simple as:

```yaml
damax_api_auth:
    jwt: '%env(APP_SECRET)%'
```

By default the _HS256 (HMAC SHA256)_ hashing algorithm is applied. It configures _symmetric_ signer i.e. same secret is used to sign and verify a token.

#### Routing

Make sure _login_ route is present. You can use the provided controller:

```yaml
damax_api_auth_login:
    resource: '@DamaxApiAuthBundle/Controller/SecurityController.php'
    type: annotation
    prefix: /api
```

#### Security

Two services are registered: `damax.api_auth.jwt.authenticator` and `damax.api_auth.jwt.handler`. Consider below `security.yml` configuration:

```yaml
security:
    encoders:
        FOS\UserBundle\Model\UserInterface: bcrypt

    providers:
        fos_userbundle:
            id: fos_user.user_provider.username

    firewalls:
        main:
            anonymous: true
            stateless: true
            json_login:
                provider: fos_userbundle
                check_path: security_login
                success_handler: damax.api_auth.jwt.handler
                failure_handler: damax.api_auth.jwt.handler
            guard:
                provider: fos_userbundle
                authenticator: damax.api_auth.jwt.authenticator

    access_control:
        - { path: ^/api/, roles: IS_AUTHENTICATED_FULLY }
```

Above is an example of _JWT_ authentication with [FOSUserBundle](https://github.com/FriendsOfSymfony/FOSUserBundle).
