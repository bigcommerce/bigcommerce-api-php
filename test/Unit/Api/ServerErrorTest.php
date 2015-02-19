<?php
namespace Bigcommerce\Unit\Api;

use Bigcommerce\Api\ServerError;

class ServerErrorTest extends \PHPUnit_Framework_TestCase
{
    public function testBehavesExactlyLikeException()
    {
        $error = new ServerError('message', 100);
        $this->assertSame('message', $error->getMessage());
        $this->assertSame(100, $error->getCode());
    }
}
