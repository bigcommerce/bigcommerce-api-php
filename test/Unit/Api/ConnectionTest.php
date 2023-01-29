<?php

namespace Bigcommerce\Test\Unit\Api;

use Bigcommerce\Api\Connection;
use PHPUnit\Framework\TestCase;

class ConnectionTest extends TestCase
{
    /**
     * @var \Bigcommerce\Api\Connection;
     */
    protected $object;

    public function setUp(): void
    {
        $this->object = new Connection();
    }

    public function testAddHeader()
    {
        $this->object->addHeader('Content-Length', 4);
        $this->assertContains('Content-Length: 4', $this->object->getRequestHeaders());
    }

    /**
     * @depends testAddHeader
     */
    public function testRemoveHeader()
    {
        $this->object->addHeader('Content-Length', 4);
        $this->object->removeHeader('Content-Length');
        $this->assertNotContains('Content-Length: 4', $this->object->getRequestHeaders());
    }
}
