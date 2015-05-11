<?php
namespace Bigcommerce\Test\Unit\Api\Resources;

use Bigcommerce\Api\Resources\ProductOption;
use Bigcommerce\Api\Client;

class ProductOptionTest extends ResourceTestBase
{
    public function testValuesPassesThroughToConnection()
    {
        $productOption = new ProductOption((object)array('option_id' => 1));
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/options/1')
            ->will($this->returnValue(array(array())));

        $this->assertInstanceOf('Bigcommerce\\Api\\Resources\\Option', $productOption->option);
    }
}
