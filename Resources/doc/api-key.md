# Api key usage

## Basics

In most simplest form you can define keys right in the config. This is useful when you collaborate between your own applications or for testing purposes:

```yaml
damax_api_auth:
    api_key:
        storage:
            app_one: '%env(API_KEY_APP_ONE)%'
            app_two: '%env(API_KEY_APP_TWO)%'
```

#### Security

Two services are registered: `damax.api_auth.api_key.user_provider` and `damax.api_auth.api_key.authenticator`. Consider below `security.yml` configuration:

```yaml
security:
    encoders:
        Symfony\Component\Security\Core\User\User: plaintext

    providers:
        damax_api:
            id: damax.api_auth.api_key.user_provider

    firewalls:
        main:
            pattern:   ^/api/
            anonymous: false
            stateless: true
            guard:     { provider: damax_api, authenticator: damax.api_auth.api_key.authenticator }

    access_control:
        - { path: ^/api/, role: ROLE_API }
```

All routes with `/api/` prefix are now guarded by API key authentication.

#### Api user

It is possible to distinguish API users if needed, e.g. in controller:

```php
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

## Storage

Api keys can be stored in _Redis_ or database accessed through _Doctrine_.

#### Redis

Store keys in _Redis_ with `api:` prefix.

```yaml
damax_api_auth:
    api_key:
        storage:
            - { type: redis, key_prefix: 'api:' }
```

Use multiple storage drivers:

```yaml
damax_api_auth:
    api_key:
        storage:
            - { type: redis, redis_client_id: snc_redis.api_cache }
            - { type: redis, key_prefix: 'api:', redis_client_id: snc_redis.default }
```

Lookup using `snc_redis.api_cache` client, if not found, then search for a key using `snc_redis.default` client.

#### Database

_Doctrine_ storage configuration:

```yaml
damax_api_auth:
    api_key:
        storage:
            - type: doctrine
              table_name: user_api_key
              fields: { key: id, identity: user_id, ttl: expires_at }
```

`ttl` field must be of type _integer_. Default `fields` values are: `key`, `identity` and `ttl`.

#### All in one

You can mix multiple storage types:

```yaml
damax_api_auth:
    api_key:
        storage:
            - type: fixed
              tokens:
                  app_one: '%env(API_KEY_APP_ONE)%'
                  app_two: '%env(API_KEY_APP_TWO)%'
            - type: redis
              key_prefix: 'api:'
            - type: doctrine
              table_name: user_api_key
              fields: { key: id, identity: user_id, ttl: expires_at }
```

Above configuration does the following:

- always grant access for `app_one` and `app_two`;
- for others search in _Redis_;
- if not found, then see if there is a match in `user_api_key` table with non-expired key.

## Console

Test given key:

```bash
$ ./bin/console damax:api-auth:storage:lookup-key <key>
```

This will go through all the configured storage types. If found, it returns the identity behind the key and ttl.

#### Add or remove

The typical scenario is to use database with caching in _Redis_, where keys are disposable by their nature when ttl reaches zero.
This is an easy way to grant a temporary access to your API without making changes in the database.

That being said, you need to define which storage is _writable_ i.e. add to or remove keys from:

```yaml
damax_api_auth:
    api_key:
        storage:
            # ...
            - { type: redis, key_prefix: 'api:', writable: true }
            # ...
```

To add a new key, please run:

```bash
$ ./bin/console damax:api-auth:storage:add-key john.doe@domain.abc 2hours
```

The new key is now available for 2 hours. Default value: `1 week`.

To remove a key:

```bash
$ ./bin/console damax:api-auth:storage:remove-key <key>
```

Only one _writable_ storage can be defined. `fixed` type can not be _writable_.

## Extractors

By default API key is expected to be found in `Authorization` header with `Token` prefix.

Example `cURL` command:

```bash
$ curl -H "Authorization: Token secret" https://domain.abc/api/run
```

To fine tune extractors to search for a key in cookie, query or header, consider the following:

```yaml
damax_api_auth:
    api_key:
        extractors:
            - { type: query, name: api_key }
            - { type: query, name: apikey }
            - { type: cookie, name: api_key }
            - { type: header, name: 'X-Auth-Token' }
            - { type: header, name: 'X-Auth', prefix: Token }
```

All the following `cURL` requests are accepted for authentication:

```bash
$ curl https://domain.abc/api/run?api_key=secret
$ curl https://domain.abc/api/run?apikey=secret
$ curl --cookie "api_key=secret" https://domain.abc/api/run
$ curl -H "X-Auth-Token: secret" https://domain.abc/api/run
$ curl -H "X-Auth: Token secret" https://domain.abc/api/run
```

## Error response

When user is not authenticated or when access is denied the error response is returned with _401_ and _403_ status codes respectively.
By default the response body is in standard _Symfony_ format:

```json
{
    "error: { "code": 401, "message": "Unathorized" }
}
```

You can customize error response by implementing [ResponseFactory](../../Security/ResponseFactory.php), register service in container and specify in config:

```yaml
damax_api_auth:
    response_factory_service_id: my_response_factory_service
```

## Custom user provider

If you want to store keys in your own way and load custom user implementation, then implement [ApiKeyUserProvider](../../Security/ApiKey/ApiKeyUserProvider.php):

```php
use Damax\Bundle\ApiAuthBundle\Security\ApiKey\ApiKeyUserProvider;
use Damax\Bundle\ApiAuthBundle\Security\ApiKey\InvalidApiKey;

class UserProvider implements ApiKeyUserProvider
{
    private $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    // ...

    public function loadUserByApiKey(string $key): UserInterface
    {
        if (null === $user = $this->repository->byApiKey($key)) {
            throw new InvalidApiKey();
        }

        return $user;
    }
}
```

Register it in container and update `security.yml` accordingly.

## Next

Read next how to [authenticate with JWT](jwt-basic.md).
