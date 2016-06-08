<?php
namespace Bigcommerce\Test\Unit\Api\Resources;

use Bigcommerce\Api\Resources\ProductImage;
use Bigcommerce\Api\Client;

class ProductImageTest extends ResourceTestBase
{
    public function testCreatePassesThroughToConnection()
    {
        $productImage = new ProductImage((object)array('product_id' => 1));
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/products/1/images', $productImage->getCreateFields());

        $productImage->create();
    }

    public function testUpdatePassesThroughToConnection()
    {
        $productImage = new ProductImage((object)(array('id' => 1, 'product_id' => 1)));
        $this->connection->expects($this->once())
            ->method('put')
            ->with($this->basePath . '/products/1/images/1', $productImage->getUpdateFields());

        $productImage->update();
    }

    public function testDeletePassesThroughToConnection()
    {
        $productImage = new ProductImage((object)(array('id' => 1, 'product_id' => 1)));
        $this->connection->expects($this->once())
            ->method('delete')
            ->with($this->basePath . '/products/1/images/1');

        $productImage->delete();
    }
}
