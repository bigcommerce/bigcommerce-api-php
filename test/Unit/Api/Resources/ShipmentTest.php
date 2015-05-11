<?php
namespace Bigcommerce\Test\Unit\Api\Resources;

use Bigcommerce\Api\Resources\Shipment;
use Bigcommerce\Api\Client;

class ShipmentTest extends ResourceTestBase
{
    public function testCreatePassesThroughToConnection()
    {
        $shipment = new Shipment((object)array('id' => 1, 'order_id' => 1));
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/orders/1/shipments', (object)array());

        $shipment->create();
    }

    public function testUpdatePassesThroughToConnection()
    {
        $shipment = new Shipment((object)array('id' => 1, 'order_id' => 1));
        $this->connection->expects($this->once())
            ->method('put')
            ->with($this->basePath . '/orders/1/shipments/1', (object)array());

        $shipment->update();
    }
}
