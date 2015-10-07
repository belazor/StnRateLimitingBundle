<?php

namespace Stn\RateLimitingBundle\Tests\Annotation;

use Stn\RateLimitingBundle\Annotation\RateLimiting;

/**
 * RateLimiting test
 *
 * @author Santino Wu <santinowu.wsq@gmail.com>
 */
class RateLimitingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RateLimiting
     */
    private $rateLimiting;

    protected function setUp()
    {
        $this->rateLimiting = new RateLimiting(array());
    }

    protected function tearDown()
    {
        unset($this->rateLimiting);
    }

    public function testInterface()
    {
        $this->assertInstanceof(
            'Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationAnnotation',
            $this->rateLimiting
        );
    }

    public function testDefaultValue()
    {
        $this->assertNull($this->rateLimiting->getLimit());
        $this->assertNull($this->rateLimiting->getTtl());
    }

    public function testAliasName()
    {
        $this->assertSame('stn_rate_limiting', $this->rateLimiting->getAliasName());
    }

    public function testAllowArray()
    {
        $this->assertFalse($this->rateLimiting->allowArray());
    }

    /**
     * @dataProvider invalidLimitsProvider
     */
    public function testInvalidParameter($invalidParameter)
    {
        $this->rateLimiting->setLimit($invalidParameter);
        $this->assertSame(0, $this->rateLimiting->getLimit());

        $this->rateLimiting->setTtl($invalidParameter);
        $this->assertSame(0, $this->rateLimiting->getTtl());
    }

    /**
     * @dataProvider validLimitsProvider
     */
    public function testValidParameter($validParameter)
    {
        $this->rateLimiting->setLimit($validParameter);
        $this->assertSame((integer) $validParameter, $this->rateLimiting->getLimit());

        $this->rateLimiting->setTtl($validParameter);
        $this->assertSame((integer) $validParameter, $this->rateLimiting->getTtl());
    }

    public function invalidLimitsProvider()
    {
        return array(
            'Null' => array( null ),
            'Empty string' => array( '' ),
            'Float' => array( 0.001 ),
            'Numeric (Float)' => array( '0.999' ),
        );
    }

    public function validLimitsProvider()
    {
        return array(
            'Numeric (Integer)' => array( '99' ),
            'Integer' => array( PHP_INT_MAX ),
        );
    }
}
