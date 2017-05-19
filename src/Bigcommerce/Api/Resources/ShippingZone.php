<?php

namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Client;
use Bigcommerce\Api\Resource;

class ShippingZone extends Resource
{
    protected $ignoreOnCreate = array(
        'id',
    );

    protected $ignoreOnUpdate = array(
        'id',
    );

    public function create()
    {
        return Client::createResource('/shipping/zones/', $this->getCreateFields());
    }

    public function update()
    {
        return Client::updateResource('/shipping/zones/'. $this->id, $this->getUpdateFields());
    }
}
