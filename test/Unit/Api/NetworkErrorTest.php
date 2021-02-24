<?php
namespace Bigcommerce\Test\Unit\Api;

use Bigcommerce\Api\NetworkError;
use PHPUnit\Framework\TestCase;

class NetworkErrorTest extends TestCase
{
    public function testBehavesExactlyLikeException()
    {
        $error = new NetworkError('message', 100);
        $this->assertSame('message', $error->getMessage());
        $this->assertSame(100, $error->getCode());
    }
}
