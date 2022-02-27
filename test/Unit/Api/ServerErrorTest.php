<?php
namespace Bigcommerce\Test\Unit\Api;

use Bigcommerce\Api\ServerError;
use PHPUnit\Framework\TestCase;

class ServerErrorTest extends TestCase
{
    public function testBehavesExactlyLikeException()
    {
        $error = new ServerError('message', 100, []);
        $this->assertSame('message', $error->getMessage());
        $this->assertSame(100, $error->getCode());
    }

    public function testServerErrorCarriesResponseHeaders()
    {
        $error = new ServerError('message', 100, ['x-bc-header' => 'abc-123']);
        $this->assertSame('message', $error->getMessage());
        $this->assertSame(100, $error->getCode());
        $this->assertSame(['x-bc-header' => 'abc-123'], $error->getResponseHeaders());
    }
}
