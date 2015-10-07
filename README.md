# StnRateLimitingBundle

The bundle to manage request rate limiting for Symfony.

[![Build Status Images](https://travis-ci.org/Santino-Wu/StnRateLimitingBundle.svg)](https://travis-ci.org/Santino-Wu/StnRateLimitingBundle)

## Configuration

```
# app/config/config.yml
stn_rate_limiting:
    enable: true # Whether rate limiting is available or not
    limit:  60   # Limit of request
    ttl:    60   # Cache expiry time, second as unit (Time to live)
```

```php
// any controller
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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
