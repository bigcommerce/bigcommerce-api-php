<?php
namespace Bigcommerce\Test\Unit\Api\Resources;

use Bigcommerce\Api\Resources\ProductOption;
use Bigcommerce\Api\Client;

class ProductOptionTest extends ResourceTestBase
{
    public function testValuesPassesThroughToConnection()
    {
        $productoption = new ProductOption((object)array('option_id' => 1));
        $this->connection->expects($this->once())
            ->method('get')
            ->with('/options/1')
            ->will($this->returnValue(array(array())));

        $this->assertInstanceOf('Bigcommerce\\Api\\Resources\\Option', $productoption->option);
    }
}
