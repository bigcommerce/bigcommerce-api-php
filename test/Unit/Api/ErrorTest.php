<?php
namespace Bigcommerce\Test\Unit;

use Bigcommerce\Api\Error;

class ErrorTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorHandlesArrayOfMessageObjects()
    {
        $messageObj = (object)array('message' => 'message here');
        $error = new Error(array($messageObj), 0);
        $this->assertSame('message here', $error->getMessage());
    }

    public function testConstructorPassesMessageAndCodeThrough()
    {
        $error = new Error('message here', 100);
        $this->assertSame('message here', $error->getMessage());
        $this->assertSame(100, $error->getCode());
    }
}
