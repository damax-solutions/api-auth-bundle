# Api key usage 

You can start using API key authentication right out of the box.

When there is a fixed amount of API keys you can define the following configuration:

```yaml
damax_api_auth:
    api_key:
        app_one: '%env(API_TOKEN_APP_ONE)%'
        app_two: '%env(API_TOKEN_APP_TWO)%'
```

Above configuration registers `damax.api_auth.api_key.user_provider` security provider you need to include in `security.yml`:

```yaml
security:
    encoders:
        Symfony\Component\Security\Core\User\User: plaintext

    providers:
        damax_api:
            id: damax.api_auth.api_key.user_provider

    firewalls:
        main:
            pattern: ^/api/
            anonymous: false
            stateless: true
            guard:
                provider: damax_api
                authenticator: damax.api_auth.api_key.authenticator

    access_control:
        - { path: ^/api/, role: ROLE_API }

```

All your controllers with `/api/` prefix are now guarded by API key authentication.

You can distinguish API users if needed, e.g. in controller:

```php
<?php

// ....

/**
 * @Route("/api")
 */
class TestController extends Controller
{
    /**
     * @Route("/run")
     */
    public function runAction(): Response
    {
        $user = $this->getUser();

        if ('app_one' === $user->getUsername()) {
            $result = $this->appOneAction();
        } elseif ('app_two' === $user->getUsername()) {
            $result = $this->appTwoAction();
        } else {
            $result = $this->defaultAction();
        }

        return JsonResponse::create($result);
    }
}
```

#### Extractors

By default API key is expected to be found in `Authorization` header with `Token` prefix.

Example `cURL` command:

```bash
$ curl -H "Authorization: Token secret" https://domain.abc/api/run
```

To fine tune extractors to look up for a key in cookie, query and/or header, consider the following:

```yaml
damax_api_auth:
    api_key:
        tokens:
            app_one: '%env(API_TOKEN_APP_ONE)%'
            app_two: '%env(API_TOKEN_APP_TWO)%'
        extractors:
            - type: query
              name: api_key
            - type: query
              name: apikey
            - type: cookie
              name: api_key
            - type: header
              name: 'X-Auth-Token'
            - type: header:
              name: 'X-Auth'
              prefix: Token
```

All the following `cURL` requests are accepted for authentication:

```bash
$ curl https://domain.abc/api/run?api_key=secret
$ curl https://domain.abc/api/run?apikey=secret
$ curl --cookie "api_key=secret" https://domain.abc/api/run?apikey=secret
$ curl -H "X-Auth-Token: secret" https://domain.abc/api/run?apikey=secret
$ curl -H "X-Auth: Token secret" https://domain.abc/api/run?apikey=secret
```
