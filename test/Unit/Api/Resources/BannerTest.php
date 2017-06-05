<?php
namespace Bigcommerce\Test\Unit\Api\Resources;

use Bigcommerce\Api\Resources\Banner;
use Bigcommerce\Api\Client;

class BannerTest extends ResourceTestBase
{
    public function testCreatePassesThroughToConnection()
    {
        $banner = new Banner();
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/banners', $banner->getCreateFields());

        $banner->create();
    }

    public function testUpdatePassesThroughToConnection()
    {
        $banner = new Banner((object)(array('id' => 1)));
        $this->connection->expects($this->once())
            ->method('put')
            ->with($this->basePath . '/banners/1', $banner->getUpdateFields());

        $banner->update();
    }

}
