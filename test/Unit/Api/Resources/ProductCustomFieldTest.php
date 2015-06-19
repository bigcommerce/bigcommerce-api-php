<?php
namespace Bigcommerce\Test\Unit\Api\Resources;

use Bigcommerce\Api\Resources\ProductCustomField;
use Bigcommerce\Api\Client;

class ProductCustomFieldTest extends ResourceTestBase
{
    public function testCreatePassesThroughToConnection()
    {
        $customField = new ProductCustomField((object)array('product_id' => 1));
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/products/1/customfields', $customField->getCreateFields());

        $customField->create();
    }

    public function testUpdatePassesThroughToConnection()
    {
        $customField = new ProductCustomField((object)(array('id' => 1, 'product_id' => 1)));
        $this->connection->expects($this->once())
            ->method('put')
            ->with($this->basePath . '/products/1/customfields/1', $customField->getUpdateFields());

        $customField->update();
    }

    public function testDeletePassesThroughToConnection()
    {
        $customField = new ProductCustomField((object)(array('id' => 1, 'product_id' => 1)));
        $this->connection->expects($this->once())
            ->method('delete')
            ->with($this->basePath . '/products/1/customfields/1');

        $customField->delete();
    }
}
