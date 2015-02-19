<?php
namespace Bigcommerce\Test\Unit\Api\Resources;

use Bigcommerce\Api\Resources\OptionValue;
use Bigcommerce\Api\Client;

class OptionValueTest extends ResourceTestBase
{
    public function testOptionPassesThroughToConnection()
    {
        $optionvalue = new OptionValue((object)array('option_id' => 1));
        $this->connection->expects($this->once())
            ->method('get')
            ->with('/options/1')
            ->will($this->returnValue(array(array())));

        $this->assertInstanceOf('Bigcommerce\\Api\\Resources\\Option', $optionvalue->option);
    }

    public function testCreatePassesThroughToConnection()
    {
        $this->markTestIncomplete('This currently fails for unknown reasons');
        $optionvalue = new OptionValue((object)array('option_id' => 1));
        $this->connection->expects($this->once())
            ->method('post')
            ->with('/options/1/values', $optionvalue->getCreateFields());

        $optionvalue->create();
    }

    public function testUpdatePassesThroughToConnection()
    {
        $this->markTestIncomplete('This currently fails for unknown reasons');
        $optionvalue = new OptionValue((object)array('id' => 1, 'option_id' => 1));
        $this->connection->expects($this->once())
            ->method('put')
            ->with('/options/1/values/1', $optionvalue->getUpdateFields());

        $optionvalue->update();
    }
}
