<?php
namespace Bigcommerce\Unit\Api;

use Bigcommerce\Api\ServerError;
use PHPUnit\Framework\TestCase;

class ServerErrorTest extends TestCase
{
    public function testBehavesExactlyLikeException()
    {
        $error = new ServerError('message', 100);
        $this->assertSame('message', $error->getMessage());
        $this->assertSame(100, $error->getCode());
    }
}
