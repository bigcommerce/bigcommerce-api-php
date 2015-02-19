<?php
namespace Bigcommerce\Unit\Api;

use Bigcommerce\Api\NetworkError;

class NetworkErrorTest extends \PHPUnit_Framework_TestCase
{
    public function testBehavesExactlyLikeException()
    {
        $error = new NetworkError('message', 100);
        $this->assertSame('message', $error->getMessage());
        $this->assertSame(100, $error->getCode());
    }
}
