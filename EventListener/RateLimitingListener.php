<?php

namespace Stn\RateLimitingBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Util\ClassUtils;
use Psr\Log\LoggerInterface;
use Predis\ClientInterface;

use Stn\RateLimitingBundle\Annotation\RateLimiting;
use Stn\RateLimitingBundle\Request\RateLimitingRequest;

use Stn\RateLimitingBundle\Component\ApiProblem;
use Stn\RateLimitingBundle\Component\ApiProblemResponse;

/**
 * Listener for handling rate limiting request
 *
 * @author Santino Wu <santinowu.wsq@gmail.com>
 */
class RateLimitingListener implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    const RATE_LIMITING_REQUEST = 'stn_rate_limiting_request';
    const RATE_LIMITING_ANNOTATION = 'Stn\RateLimitingBundle\Annotation\RateLimiting';

    /**
     * @var ClientInterface
     */
    private $cacheServer;

    /**
     * @var Reader
     */
    private $reader;

    /**
     * Set cache server
     *
     * @param ClientInterface $server
     */
    public function setCacheServer(ClientInterface $server)
    {
        $this->cacheServer = $server;
    }

    /**
     * Set annotation reader
     *
     * @param Reader $reader
     */
    public function setReader(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * Will be triggerred by Symfony event dispatcher when event `kernel.request` was fired.
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (true !== $event->isMasterRequest()) {
            return;
        }

        if (false !== $event->isPropagationStopped()) {
            return;
        }

        if (false === strpos($event->getRequest()->attributes->get('_controller', null), '::')) {
            return;
        }

        // Get configuration
        $configs = $this->container->getParameter('stn_rate_limiting');

        if (false === $configs['enable']) {
            return;
        }

        // Get annotation of rate limiting
        $rateLimiting = null;
        $tmp = explode('::', $event->getRequest()->attributes->get('_controller'));
        $reflClass = ClassUtils::newReflectionClass($tmp[0]);
        $reflMethod = $reflClass->getMethod($tmp[1]);

        if (null !== $rateLimiting = $this->getRateLimitingAnnotation($reflClass, $reflMethod)) {
            // Merge configuration
            $configs = $this->mergeConfiguration($configs, $rateLimiting);
        }

        $rateLimitingRequest = new RateLimitingRequest($event->getRequest(), $this->cacheServer, $configs);

        if (true === $rateLimitingRequest->isExceeded()) {
            $clientIp = $event->getRequest()->getClientIp();
            $event->setResponse($this->createRateLimitExceededResponse($clientIp));

            return;
        }

        $event->getRequest()->attributes->set(self::RATE_LIMITING_REQUEST, $rateLimitingRequest);
    }

    /**
     * Will be triggerred by Symfony event dispatcher when event `kernel.response` was fired.
     *
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (null === $rateLimitingRequest = $event->getRequest()->attributes->get(self::RATE_LIMITING_REQUEST, null)) {
            return;
        }

        // Setup rate limiting response headers
        $event->getResponse()->headers->set('X-RateLimit-Limit', $rateLimitingRequest->getLimit());
        $event->getResponse()->headers->set('X-RateLimit-Remaining', $rateLimitingRequest->getRemaining());
        $event->getResponse()->headers->set('X-RateLimit-Reset', $rateLimitingRequest->getResetAt());
    }

    /**
     * Get annotation of rate limiting, and controller method has a higher priority.
     *
     * @param \ReflectionClass  $reflClass  Reflection class
     * @param \ReflectionMethod $reflMethod Reflection method
     * @return RateLimiting
     */
    private function getRateLimitingAnnotation(\ReflectionClass $reflClass, \ReflectionMethod $reflMethod)
    {
        $rateLimiting = $this->reader->getMethodAnnotation($reflMethod, self::RATE_LIMITING_ANNOTATION);

        if (null === $rateLimiting) {
            $rateLimiting = $this->reader->getClassAnnotation($reflClass, self::RATE_LIMITING_ANNOTATION);
        }

        return $rateLimiting;
    }

    /**
     * Merge configuration
     *
     * @param array        $configs      Rate limiting configuration
     * @param RateLimiting $rateLimiting Instance of annotation RateLimiting
     * @return array
     */
    private function mergeConfiguration(array $configs, RateLimiting $rateLimiting)
    {
        if (null !== $limit = $rateLimiting->getLimit()) {
            $configs['limit'] = $limit;
        }

        if (null !== $ttl = $rateLimiting->getTtl()) {
            $configs['ttl'] = $ttl;
        }

        return $configs;
    }

    /**
     * Create a response with rate limit exceeded information
     *
     * @param string $clientIp IP address
     * @return Response
     */
    private function createRateLimitExceededResponse($clientIp)
    {
        // Generate a new response
        return ApiProblemResponse(
            new ApiProblem(429, 'Too Many Requests')
        );
    }
}
