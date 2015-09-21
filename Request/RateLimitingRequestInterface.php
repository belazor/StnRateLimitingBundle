<?php

namespace Stn\RateLimitingBundle\Request;

/**
 * Interface for rate limiting request
 *
 * @author Santino Wu <santinowu.wsq@gmail.com>
 */
interface RateLimitingRequestInterface
{
    /**
     * Whether the rate limit of request was exceeded or not
     *
     * @return boolean
     */
    public function isExceeded();

    /**
     * Get rate limit of request
     *
     * @return integer
     */
    public function getLimit();

    /**
     * Get remaining times of rate limiting request
     *
     * @return integer
     */
    public function getRemaining();

    /**
     * Get reset timestamp
     *
     * @return integer
     */
    public function getResetAt();
}
