<?php
namespace Bigcommerce\Test\Unit\Api\Resources;

use Bigcommerce\Api\Resources\SkuOption;
use Bigcommerce\Api\Client;

class SkuOptionTest extends ResourceTestBase
{
    public function testCreatePassesThroughToConnection()
    {
        $skuoption = new SkuOption((object)array('id' => 1, 'sku_id' => 1, 'product_id' => 1));
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/products/1/skus/1/options', (object)array('sku_id' => 1, 'product_id' => 1));

        $skuoption->create();
    }

    public function testUpdatePassesThroughToConnection()
    {
        $skuoption = new SkuOption((object)array('id' => 1, 'sku_id' => 1, 'product_id' => 1));
        $this->connection->expects($this->once())
            ->method('put')
            ->with($this->basePath . '/products/1/skus/1/options/1', (object)array('product_id' => 1));

        $skuoption->update();
    }
}
