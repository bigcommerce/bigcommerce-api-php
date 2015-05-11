<?php
namespace Bigcommerce\Test\Unit\Api\Resources;

use Bigcommerce\Api\Client;
use Bigcommerce\Api\Connection;

class ResourceTestBase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Connection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connection;
    protected $basePath = '';

    public function setUp()
    {
        $methods = array(
            'useXml',
            'failOnError',
            'authenticate',
            'setTimeout',
            'useProxy',
            'verifyPeer',
            'addHeader',
            'getLastError',
            'get',
            'post',
            'head',
            'put',
            'delete',
            'getStatus',
            'getStatusMessage',
            'getBody',
            'getHeader',
            'getHeaders',
            '__destruct'
        );
        $this->connection = $this->getMockBuilder('Bigcommerce\\Api\\Connection')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
        $this->basePath = $this->getStaticAttribute('Bigcommerce\\Api\\Client', 'api_path');
        Client::setConnection($this->connection);
    }
}
