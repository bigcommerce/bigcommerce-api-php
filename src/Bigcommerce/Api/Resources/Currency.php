<?php

namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;

/**
 * Represents a single currency.
 */
class Currency extends Resource
{
    protected $ignoreOnCreate = array(
        'date_created',
        'date_modified',
    );

    protected $ignoreOnUpdate = array(
        'id',
        'date_created',
        'date_modified',
    );

    public function create()
    {
        return Client::createCurrency($this->getCreateFields());
    }

    public function update()
    {
        return Client::updateCurrency($this->id, $this->getUpdateFields());
    }

    public function delete()
    {
        return Client::deleteCurrency($this->id);
    }
}
