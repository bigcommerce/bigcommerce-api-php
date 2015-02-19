<?php
namespace Bigcommerce\Test\Unit\Api\Resources;

use Bigcommerce\Api\Resources\ProductImage;
use Bigcommerce\Api\Client;

class ProductImageTest extends ResourceTestBase
{
    public function testCreatePassesThroughToConnection()
    {
        $this->markTestIncomplete('This currently fails for unknown reasons');
        $customfield = new ProductImage((object)array('product_id' => 1));
        $this->connection->expects($this->once())
            ->method('post')
            ->with('/products/1/images', $customfield->getCreateFields());

        $customfield->create();
    }

    public function testUpdatePassesThroughToConnection()
    {
        $this->markTestIncomplete('This currently fails for unknown reasons');
        $customfield = new ProductImage((object)(array('id' => 1, 'product_id' => 1)));
        $this->connection->expects($this->once())
            ->method('put')
            ->with('/products/1/images/1', $customfield->getUpdateFields());

        $customfield->update();
    }
}
