<?php

namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Client;
use Bigcommerce\Api\Resource;

class ShippingMethod extends Resource
{
    protected $ignoreOnCreate = array(
        'id',
    );

    protected $ignoreOnUpdate = array(
        'id',
    );

    public function create($zoneId)
    {
        return Client::createResource('/shipping/zones/' . $zoneId . '/methods', $this->getCreateFields());
    }

    public function update($zoneId)
    {
        return Client::updateResource('/shipping/zones/' . $zoneId . '/methods/' . $this->id, $this->getUpdateFields());
    }
}
