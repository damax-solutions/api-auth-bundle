# DamaxApiAuthBundle

[![Build Status](https://travis-ci.org/lakiboy/damax-api-auth-bundle.svg?branch=master)](https://travis-ci.org/lakiboy/damax-api-auth-bundle) [![Coverage Status](https://coveralls.io/repos/lakiboy/damax-api-auth-bundle/badge.svg?branch=master&service=github)](https://coveralls.io/github/lakiboy/damax-api-auth-bundle?branch=master) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/lakiboy/damax-api-auth-bundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/lakiboy/damax-api-auth-bundle/?branch=master)

API key or [JWT](https://jwt.io/) authentication for Symfony application.

## Features

#### Api keys

- Store keys in _Redis_, _Doctrine_ or in memory.
- Lookup in multiple storage types until found.
- Use console commands to add, remove or lookup existing keys.
- Define _TTL_ for each key i.e. grant temporary access to your API.
- Configure the chain of key extractors from cookie, query string or header.
- Finally, implement your own [ApiKeyUserProvider](Security/ApiKey/ApiKeyUserProvider.php) for custom solution.

## Documentation

Topics:

- [Installation](Resources/doc/installation.md)
- [Api key usage](Resources/doc/api-key.md)

## Contribute

If you wish to contribute take a look how to [run the code locally](Resources/doc/development.md) in Docker.
