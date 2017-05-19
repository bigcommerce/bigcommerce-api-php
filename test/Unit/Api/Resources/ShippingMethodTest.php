<?php

namespace Bigcommerce\Test\Unit\Api\Resources;

use Bigcommerce\Api\Client;
use Bigcommerce\Api\Resources\ShippingMethod;

class ShippingMethodTest extends ResourceTestBase
{
    public function testCreateShippingMethod()
    {
        $input = array(
            "name" => "USPS Endicia",
            "type" => "endicia",
            "settings" => array(
                "carrier_options" => array(
                    "delivery_services" => array(
                        "PriorityExpress",
                        "PriorityMailExpressInternational",
                        "FirstClassPackageInternationalService",
                        "Priority",
                        "PriorityMailInternational",
                        "First",
                        "ParcelSelect",
                        "MediaMail"
                    ),
                    "packaging" => array(
                        "FlatRateLegalEnvelope",
                        "FlatRatePaddedEnvelope",
                        "Parcel",
                        "SmallFlatRateBox",
                        "MediumFlatRateBox",
                        "LargeFlatRateBox",
                        "FlatRateEnvelope",
                        "RegionalRateBoxA",
                        "RegionalRateBoxB"
                    ),
                    "show_transit_time" => true,
                )
            ),
            "enabled" => true
        );
        $method = new ShippingMethod((object)$input);
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/shipping/zones/1/methods', (object)$input);

        $method->create(1);
    }

    public function testUpdateShippingMethod()
    {
        $input = array(
            "name" => "Ship by Weight",
            "type" => "weight",
            "settings" => array(
                "default_cost" => 1,
                "default_cost_type" => "fixed_amount",
                "range" => array(
                    array(
                        "lower_limit" => 0,
                        "upper_limit" => 20,
                        "shipping_cost" => 8
                    )
                )
            ),
            "enabled" => true
        );
        $updateResource = array_merge(array('id' => 1), $input);
        $method = new ShippingMethod((object)$updateResource);
        $this->connection->expects($this->once())
            ->method('put')
            ->with($this->basePath . '/shipping/zones/1/methods/1', (object)$input);

        $method->update(1);
    }

    public function testGetShippingMethod()
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/shipping/zones/1/methods/2');

        Client::getShippingMethod(1, 2);
    }

    public function testGetShippingMethods()
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/shipping/zones/1/methods');

        Client::getShippingMethods(1);
    }

    public function testDeleteShippingMethod()
    {

        $this->connection->expects($this->once())
            ->method('delete')
            ->with($this->basePath . '/shipping/zones/1/methods/2');

        Client::deleteShippingMethod(1, 2);
    }
}
