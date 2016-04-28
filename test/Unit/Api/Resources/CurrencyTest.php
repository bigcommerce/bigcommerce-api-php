<?php
namespace Bigcommerce\Test\Unit\Api\Resources;

use Bigcommerce\Api\Resources\Currency;
use Bigcommerce\Api\Client;

class CurrencyTest extends ResourceTestBase
{
    public function testCreatePassesThroughToConnection()
    {
        $currency = new Currency((object)array('id' => 1));
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/currencies', (object)array('id' => 1));

        $currency->create();
    }

    public function testUpdatePassesThroughToConnection()
    {
        $currency = new Currency((object)array('id' => 1));
        $this->connection->expects($this->once())
            ->method('put')
            ->with($this->basePath . '/currencies/1', (object)array());

        $currency->update();
    }

    public function testDeletePassesThroughToConnection()
    {
        $currency = new Currency((object)array('id' => 1));
        $this->connection->expects($this->once())
            ->method('delete')
            ->with($this->basePath . '/currencies/1');

        $currency->delete();
    }
}
