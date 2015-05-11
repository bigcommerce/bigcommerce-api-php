<?php
namespace Bigcommerce\Test\Unit\Api\Resources;

use Bigcommerce\Api\Resources\Brand;
use Bigcommerce\Api\Client;

class BrandTest extends ResourceTestBase
{
    public function testCreatePassesThroughToConnection()
    {
        $brand = new Brand();
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/brands', $brand->getCreateFields());

        $brand->create();
    }

    public function testUpdatePassesThroughToConnection()
    {
        $brand = new Brand((object)(array('id' => 1)));
        $this->connection->expects($this->once())
            ->method('put')
            ->with($this->basePath . '/brands/1', $brand->getUpdateFields());

        $brand->update();
    }
}
