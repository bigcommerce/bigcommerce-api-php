<?php
namespace Bigcommerce\Unit\Api;

use Bigcommerce\Api\ClientError;

class ClientErrorTest extends \PHPUnit_Framework_TestCase
{
    public function testStringifyingReturnsTheMessageAndCodeAppropriately()
    {
        $error = new ClientError('message here', 100);
        $this->assertSame('Client Error (100): message here', (string)$error);
    }
}
