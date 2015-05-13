<?php

namespace Bigcommerce\Test\Unit;

use Bigcommerce\Api\Connection;

class ConnectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Bigcommerce\Api\Connection;
     */
    protected $object;

    public function setUp()
    {
        $this->object = new Connection();
    }

    public function testFailOnError()
    {
        $this->object->failOnError(false);
        $this->assertAttributeSame(false, 'failOnError', $this->object);
        $this->object->failOnError(true);
        $this->assertAttributeSame(true, 'failOnError', $this->object);
    }

    public function testAddHeader()
    {
        $this->object->addHeader('Content-Length', 4);
        $this->assertAttributeContains('Content-Length: 4', 'headers', $this->object);
    }

    /**
     * @depends testAddHeader
     */
    public function testRemoveHeader()
    {
        $this->object->addHeader('Content-Length', 4);
        $this->object->removeHeader('Content-Length');
        $this->assertAttributeNotContains('Content-Length: 4', 'headers', $this->object);
    }
}
