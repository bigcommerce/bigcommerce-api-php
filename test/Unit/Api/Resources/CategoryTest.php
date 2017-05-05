<?php
namespace Bigcommerce\Test\Unit\Api\Resources;

use Bigcommerce\Api\Resources\Category;
use Bigcommerce\Api\Client;

class CategoryTest extends ResourceTestBase
{
    public function testCreatePassesThroughToConnection()
    {
        $category = new Category();
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/categories', $category->getCreateFields());

        $category->create();
    }

    public function testUpdatePassesThroughToConnection()
    {
        $category = new Category((object)(array('id' => 1)));
        $this->connection->expects($this->once())
            ->method('put')
            ->with($this->basePath . '/categories/1', $category->getUpdateFields());

        $category->update();
    }

    public function testDeletePassesThroughToConnection()
    {
        $category = new Category((object)(array('id' => 1)));
        $this->connection->expects($this->once())
            ->method('delete')
            ->with($this->basePath . '/categories/1');

        $category->delete();
    }
}
