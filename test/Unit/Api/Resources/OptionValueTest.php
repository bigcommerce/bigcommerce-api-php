<?php
namespace Bigcommerce\Test\Unit\Api\Resources;

use Bigcommerce\Api\Resources\OptionValue;
use Bigcommerce\Api\Client;

class OptionValueTest extends ResourceTestBase
{
    public function testOptionPassesThroughToConnection()
    {
        $optionValue = new OptionValue((object)array('option_id' => 1));
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/options/1')
            ->will($this->returnValue(array(array())));

        $this->assertInstanceOf('Bigcommerce\\Api\\Resources\\Option', $optionValue->option);
    }

    public function testCreatePassesThroughToConnection()
    {
        $optionValue = new OptionValue((object)array('option_id' => 1));
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/options/1/values', $optionValue->getCreateFields());

        $optionValue->create();
    }

    public function testUpdatePassesThroughToConnection()
    {
        $optionValue = new OptionValue((object)array('id' => 1, 'option_id' => 1));
        $this->connection->expects($this->once())
            ->method('put')
            ->with($this->basePath . '/options/1/values/1', $optionValue->getUpdateFields());

        $optionValue->update();
    }
}
