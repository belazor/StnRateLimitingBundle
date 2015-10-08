<?php

namespace Stn\RateLimitingBundle\Tests\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Stn\RateLimitingBundle\DependencyInjection\StnRateLimitingExtension;

/**
 * StnRateLimitingExtension test
 *
 * @author Santino Wu <santinowu.wsq@gmail.com>
 */
class StnRateLimitingExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var StnRateLimitingExtension
     */
    private $extension;

    /**
     * @var array
     */
    private $defaultConfig;

    protected function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->extension = new StnRateLimitingExtension();
        $this->defaultConfig = array(
            'stn_rate_limiting' => array(
                'enable'     => true,
                'limit'      => 49,
                'ttl'        => 51,
                'key_prefix' => 'RL',
                'key_length' => 8
            )
        );
    }

    protected function tearDown()
    {
        unset($this->container, $this->extension);
    }

    /**
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The child node "limit" at path "stn_rate_limiting" must be configured.
     */
    public function testConfiguredWithEmptyLimit()
    {
        unset($this->defaultConfig['stn_rate_limiting']['limit']);

        $this->extension->load($this->defaultConfig, $this->container);
    }

    /**
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The child node "ttl" at path "stn_rate_limiting" must be configured.
     */
    public function testConfiguredWithEmptyTtl()
    {
        unset($this->defaultConfig['stn_rate_limiting']['ttl']);

        $this->extension->load($this->defaultConfig, $this->container);
    }

    public function testDefaultConfiguration()
    {
        $requiredConfig = array(
            'stn_rate_limiting' => array(
                'limit' => 1,
                'ttl'   => 2,
            )
        );

        $this->extension->load($requiredConfig, $this->container);

        $parameter = $this->container->getParameter('stn_rate_limiting');

        $this->assertCount(5, $parameter);
        $this->assertTrue($parameter['enable']);
        $this->assertSame('RL', $parameter['key_prefix']);
        $this->assertSame(8, $parameter['key_length']);
    }

    /**
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidTypeException
     * @dataProvider invalidParametersProvider
     */
    public function testConfiguredWithInvalidParameter($key, $value)
    {
        $this->defaultConfig['stn_rate_limiting'][ $key ] = $value;
        $this->extension->load($this->defaultConfig, $this->container);
    }

    /**
     * @dataProvider validParametersProvider
     */
    public function testConfiguredWithValidParameter($key, $value)
    {
        $this->defaultConfig['stn_rate_limiting'][ $key ] = $value;
        $this->extension->load($this->defaultConfig, $this->container);

        $parameter = $this->container->getParameter('stn_rate_limiting');

        $this->assertSame($value, $parameter[ $key ]);
    }

    /**
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @dataProvider invalidTypesProvider
     */
    public function testConfiguredWithInvalidTypes($key, $value)
    {
        $this->defaultConfig['stn_rate_limiting'][ $key ] = $value;
        $this->extension->load($this->defaultConfig, $this->container);
    }

    public function invalidParametersProvider()
    {
        return array(
            'Set "enable" to a Integer' => array( 'enable', 99 ),
            'Set "enable" to a Float' => array( 'enable', 1.23 ),
            'Set "enable" to a String' => array( 'enable', 'InvalidEnable' ),
            'Set "enable" to an Array' => array( 'enable', array() ),
            'Set "enable" to an Object' => array( 'enable', new \stdClass() ),

            'Set "limit" to Null' => array( 'limit', null ),
            'Set "limit" to a Float' => array( 'limit', 32.1 ),
            'Set "limit" to a String' => array( 'limit', 'InvaliLimit' ),
            'Set "limit" to a Boolean' => array( 'limit', true ),
            'Set "limit" to an Array' => array( 'limit', array() ),
            'Set "limit" to an Object' => array( 'limit', new \stdClass() ),

            'Set "ttl" to Null' => array( 'ttl', null ),
            'Set "ttl" to a Float' => array( 'ttl', 32.1 ),
            'Set "ttl" to a String' => array( 'ttl', 'InvaliTtl' ),
            'Set "ttl" to a Boolean' => array( 'ttl', false ),
            'Set "ttl" to an Array' => array( 'ttl', array() ),
            'Set "ttl" to an Object' => array( 'ttl', new \stdClass() ),

            'Set "key_prefix" to an Array' => array( 'key_prefix', array() ),
            'Set "key_prefix" to an Object' => array( 'key_prefix', new \stdClass() ),

            'Set "key_length" to Null' => array( 'key_length', null ),
            'Set "key_length" to a Float' => array( 'key_length', 32.1 ),
            'Set "key_length" to a String' => array( 'key_length', 'InvaliTtl' ),
            'Set "key_length" to a Boolean' => array( 'key_length', false ),
            'Set "key_length" to an Array' => array( 'key_length', array() ),
            'Set "key_length" to an Object' => array( 'key_length', new \stdClass() ),
        );
    }

    public function validParametersProvider()
    {
        return array(
            'Set "enable" to true' => array( 'enable', true ),
            'Set "enable" to false' => array( 'enable', false ),

            'Set "enable" to true' => array( 'enable', true ),
            'Set "enable" to false' => array( 'enable', false ),
        );
    }

    public function invalidTypesProvider()
    {
        return array(
            'Set "limit" to an invalid Integer' => array( 'limit', -1 ),
            'Set "ttl" to an invalid Integer' => array( 'ttl', 0 ),
            'Set "key_length" to an invalid Integer' => array( 'key_length', 1 ),
        );
    }
}
