<?php

namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;

/**
 * Represents a single currency.
 */
class Currency extends Resource
{
    /** @var string[] */
    protected $ignoreOnCreate = array(
        'date_created',
        'date_modified',
    );

    /** @var string[] */
    protected $ignoreOnUpdate = array(
        'id',
        'date_created',
        'date_modified',
    );

    /**
     * @return mixed
     */
    public function create()
    {
        return Client::createCurrency($this->getCreateFields());
    }

    /**
     * @return mixed
     */
    public function update()
    {
        return Client::updateCurrency($this->id, $this->getUpdateFields());
    }

    /**
     * @return mixed
     */
    public function delete()
    {
        return Client::deleteCurrency($this->id);
    }
}
