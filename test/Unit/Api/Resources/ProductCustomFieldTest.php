<?php
namespace Bigcommerce\Test\Unit\Api\Resources;

use Bigcommerce\Api\Resources\ProductCustomField;
use Bigcommerce\Api\Client;

class ProductCustomFieldTest extends ResourceTestBase
{
    public function testCreatePassesThroughToConnection()
    {
        $this->markTestIncomplete('This currently fails for unknown reasons');
        $customfield = new ProductCustomField((object)array('product_id' => 1));
        $this->connection->expects($this->once())
            ->method('post')
            ->with('/products/1/customfields', $customfield->getCreateFields());

        $customfield->create();
    }

    public function testUpdatePassesThroughToConnection()
    {
        $this->markTestIncomplete('This currently fails for unknown reasons');
        $customfield = new ProductCustomField((object)(array('id' => 1, 'product_id' => 1)));
        $this->connection->expects($this->once())
            ->method('put')
            ->with('/products/1/customfields/1', $customfield->getUpdateFields());

        $customfield->update();
    }

    public function testDeletePassesThroughToConnection()
    {
        $customfield = new ProductCustomField((object)(array('id' => 1, 'product_id' => 1)));
        $this->connection->expects($this->once())
            ->method('delete')
            ->with('/products/1/customfields/1');

        $customfield->delete();
    }
}
