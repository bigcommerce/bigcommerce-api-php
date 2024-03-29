<?php
namespace Bigcommerce\Test\Unit\Api\Resources;

use Bigcommerce\Api\Client;
use Bigcommerce\Api\Connection;
use PHPUnit\Framework\TestCase;

class ResourceTestBase extends TestCase
{
    /**
     * @var Connection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connection;
    protected $basePath = '';

    public function setUp(): void
    {
        $methods = ['useXml', 'failOnError', 'authenticate', 'setTimeout', 'useProxy', 'verifyPeer', 'addHeader', 'getLastError', 'get', 'post', 'head', 'put', 'delete', 'getStatus', 'getStatusMessage', 'getBody', 'getHeader', 'getHeaders', '__destruct'];
        $this->connection = $this->getMockBuilder(\Bigcommerce\Api\Connection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->basePath = Client::$api_path;
        Client::setConnection($this->connection);
    }
}
