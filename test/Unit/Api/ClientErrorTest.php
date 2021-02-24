<?php
namespace Bigcommerce\Test\Unit\Api;

use Bigcommerce\Api\ClientError;
use PHPUnit\Framework\TestCase;

class ClientErrorTest extends TestCase
{
    public function testStringifyingReturnsTheMessageAndCodeAppropriately()
    {
        $error = new ClientError('message here', 100);
        $this->assertSame('Client Error (100): message here', (string)$error);
    }
}
