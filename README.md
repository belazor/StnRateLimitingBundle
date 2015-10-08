# StnRateLimitingBundle

This bundle(require [Predis](https://github.com/nrk/predis)) adds support for request rate limiting management in Symfony.

[![Build Status Images](https://travis-ci.org/Santino-Wu/StnRateLimitingBundle.svg)](https://travis-ci.org/Santino-Wu/StnRateLimitingBundle)

## Installation

### Download StnRateLimitingBundle bundle using Composer

```
$ composer require stn/rate-limiting-bundle
```

### Register StnRateLimitingBundle

```php
<?php
// app/AppKernel.php

// ...
    public function registerBundles()
    {
        $bundles = array(
            // Other bundles ...

            new Stn\RateLimitingBundle\StnRateLimitingBundle(),
        );
    }
// ...
```

### Configuration

```
# app/config/config.yml
stn_rate_limiting:
    enable: true    # Whether rate limiting is available or not
    limit:  60      # Limit of request
    ttl:    60      # Cache expiry time, second as unit (Time to live)
    key_prefix: ~   # The cache key prefix, defaults to 'RL'
    key_length: ~   # The cache key length, defaults to 8
    client:         # Configuration for Predis
        dsn:  ~     # DSN for connection, defaults to 'tcp://127.0.0.1:6379'
        pass: ~     # Redis requirepass configuration, will invoke `auth` to setup connection if provided, defaults to null
```

## Usage

Use annotation `@RateLimiting` to rate limit request, and you can setup rate limit by manual the following two parameters or use default configuration:

* `limit`
* `ttl`

```php
// any controller
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Stn\RateLimitingBundle\Annotation\RateLimiting;

/**
 * Mark with annotation `RateLimiting` to enable rate limiting.
 *
 * @RateLimiting(limit=10, ttl=60)
 */
class DefaultController extends Controller
{
    /**
     * Annotation in controller's action has a higher priority than controller itself.
     *
     * @RateLimiting(limit=2, ttl=10)
     */
    public function indexAction()
    {
        // Do something...
    }
}
```

## TODO

- [ ] Add forbidden response template
- [ ] Make redis configuration more flexible
- [ ] More tests

## License

MIT
