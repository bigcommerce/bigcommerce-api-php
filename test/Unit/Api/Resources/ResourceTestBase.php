<?php
namespace Bigcommerce\Test\Unit\Api\Resources;

use Bigcommerce\Api\Client;

class ResourceTestBase extends \PHPUnit_Framework_TestCase
{
    protected $connection;

    public function setUp()
    {
        $methods = array(
            'useXml',
            'failOnError',
            'authenticate',
            'setTimeout',
            'useProxy',
            'verifyPeer',
            'setCipher',
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
        Client::setConnection($this->connection);
    }
}