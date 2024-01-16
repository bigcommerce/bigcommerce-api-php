<?php
namespace Bigcommerce\Test\Unit\Api\Resources;

use Bigcommerce\Api\Resources\Option;
use Bigcommerce\Api\Client;

class OptionTest extends ResourceTestBase
{
    public function testValuesPassesThroughToConnection()
    {
        $option = new Option((object)['values' => (object)['resource' => '/options/1/values']]);
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/options/1/values')
            ->will($this->returnValue([[], []]));

        foreach ($option->values as $value) {
            $this->assertInstanceOf(\Bigcommerce\Api\Resources\OptionValue::class, $value);
        }
    }
}
