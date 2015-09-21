<?php

namespace Stn\RateLimitingBundle\Annotation;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationAnnotation;

/**
 * The annotation for configuring how rate limiting should works
 *
 * @Annotation
 * @Target({ "CLASS", "METHOD" })
 */
class RateLimiting extends ConfigurationAnnotation
{
    /**
     * @var integer
     */
    private $limit;

    /**
     * @var integer
     */
    private $ttl;

    /**
     * Set limit
     *
     * @param integer $limit
     */
    public function setLimit($limit)
    {
        $this->limit = (integer) $limit;
    }

    /**
     * Get limit
     *
     * @return integer|null
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Set time to live
     *
     * @param integer $ttl
     */
    public function setTtl($ttl)
    {
        $this->ttl = (integer) $ttl;
    }

    /**
     * Get time to live
     *
     * @return integer|null
     */
    public function getTtl()
    {
        return $this->ttl;
    }

    /**
     * {@inheritdoc}
     */
    public function getAliasName()
    {
        return 'stn_rate_limiting';
    }

    /**
     * {@inheritdoc}
     */
    public function allowArray()
    {
        return false;
    }
}
