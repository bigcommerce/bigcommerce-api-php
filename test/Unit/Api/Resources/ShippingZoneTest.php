<?php

namespace Bigcommerce\Test\Unit\Api\Resources;

use Bigcommerce\Api\Client;
use Bigcommerce\Api\Resources\ShippingZone;

class ShippingZoneTest extends ResourceTestBase
{
    public function testCreateShippingZone()
    {
        $input = array(
            'name' => 'United States',
            'type' => 'country',
            'locations' => array(
                array('country_iso2' => 'US'),
            ),
        );
        $zone = new ShippingZone((object)$input);
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/shipping/zones/', (object)$input);

        $zone->create();
    }

    public function testUpdateShippingZone()
    {
        $input = array(
            'name' => 'United States',
            'type' => 'country',
            'locations' => array(
                array('country_iso2' => 'US'),
            ),
        );
        $updateResource = array_merge(array('id' => 1), $input);
        $zone = new ShippingZone((object)$updateResource);

        $this->connection->expects($this->once())
            ->method('put')
            ->with($this->basePath . '/shipping/zones/1', (object)$input);

        $zone->update();
    }

    public function testGetShippingZone()
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/shipping/zones/1');

        Client::getShippingZone(1);
    }

    public function testGetShippingZones()
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/shipping/zones/');

        Client::getShippingZones();
    }

    public function testDeleteShippingZone()
    {

        $this->connection->expects($this->once())
            ->method('delete')
            ->with($this->basePath . '/shipping/zones/1');

        Client::deleteShippingZone(1);
    }
}
