# Advanced JWT usage

## Symmetric signer

As stated before symmetric signer is used by default:

```yaml
damax_api_auth:
    jwt: '%env(APP_SECRET)%'
```

Above configuration is same as:

```yaml
damax_api_auth:
    jwt:
        signer:
            type: symmetric
            signing_key: '%env(APP_SECRET)%'
            algorithm: HS256
```

You can change the algorithm to `HS384` or `HS512` e.g.

```yaml
damax_api_auth:
    jwt:
        signer:
            signing_key: '%env(APP_SECRET)%'
            algorithm: HS512
```

## Asymmetric signer

First generate the relevant keys:

```bash
$ mkdir -p var/keys
$ ssh-keygen -t rsa -b 4096 -f var/keys/jwt
$ openssl rsa -in var/keys/jwt -pubout -outform PEM -out var/keys/jwt.pub
```

Update the config:

```yaml
damax_api_auth:
    jwt:
        signer:
            type: asymmetric
            signing_key: '%kernel.project_dir%/var/keys/jwt'
            verification_key: '%kernel.project_dir%/var/keys/jwt.pub'
            passphrase: '%env(JWT_PASSPHRASE)%'
```

You can omit the `passphrase` if you left it empty or if your application only validates the token.

Asymmetric algorithm is also configurable:

```yaml
damax_api_auth:
    jwt:
        signer:
            type: asymmetric
            signing_key: '%kernel.project_dir%/var/keys/jwt'
            verification_key: '%kernel.project_dir%/var/keys/jwt.pub'
            passphrase: '%env(JWT_PASSPHRASE)%'
            algorithm: RS512 # or RS384
```
