<?php

namespace Stn\RateLimitingBundle\Request;

use Symfony\Component\HttpFoundation\Request;
use Predis\ClientInterface;

/**
 * Rate limiting request
 *
 * @author Santino Wu <santinowu.wsq@gmail.com>
 */
class RateLimitingRequest implements RateLimitingRequestInterface
{
    /**
     * @var integer
     */
    private $limit;

    /**
     * @var integer
     */
    private $remaining;

    /**
     * @var integer
     */
    private $resetAt;

    /**
     * Constructor
     *
     * @param Request         $request The request
     * @param ClientInterface $client  Redis client
     * @param array           $configs
     */
    public function __construct(Request $request, ClientInterface $client, array $configs)
    {
        $cacheServer = $client;
        $cacheKey = $this->generateCacheKey($request, $configs);

        // Set expire time
        if (1 === $times = $cacheServer->incr($cacheKey)) {
            $cacheServer->expire($cacheKey, $configs['ttl']);
        }

        $timezone = new \DateTimeZone('UTC');
        $dateTime = new \DateTime('now', $timezone);

        $this->limit = (integer) $configs['limit'];
        $this->remaining = (integer) ($configs['limit'] -  $times);
        $this->resetAt = $dateTime->getTimestamp() + $cacheServer->ttl($cacheKey);
    }

    /**
     * {@inheritdoc}
     */
    public function isExceeded()
    {
        return 0 > $this->remaining;
    }

    /**
     * {@inheritdoc}
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * {@inheritdoc}
     */
    public function getRemaining()
    {
        return $this->remaining;
    }

    /**
     * {@inheritdoc}
     */
    public function getResetAt()
    {
        return $this->resetAt;
    }

    /**
     * Generate cache key with hash
     *
     * @param Request $request
     * @param array   $configs
     */
    private function generateCacheKey(Request $request, array $configs)
    {
        $clientIp = $request->getClientIp();
        $routerName = $request->attributes->get('_route');
        $prefix = isset($configs['key_prefix']) ? (string) $configs['key_prefix'] : 'RL';
        $keyLength = isset($configs['key_length']) && $configs['key_length'] > 0 ? (integer) $configs['key_length'] : 8;

        return sprintf(
            '%s:%s:count',
            $prefix,
            substr(hash('sha1', $clientIp . $routerName), 0, $keyLength)
        );
    }
}
