# JWT usage

To enable JWT authentication the configuration is as simple as:

```yaml
damax_api_auth:
    jwt: '%env(API_JWT_SECRET)%'
```

By default the _HS256 (HMAC SHA256)_ hashing algorithm is applied. This way of signing a token is called _symmetric_, meaning the same secret value is used to sign and verify a token.

Above configuration registers `damax.api_auth.jwt.authenticator` and `damax.api_auth.jwt.handler` services you need to include in `security.yml`:

```yaml
security:
    encoders:
        FOS\UserBundle\Model\UserInterface: bcrypt

    role_hierarchy:
        ROLE_ADMIN:       ROLE_USER
        ROLE_SUPER_ADMIN: ROLE_ADMIN

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
        - { path: ^/admin/, roles: ROLE_ADMIN }
```

This is an example of _JWT_ authentication with [FOSUserBundle](https://github.com/FriendsOfSymfony/FOSUserBundle).

To enable _security_login_ route add the following:

```yaml
# config/routes/damax_api_auth.yaml
damax_api_auth:
    resource: '@DamaxApiAuthBundle/Resources/config/routing/security.xml'
```
