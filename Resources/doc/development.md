# Development

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
$ docker run --rm -v $(pwd):/app -w /app damax-api-auth-bundle composer cs
```

Running tests:

```bash
$ docker run --rm -v $(pwd):/app -w /app damax-api-auth-bundle composer test
$ docker run --rm -v $(pwd):/app -w /app damax-api-auth-bundle composer test-cc
```
