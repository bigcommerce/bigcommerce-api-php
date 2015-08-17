<?php
namespace Bigcommerce\Test\Unit\Api\Resources;

use Bigcommerce\Api\Resources\Sku;
use Bigcommerce\Api\Client;

class SkuTest extends ResourceTestBase
{
    public function testCreatePassesThroughToConnection()
    {
        $sku = new Sku((object)array('id' => 1, 'product_id' => 1));
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/products/1/skus', (object)array('id' => 1));

        $sku->create();
    }

    public function testUpdatePassesThroughToConnection()
    {
        $sku = new Sku((object)array('id' => 1, 'product_id' => 1));
        $this->connection->expects($this->once())
            ->method('put')
            ->with($this->basePath . '/products/1/skus/1', (object)array());

        $sku->update();
    }
}
