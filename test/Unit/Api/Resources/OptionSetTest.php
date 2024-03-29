<?php
namespace Bigcommerce\Test\Unit\Api\Resources;

use Bigcommerce\Api\Resources\OptionSet;
use Bigcommerce\Api\Client;

class OptionSetTest extends ResourceTestBase
{
    public function testOptionsPassesThroughToConnection()
    {
        $optionset = new OptionSet((object)['options' => (object)['resource' => '/optionsets/1/options']]);
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/optionsets/1/options')
            ->will($this->returnValue([[], []]));

        foreach ($optionset->options as $value) {
            $this->assertInstanceOf(\Bigcommerce\Api\Resources\OptionSetOption::class, $value);
        }
    }

    public function testCreatePassesThroughToConnection()
    {
        $optionset = new OptionSet();
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/optionsets', $optionset->getCreateFields());

        $optionset->create();
    }

    public function testUpdatePassesThroughToConnection()
    {
        $optionset = new OptionSet((object)['id' => 1]);
        $this->connection->expects($this->once())
            ->method('put')
            ->with($this->basePath . '/optionsets/1', $optionset->getUpdateFields());

        $optionset->update();
    }
}
