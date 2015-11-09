<?php

namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;

class Shipment extends Resource
{
    protected $ignoreOnCreate = array(
        'id',
        'order_id',
        'date_created',
        'customer_id',
        'billing_address',
        'shipping_address'
    );

    protected $ignoreOnUpdate = array(
        'id',
        'order_id',
        'date_created',
        'customer_id',
        'items',
        'billing_address',
        'shipping_address'
    );

    public function create()
    {
        return Client::createResource('/orders/' . $this->fields->order_id . '/shipments', $this->getCreateFields());
    }

    public function update()
    {
        return Client::updateResource('/orders/' . $this->fields->order_id . '/shipments/' . $this->id, $this->getUpdateFields());
    }
}
