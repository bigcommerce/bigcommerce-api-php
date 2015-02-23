<?php
namespace Bigcommerce\Test\Unit\Api\Resources;

use Bigcommerce\Api\Resources\OptionSetOption;
use Bigcommerce\Api\Client;

class OptionSetOptionTest extends ResourceTestBase
{
    public function testOptionPassesThroughToConnection()
    {
        $optionsetoption = new OptionSetOption((object)array('option' => (object)array('resource' => '/options/1')));
        $this->connection->expects($this->once())
            ->method('get')
            ->with('/options/1')
            ->will($this->returnValue(array(array())));

        $this->assertInstanceOf('Bigcommerce\\Api\\Resources\\Option', $optionsetoption->option);
    }

    public function testCreatePassesThroughToConnection()
    {
        $optionsetoption = new OptionSetOption();
        $this->connection->expects($this->once())
            ->method('post')
            ->with('/optionsets/options', $optionsetoption->getCreateFields());

        $optionsetoption->create();
    }

    public function testUpdatePassesThroughToConnection()
    {
        $optionsetoption = new OptionSetOption((object)(array('id' => 1)));
        $this->connection->expects($this->once())
            ->method('put')
            ->with('/optionsets/options/1', $optionsetoption->getUpdateFields());

        $optionsetoption->update();
    }
}
