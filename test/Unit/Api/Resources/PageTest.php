<?php
namespace Bigcommerce\Test\Unit\Api\Resources;

use Bigcommerce\Api\Resources\Page;
use Bigcommerce\Api\Client;

class PageTest extends ResourceTestBase
{
    public function testGetAllPassesThroughToConnection()
    {
        $page = new Page();

        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/pages');

        $page->getAll();
    }

    public function testGetPassesThroughToConnection()
    {
        $page = new Page((object)(array('id' => 1)));

        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/pages/1');

        $page->get();
    }

    public function testCreatePassesThroughToConnection()
    {
        $page = new Page();
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/pages', $page->getCreateFields());

        $page->create();
    }

    public function testUpdatePassesThroughToConnection()
    {
        $page = new Page((object)(array('id' => 1)));
        $this->connection->expects($this->once())
            ->method('put')
            ->with($this->basePath . '/pages/1', $page->getUpdateFields());

        $page->update();
    }

    public function testDeletePassesThroughToConnection()
    {
        $page = new Page((object)(array('id' => 1)));
        $this->connection->expects($this->once())
            ->method('delete')
            ->with($this->basePath . '/pages/1');

        $page->delete();
    }

}
